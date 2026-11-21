const content = document.querySelector('.entry-main > .entry-content');
const selector = 'a.glaze-external-link:any-link:not(.wp-element-button, .wp-block-button__link, .wp-block-file__button, [role="button"], [download], :has(img, svg, video, canvas))';

if (content?.querySelector(selector)) {
  const preview = document.createElement('div');
  preview.className = 'glaze-link-preview glaze-panel';
  preview.hidden = true;
  preview.setAttribute('aria-hidden', 'true');
  preview.innerHTML = `
    <div class="glaze-link-preview-media">
      <img alt="" decoding="async" referrerpolicy="no-referrer" hidden>
    </div>
    <div class="glaze-link-preview-info"><strong></strong><span></span></div>`;
  const media = preview.querySelector('.glaze-link-preview-media');
  const image = preview.querySelector('img');
  const title = preview.querySelector('strong');
  const host = preview.querySelector('span');
  document.body.append(preview);

  let active;
  let imageUrl;
  let timer;
  let exitTimer;

  function close() {
    window.clearTimeout(timer);
    if (!active) return;
    active.classList.remove('is-link-previewing');
    active = undefined;
    preview.classList.remove('is-open');
    preview.classList.add('is-closing');
    const closeDuration = Number.parseFloat(getComputedStyle(preview).transitionDuration);
    exitTimer = window.setTimeout(() => {
      preview.hidden = true;
      preview.classList.remove('is-closing');
    }, closeDuration * 1000);
  }

  function position() {
    if (!active) return;
    const bounds = active.getBoundingClientRect();
    if (!active.isConnected || bounds.bottom <= 0 || bounds.top >= window.innerHeight) {
      close();
      return;
    }

    const center = bounds.left + bounds.width / 2;
    const width = preview.offsetWidth;
    const height = preview.offsetHeight;
    const left = Math.max(12, Math.min(center - width / 2, window.innerWidth - width - 12));
    const above = bounds.top - height - 8;
    const below = bounds.bottom + 8;
    const placeBelow = above < 8 && (below + height <= window.innerHeight || window.innerHeight - below > bounds.top);
    const top = Math.max(8, Math.min(placeBelow ? below : above, window.innerHeight - height - 8));
    preview.dataset.placement = placeBelow ? 'below' : 'above';
    preview.style.left = `${left}px`;
    preview.style.top = `${top}px`;
    preview.style.setProperty('--link-preview-pointer', `${Math.max(9, Math.min(center - left, width - 9))}px`);
  }

  function show(link) {
    if (!link.isConnected) return;
    const destination = new URL(link.dataset.glazePreviewUrl || link.href, document.baseURI);
    if (!['https:', 'http:'].includes(destination.protocol) || destination.username || destination.password) return;
    destination.hash = '';

    window.clearTimeout(exitTimer);
    active?.classList.remove('is-link-previewing');
    active = link;
    link.classList.add('is-link-previewing');
    title.textContent = link.textContent.trim() || destination.hostname;
    host.textContent = destination.hostname;
    const screenshot = `https://s.wordpress.com/mshots/v1/${encodeURIComponent(destination.href)}?w=640`;
    if (screenshot !== imageUrl) {
      imageUrl = screenshot;
      media.hidden = false;
      image.hidden = true;
      image.src = screenshot;
    }

    preview.classList.remove('is-closing');
    preview.hidden = false;
    position();
    window.requestAnimationFrame(() => {
      if (active === link) preview.classList.add('is-open');
    });
  }

  function scheduleShow(link) {
    window.clearTimeout(timer);
    if (active !== link) timer = window.setTimeout(() => show(link), 230);
  }

  function scheduleClose() {
    window.clearTimeout(timer);
    timer = window.setTimeout(close, 180);
  }

  function findLink(node) {
    const link = node instanceof Element ? node.closest(selector) : null;
    return link && content.contains(link) ? link : null;
  }

  image.addEventListener('load', () => {
    // mShots sends a 400 × 300 placeholder while it prepares a new capture.
    const pending = image.naturalWidth === 400 && image.naturalHeight === 300;
    media.hidden = pending;
    image.hidden = pending;
    if (pending) imageUrl = undefined;
    position();
  });
  image.addEventListener('error', () => {
    media.hidden = true;
    image.hidden = true;
    imageUrl = undefined;
    position();
  });

  content.addEventListener('pointerover', (event) => {
    if (event.pointerType !== 'mouse') return;
    const link = findLink(event.target);
    if (link && !link.contains(event.relatedTarget)) scheduleShow(link);
  });
  content.addEventListener('pointerout', (event) => {
    const link = findLink(event.target);
    if (link && !link.contains(event.relatedTarget) && !link.matches(':focus-visible')) scheduleClose();
  });
  content.addEventListener('focusin', (event) => {
    const link = findLink(event.target);
    if (link?.matches(':focus-visible')) scheduleShow(link);
  });
  content.addEventListener('focusout', (event) => {
    const link = findLink(event.target);
    if (link && !link.matches(':hover')) scheduleClose();
  });
  preview.addEventListener('pointerenter', () => window.clearTimeout(timer));
  preview.addEventListener('pointerleave', scheduleClose);
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') close();
  });
  window.addEventListener('scroll', position, { passive: true });
  window.addEventListener('resize', position);
}
