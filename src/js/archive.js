import { showToast } from './components/toast.js';

const archive = document.querySelector('[data-glaze-archive]');
let request;

archive?.addEventListener('click', async (event) => {
  const link = event.target.closest('[data-archive-year-link]');
  if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

  event.preventDefault();
  const menu = link.closest('details');
  const trigger = menu.querySelector('summary');
  const frame = archive.querySelector('[data-archive-calendar-frame]');
  const year = link.dataset.archiveYearLink;
  if (menu.contains(document.activeElement)) trigger.focus({ preventScroll: true });
  menu.open = false;

  request?.abort();
  if (year === archive.dataset.archiveYear) {
    request = null;
    frame.removeAttribute('aria-busy');
    return;
  }

  const controller = new AbortController();
  request = controller;
  frame.setAttribute('aria-busy', 'true');

  try {
    const url = new URL(link.href);
    url.searchParams.set('glaze_archive_fragment', '1');
    const response = await fetch(url, { signal: controller.signal });
    if (!response.ok) throw new Error('Archive request failed');

    const page = new DOMParser().parseFromString(await response.text(), 'text/html');
    const next = page.querySelector('[data-glaze-archive]');
    if (next?.dataset.archiveYear !== year) throw new Error('Archive data does not match the requested year');
    if (request !== controller) return;

    const restoreFocus = archive.contains(document.activeElement);
    archive.replaceChildren(...next.childNodes);
    archive.dataset.archiveYear = year;
    if (restoreFocus) archive.querySelector('.archive-years summary').focus({ preventScroll: true });
  } catch {
    if (request === controller) showToast(archive.dataset.archiveError, 'error', trigger);
  } finally {
    if (request === controller) {
      request = null;
      frame.removeAttribute('aria-busy');
    }
  }
});
