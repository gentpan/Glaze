const root = document.documentElement;
const previewing = root.hasAttribute('data-glaze-customize-preview');
const accents = (root.dataset.glazePalettes || 'green').split(' ');
const schemes = ['light', 'dark'];
const accentKey = 'glaze-accent';
const schemeKey = 'glaze-appearance';
const defaultPalette = root.dataset.glazePalette;
const defaultScheme = root.dataset.glazeScheme;

if (!previewing) {
  try {
    const accent = localStorage.getItem(accentKey);
    const scheme = localStorage.getItem(schemeKey);
    if (accents.includes(accent)) root.dataset.glazePalette = accent;
    if (schemes.includes(scheme)) root.dataset.glazeScheme = scheme;
  } catch {
    // Browser storage is optional.
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const picker = document.querySelector('[data-glaze-palette-picker]');
  if (!picker) return;

  const swatches = picker.querySelectorAll('[data-glaze-swatch]');
  const schemeToggle = picker.querySelector('[data-glaze-theme-toggle]');

  function setPreference(property, key, value) {
    root.dataset[property] = value;
    if (previewing) return;
    try {
      localStorage.setItem(key, value);
    } catch {
      // The selected preference still applies to this page.
    }
  }

  function updateSelection() {
    for (const swatch of swatches) {
      swatch.setAttribute('aria-pressed', String(swatch.dataset.glazeSwatch === root.dataset.glazePalette));
    }
    schemeToggle?.setAttribute('aria-pressed', String(root.dataset.glazeScheme === 'dark'));
  }

  for (const swatch of swatches) {
    swatch.addEventListener('click', () => {
      setPreference('glazePalette', accentKey, swatch.dataset.glazeSwatch);
      updateSelection();
    });
  }

  if (schemeToggle) {
    schemeToggle.addEventListener('click', () => {
      setPreference('glazeScheme', schemeKey, root.dataset.glazeScheme === 'dark' ? 'light' : 'dark');
      updateSelection();
    });
  }

  if (!previewing) {
    window.addEventListener('storage', (event) => {
      if (event.key === accentKey || event.key === null) {
        root.dataset.glazePalette = accents.includes(event.newValue) ? event.newValue : defaultPalette;
      }
      if (event.key === schemeKey || event.key === null) {
        root.dataset.glazeScheme = schemes.includes(event.newValue) ? event.newValue : defaultScheme;
      }
      updateSelection();
    });
  }

  updateSelection();
  picker.hidden = false;
}, { once: true });
