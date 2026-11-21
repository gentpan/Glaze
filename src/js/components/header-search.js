import { initDropdown } from './dropdown.js';

const search = document.querySelector('[data-glaze-header-search]');

if (search) {
  const field = search.querySelector('input[name="s"]');
  const shortcut = search.querySelector('[data-glaze-search-key]');
  const platform = navigator.userAgentData?.platform || navigator.platform;
  if (shortcut) {
    shortcut.textContent = /Mac|iPhone|iPad|iPod/i.test(platform) ? '⌘ K' : 'Ctrl K';
    shortcut.hidden = false;
  }

  const openSearch = initDropdown(search, {
    onOpen() {
      field?.focus();
      field?.select();
    },
  });
  document.addEventListener('keydown', (event) => {
    if ((event.metaKey || event.ctrlKey) && !event.altKey && !event.shiftKey && event.key.toLowerCase() === 'k' && !document.querySelector('dialog[open]')) {
      event.preventDefault();
      openSearch();
    }
  });
}
