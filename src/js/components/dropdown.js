const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
let closeCurrent;

export function initDropdown(details, { hover = false, onOpen } = {}) {
  const trigger = details.querySelector('summary');
  const panel = details.querySelector('.t-dropdown');
  let closeTimer;
  let hoverTimer;

  function open() {
    clearTimeout(closeTimer);
    clearTimeout(hoverTimer);
    if (closeCurrent && closeCurrent !== close) closeCurrent();
    closeCurrent = close;

    if (!details.open) {
      details.open = true;
      panel.getBoundingClientRect();
    }
    panel.classList.remove('is-closing');
    panel.classList.add('is-open');
    panel.inert = false;
    trigger.setAttribute('aria-expanded', 'true');
    onOpen?.();
  }

  function close() {
    if (!panel.classList.replace('is-open', 'is-closing')) return;

    clearTimeout(hoverTimer);
    if (closeCurrent === close) closeCurrent = undefined;
    panel.inert = true;
    trigger.setAttribute('aria-expanded', 'false');

    const finish = () => {
      details.open = false;
      panel.classList.remove('is-closing');
    };
    const duration = Number.parseFloat(getComputedStyle(panel).getPropertyValue('--dropdown-close-dur'));
    if (reducedMotion.matches || !duration) finish();
    else closeTimer = setTimeout(finish, duration);
  }

  details.dataset.animated = '';
  if (details.open) open();
  else {
    panel.inert = true;
    trigger.setAttribute('aria-expanded', 'false');
  }

  trigger.addEventListener('click', (event) => {
    event.preventDefault();
    if (closeCurrent === close) close();
    else open();
  });

  document.addEventListener('pointerdown', (event) => {
    if (!details.contains(event.target)) close();
  });

  details.addEventListener('focusout', (event) => {
    if (event.relatedTarget && !details.contains(event.relatedTarget) && (!hover || !details.matches(':hover'))) close();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape' || closeCurrent !== close) return;
    event.preventDefault();
    trigger.focus();
    close();
  });

  if (hover) {
    details.addEventListener('pointerenter', (event) => {
      if (event.pointerType === 'mouse') open();
    });
    details.addEventListener('pointerleave', (event) => {
      if (event.pointerType !== 'mouse') return;
      hoverTimer = setTimeout(() => {
        if (!details.matches(':focus-within')) close();
      }, 180);
    });
  }

  return open;
}
