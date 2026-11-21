const toggle = document.querySelector('[data-glaze-reading-toggle]');
const entry = toggle?.closest('.entry-main');
const content = entry?.querySelector('.entry-content');
const veil = entry?.querySelector('.entry-reading-veil');

if (toggle && content && veil && CSS.supports('clip-path', 'inset(0px)') && CSS.supports('color', 'color-mix(in srgb, white, transparent)')) {
  const viewport = window.visualViewport;
  let activeListeners;
  let frame = 0;

  function updateVeil() {
    frame = 0;
    const viewportTop = viewport?.offsetTop ?? 0;
    const height = viewport?.height ?? window.innerHeight;
    const width = document.documentElement.clientWidth;
    const bounds = content.getBoundingClientRect();
    const viewportBottom = viewportTop + height;

    veil.hidden = bounds.top >= viewportBottom || bounds.bottom <= viewportTop;
    if (veil.hidden) return;

    veil.style.setProperty('--glaze-reading-viewport-top', `${viewportTop}px`);
    veil.style.setProperty('--glaze-reading-viewport-height', `${height}px`);
    // Keep the viewport gradient inside the article as its edges scroll past.
    const clip = [bounds.top - viewportTop, width - bounds.right, viewportBottom - bounds.bottom, bounds.left];
    veil.style.clipPath = `inset(${clip.map(edge => `${Math.max(0, edge)}px`).join(' ')})`;
  }

  function scheduleUpdate() {
    if (!frame) frame = requestAnimationFrame(updateVeil);
  }

  const contentSize = new ResizeObserver(scheduleUpdate);
  toggle.closest('.entry-reading-controls').hidden = false;
  toggle.addEventListener('click', () => {
    const enabled = toggle.getAttribute('aria-pressed') === 'false';
    toggle.setAttribute('aria-pressed', String(enabled));

    if (enabled) {
      activeListeners = new AbortController();
      const options = { passive: true, signal: activeListeners.signal };
      window.addEventListener('scroll', scheduleUpdate, options);
      window.addEventListener('resize', scheduleUpdate, options);
      viewport?.addEventListener('scroll', scheduleUpdate, options);
      viewport?.addEventListener('resize', scheduleUpdate, options);
      contentSize.observe(content);
      scheduleUpdate();
    } else {
      activeListeners.abort();
      contentSize.disconnect();
      cancelAnimationFrame(frame);
      frame = 0;
      veil.hidden = true;
    }
  });
}
