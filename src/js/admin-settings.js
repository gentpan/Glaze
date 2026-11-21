import { showToast } from './components/toast.js';

(() => {
  const settings = document.querySelector('.glaze-settings');
  if (!settings) return;

  const form = settings.querySelector('form');
  const toastStack = settings.querySelector('[data-glaze-toast-stack]');
  if (toastStack) {
    showToast(toastStack.dataset.glazeSavedMessage, 'success', form.elements.submit);
    const url = new URL(window.location.href);
    url.searchParams.delete('glaze-updated');
    window.history.replaceState(window.history.state, '', url);
  }

  const sections = Array.from(settings.querySelectorAll('.glaze-settings__nav-link'), (link) => ({
    link,
    panel: document.getElementById(link.hash.slice(1)),
  }));
  const showSection = (hash) => {
    const active = sections.find(({ link }) => link.hash === hash) || sections[0];
    for (const { link, panel } of sections) {
      panel.hidden = panel !== active.panel;
      if (link === active.link) link.setAttribute('aria-current', 'location');
      else link.removeAttribute('aria-current');
    }
    form.elements.glaze_section.value = active.panel.id;
  };
  for (const { link } of sections) {
    link.addEventListener('click', (event) => {
      if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
      event.preventDefault();
      if (link.hash !== window.location.hash) window.history.pushState(null, '', link.hash);
      showSection(link.hash);
    });
  }
  window.addEventListener('hashchange', () => showSection(window.location.hash));
  form.addEventListener('invalid', (event) => {
    if (event.target !== form.querySelector(':invalid')) return;
    const section = event.target.closest('.glaze-settings__section');
    if (section?.hidden) {
      const hash = `#${section.id}`;
      window.history.replaceState(null, '', hash);
      showSection(hash);
    }
  }, true);
  showSection(window.location.hash);

  const width = form.elements.westnote_glaze_content_width;
  const presets = form.querySelectorAll('[data-glaze-width]');
  const sample = form.querySelector('[data-glaze-admin-sample]');
  const sampleTitle = sample.querySelector('[data-glaze-sample-title]');
  const savedTitle = sampleTitle.textContent;
  const updateSample = () => {
    sample.dataset.glazeSwatch = form.elements.westnote_glaze_palette.value;
    sample.dataset.scheme = form.elements.westnote_glaze_scheme.value;
    sample.dataset.background = form.elements.westnote_glaze_background.value;
    sample.dataset.corners = form.elements.westnote_glaze_corner_mode.value;
    sampleTitle.textContent = form.elements.blogname?.value.trim() ?? savedTitle;
    if (width.validity.valid) {
      const ratio = (width.valueAsNumber - Number(width.min)) / (Number(width.max) - Number(width.min));
      sample.style.setProperty('--sample-width', `${56 + ratio * 34}%`);
    }
  };
  const updatePresets = () => {
    for (const preset of presets) {
      preset.setAttribute('aria-pressed', String(width.valueAsNumber === Number(preset.dataset.glazeWidth)));
    }
  };
  for (const preset of presets) {
    preset.addEventListener('click', () => {
      width.value = preset.dataset.glazeWidth;
      width.dispatchEvent(new Event('input', { bubbles: true }));
      width.focus();
    });
  }
  width.addEventListener('input', updatePresets);
  form.addEventListener('input', (event) => {
    if (event.target.matches('#blogname, select, [name="westnote_glaze_palette"], #westnote_glaze_content_width')) updateSample();
  });
  updateSample();

  const palette = form.querySelector('[data-glaze-admin-palette]');
  if (palette) {
    const trigger = palette.querySelector('summary');
    const dot = trigger.querySelector('[data-glaze-swatch]');
    const name = trigger.querySelector('[data-glaze-palette-current]');
    const close = (restoreFocus = false) => {
      if (!palette.open) return;
      palette.open = false;
      if (restoreFocus) trigger.focus();
    };

    palette.addEventListener('change', (event) => {
      const option = event.target;
      dot.dataset.glazeSwatch = option.value;
      name.textContent = option.labels[0].querySelector('[data-glaze-palette-name]').textContent;
    });
    palette.addEventListener('click', (event) => {
      if (event.detail && event.target.closest('.glaze-settings__palette-option')) {
        requestAnimationFrame(() => close(true));
      }
    });
    palette.addEventListener('keydown', (event) => {
      if (!palette.open) return;
      if (event.key === 'Escape' || (event.key === 'Enter' && event.target.matches('input[type="radio"]'))) {
        event.preventDefault();
        close(true);
      }
    });
    palette.addEventListener('focusout', (event) => {
      if (event.relatedTarget && !palette.contains(event.relatedTarget)) close();
    });
    document.addEventListener('pointerdown', (event) => {
      if (!palette.contains(event.target)) close();
    });
  }

  const logo = form.querySelector('[data-glaze-logo]');
  if (!logo) return;

  const preview = logo.querySelector('img');
  const value = logo.querySelector('input');
  const pick = logo.querySelector('[data-glaze-pick-logo]');
  const remove = logo.querySelector('[data-glaze-remove-logo]');

  pick.addEventListener('click', () => {
    const frame = wp.media({ title: pick.textContent.trim(), library: { type: 'image' }, multiple: false });
    frame.on('select', () => {
      const image = frame.state().get('selection').first().toJSON();
      value.value = image.id;
      preview.src = image.sizes?.thumbnail?.url || image.url;
      preview.hidden = false;
      remove.hidden = false;
    });
    frame.open();
  });

  remove.addEventListener('click', () => {
    value.value = '0';
    preview.hidden = true;
    remove.hidden = true;
  });
})();
