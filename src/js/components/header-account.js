import { initDropdown } from './dropdown.js';

const account = document.querySelector('[data-glaze-header-account]');
if (account) initDropdown(account);

const openLogin = document.querySelector('[data-glaze-open-login]');

if (openLogin) {
  const dialog = document.getElementById(openLogin.getAttribute('aria-controls'));
  const form = dialog.querySelector('[data-glaze-login-form]');
  const feedback = form.querySelector('[role="alert"]');
  const submit = form.querySelector('[type="submit"]');

  openLogin.addEventListener('click', () => dialog.showModal());
  dialog.querySelector('[data-glaze-close-login]').addEventListener('click', () => dialog.close());
  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (submit.disabled) return;

    const data = new URLSearchParams(new FormData(form));
    data.set('redirect_to', window.location.href);
    submit.disabled = true;
    feedback.hidden = true;

    try {
      // WordPress issues a test cookie before accepting a login form submission.
      const setup = await fetch(form.action, { mode: 'same-origin', cache: 'no-store' });
      if (!setup.ok) throw new Error();

      const result = await fetch(form.action, {
        method: 'POST',
        mode: 'same-origin',
        body: data,
        redirect: 'manual',
      });
      if (result.type === 'opaqueredirect') {
        window.location.reload();
        return;
      }

      feedback.textContent = result.ok ? form.dataset.loginError : form.dataset.networkError;
    } catch {
      feedback.textContent = form.dataset.networkError;
    }

    feedback.hidden = false;
    submit.disabled = false;
  });
  openLogin.hidden = false;
}
