let dismissTimer;

export function showToast(message, type, trigger, { announce = true } = {}) {
  const stack = document.querySelector('[data-glaze-toast-stack]');
  if (!stack || !message) return;

  clearTimeout(dismissTimer);

  const toast = document.createElement('button');
  toast.type = 'button';
  toast.className = 'glaze-toast is-entering';
  toast.dataset.type = type;
  toast.setAttribute('aria-label', stack.dataset.dismissLabel);

  const icon = document.createElement('span');
  icon.className = 'glaze-toast-icon';
  icon.setAttribute('aria-hidden', 'true');
  icon.textContent = { success: '\uf00c', warning: '\uf12a', error: '\uf00d' }[type] || '\uf129';

  const text = document.createElement('span');
  text.className = 'glaze-toast-message';
  text.textContent = message;

  function dismiss() {
    if (!toast.isConnected || toast.classList.contains('is-leaving')) return;

    clearTimeout(dismissTimer);
    toast.classList.add('is-leaving');
    if (document.activeElement === toast) {
      trigger?.focus({ preventScroll: true });
    }
    const animations = toast.getAnimations?.() ?? [];
    Promise.allSettled(animations.map((animation) => animation.finished)).then(() => toast.remove());
  }

  function scheduleDismiss() {
    if (!toast.isConnected || toast.classList.contains('is-leaving')) return;

    clearTimeout(dismissTimer);
    if (!toast.matches(':hover, :focus-within')) {
      dismissTimer = setTimeout(dismiss, 5000);
    }
  }

  toast.addEventListener('click', dismiss);
  toast.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') dismiss();
  });
  toast.addEventListener('pointerenter', () => clearTimeout(dismissTimer));
  toast.addEventListener('pointerleave', scheduleDismiss);
  toast.addEventListener('focusin', () => clearTimeout(dismissTimer));
  toast.addEventListener('focusout', () => queueMicrotask(scheduleDismiss));
  toast.append(icon, text);
  stack.replaceChildren(toast);
  requestAnimationFrame(() => requestAnimationFrame(() => toast.classList.remove('is-entering')));
  scheduleDismiss();

  if (announce) {
    const status = document.querySelector('[data-glaze-toast-status]');
    if (status) status.textContent = message;
  }
}
