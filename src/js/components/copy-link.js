import { showToast } from './toast.js';

for (const button of document.querySelectorAll('[data-glaze-copy-link]')) {
  const isRss = button.hasAttribute('data-glaze-rss-copy');
  let resetIcon;

  button.hidden = false;
  button.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(button.dataset.glazeCopyLink);
      if (isRss) {
        clearTimeout(resetIcon);
        button.dataset.copyState = 'copied';
        resetIcon = setTimeout(() => delete button.dataset.copyState, 2200);
      }
      showToast(button.dataset.copySuccess, 'success', button);
    } catch {
      showToast(button.dataset.copyError, 'error', button);
    }
  });
}
