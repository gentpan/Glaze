import { showToast } from './toast.js';

const toastTypes = { approved: 'success', pending: 'warning', failed: 'error' };
let activeConfirmation;

function showConfirmation(confirmation, button = document.querySelector('#comments .glaze-comment-submit')) {
  activeConfirmation?.remove();
  activeConfirmation = confirmation;

  const plane = confirmation.querySelector('.glaze-comment-plane');
  const outcome = confirmation.querySelector('.glaze-comment-outcome');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const state = confirmation.dataset.glazeCommentConfirmation;
  let fallback;

  function revealOutcome() {
    clearTimeout(fallback);
    if (activeConfirmation !== confirmation || outcome.dataset.state === 'in') return;
    outcome.dataset.state = 'in';
    confirmation.classList.add('is-confirmed');
    const message = confirmation.querySelector('p').textContent;
    // Rejections are already announced immediately by the form's alert.
    showToast(message, toastTypes[state], button, { announce: state !== 'failed' });
    setTimeout(() => confirmation.remove(), state === 'failed' ? 2800 : 1800);
  }

  document.body.append(confirmation);
  confirmation.hidden = false;
  if (reducedMotion.matches) {
    revealOutcome();
  } else {
    plane.addEventListener('animationend', revealOutcome, { once: true });
    fallback = setTimeout(revealOutcome, 1280);
  }
}

const confirmation = document.querySelector('[data-glaze-comment-confirmation]');
if (confirmation) {
  const address = new URL(window.location.href);
  address.searchParams.delete('glaze_sent');
  history.replaceState(history.state, '', address);
  showConfirmation(confirmation);
}

const section = document.querySelector('#comments');
const form = section?.querySelector('form.comment-form');
const feedback = form?.querySelector('.glaze-comment-error');
const outcomes = new Map(Array.from(section?.querySelectorAll('template[data-glaze-comment-outcome]') || [],
  (template) => [template.dataset.glazeCommentOutcome, template]));
const listSelector = '[data-glaze-comment-list]';
let pageRequest;

async function refreshList(location, signal) {
  const destination = new URL(location, window.location.href);
  if (destination.origin !== window.location.origin) return false;

  const response = await fetch(destination, {
    mode: 'same-origin',
    credentials: 'same-origin',
    cache: 'no-store',
    headers: { Accept: 'application/json', 'X-Glaze-Comments': '1' },
    signal,
  });
  const result = await response.json();
  if (signal?.aborted || !response.ok || result?.success !== true || typeof result.data?.html !== 'string') return false;

  const markup = document.createElement('template');
  markup.innerHTML = result.data.html;
  const current = section.querySelector(listSelector);
  const updated = markup.content.querySelector(listSelector);
  if (!current || !updated) return false;

  if (form && current.contains(form)) {
    // Restore the threaded reply form before replacing its parent.
    const cancel = section.querySelector('#cancel-comment-reply-link');
    cancel?.dispatchEvent(new Event('click', { bubbles: true, cancelable: true }));
    if (current.contains(form)) return false;
  }
  current.replaceWith(updated);
  return true;
}

if (section && window.fetch) {
  section.addEventListener('click', async (event) => {
    const link = event.target.closest('.comments-pagination a');
    if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.origin !== window.location.origin) return;
    event.preventDefault();
    if (form?.getAttribute('aria-busy') === 'true') return;

    pageRequest?.abort();
    const request = new AbortController();
    pageRequest = request;
    const list = section.querySelector(listSelector);
    const error = section.querySelector('[data-glaze-comment-page-error]');
    error.hidden = true;
    list.setAttribute('aria-busy', 'true');
    try {
      if (await refreshList(link.href, request.signal)) {
        const heading = section.querySelector('#comments-title');
        heading.tabIndex = -1;
        heading.focus({ preventScroll: true });
        heading.scrollIntoView({ block: 'start' });
      } else if (!request.signal.aborted) {
        error.hidden = false;
      }
    } catch {
      if (!request.signal.aborted) error.hidden = false;
    } finally {
      if (pageRequest === request) {
        pageRequest = undefined;
        list.removeAttribute('aria-busy');
      }
    }
  });
}

if (form && feedback && outcomes.has('failed') && window.fetch) {
  async function getMacOSVersion() {
    const agent = navigator.userAgentData;
    if (agent?.platform !== 'macOS' || !agent.brands?.some(({ brand }) => brand === 'Google Chrome') || typeof agent.getHighEntropyValues !== 'function') {
      return '';
    }

    let timeout;
    try {
      const hints = await Promise.race([
        agent.getHighEntropyValues(['platformVersion']),
        new Promise((resolve) => { timeout = setTimeout(resolve, 300); }),
      ]);
      return typeof hints?.platformVersion === 'string' ? hints.platformVersion : '';
    } catch {
      return '';
    } finally {
      clearTimeout(timeout);
    }
  }

  function showOutcome(state, button, message) {
    const overlay = outcomes.get(state).content.firstElementChild.cloneNode(true);
    if (message) overlay.querySelector('p').textContent = message;
    showConfirmation(overlay, button);
  }

  function report(message, { button, type } = {}) {
    feedback.hidden = false;
    feedback.textContent = message;
    if (type === 'error') showOutcome('failed', button, message);
    if (type === 'warning') showToast(message, type, button, { announce: false });
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (form.getAttribute('aria-busy') === 'true') return;
    pageRequest?.abort();

    const data = new FormData(form);
    const button = event.submitter || form.querySelector('[type="submit"]');
    if (button?.name) data.append(button.name, button.value);

    form.setAttribute('aria-busy', 'true');
    if (button) {
      button.disabled = true;
      button.setAttribute('aria-busy', 'true');
    }
    feedback.hidden = true;
    feedback.textContent = '';
    try {
      const macOSVersion = await getMacOSVersion();
      if (macOSVersion) data.set('glaze_macos_version', macOSVersion);

      const response = await fetch(form.action, {
        method: 'POST',
        mode: 'same-origin',
        credentials: 'same-origin',
        redirect: 'error',
        headers: { Accept: 'application/json', 'X-Glaze-Comment': '1' },
        body: data,
      });
      const result = await response.json().catch(() => null);
      button?.removeAttribute('aria-busy');
      const state = result?.data?.state;
      if (response.ok && result?.success === true && (state === 'approved' || state === 'pending') && outcomes.has(state)) {
        const comment = form.elements.namedItem('comment');
        if (comment && comment.value === data.get('comment')) comment.value = '';
        showOutcome(state, button);

        const location = result.data.location;
        const updated = typeof location === 'string' && location && await refreshList(location).catch(() => false);
        if (!updated) report(section.dataset.commentListUnavailable);
        return;
      }

      const reason = result?.data?.message || result?.message;
      const rejected = result?.success === false || Boolean(result?.code && typeof reason === 'string') || (response.status >= 400 && response.status < 500);
      if (rejected) {
        // Core comment errors can include HTML. An inert template lets us extract text.
        const text = document.createElement('template');
        if (typeof reason === 'string') text.innerHTML = reason;
        const message = text.content.textContent.trim() || section.dataset.commentRejected.replace('%s', response.status);
        report(message, { button, type: 'error' });
      } else {
        report(section.dataset.commentUnconfirmed, { button, type: 'warning' });
      }
    } catch {
      report(section.dataset.commentUnconfirmed, { button, type: 'warning' });
    } finally {
      form.removeAttribute('aria-busy');
      if (button) {
        button.removeAttribute('aria-busy');
        button.disabled = false;
      }
    }
  });
}
