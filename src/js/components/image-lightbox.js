const content = document.querySelector('[data-glaze-image-open]');

if (content && typeof HTMLDialogElement !== 'undefined') {
  const dialog = document.createElement('dialog');
  const button = document.createElement('button');
  const media = document.createElement('span');
  const photo = document.createElement('img');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const easing = 'cubic-bezier(0.22, 1, 0.36, 1)';
  const flat = 'rotateX(0deg) rotateY(0deg) translateZ(0px)';
  let source;
  let invoker;
  let fullPhoto;
  let closing = false;

  dialog.className = 'glaze-image-lightbox';
  button.type = 'button';
  button.className = 'glaze-image-lightbox-frame squircle';
  button.setAttribute('aria-label', content.dataset.glazeImageClose);
  media.className = 'glaze-image-lightbox-media';
  photo.alt = '';
  media.append(photo);
  button.append(media);
  dialog.append(button);
  document.body.append(dialog);

  function thumbnailFrame() {
    const viewport = dialog.getBoundingClientRect();
    const small = source.getBoundingClientRect();
    const x = small.left + small.width / 2 - viewport.left - viewport.width / 2;
    const y = small.top + small.height / 2 - viewport.top - viewport.height / 2;
    return {
      width: small.width + 'px',
      height: small.height + 'px',
      transform: `translate(${x}px, ${y}px)`,
      borderRadius: getComputedStyle(source).borderRadius,
    };
  }

  function currentFrame() {
    const { width, height, transform, borderRadius } = getComputedStyle(button);
    return { width, height, transform, borderRadius };
  }

  function open(image, trigger, originalUrl) {
    source = image;
    invoker = trigger;
    const { objectFit, objectPosition } = getComputedStyle(image);
    photo.src = image.currentSrc || image.src;
    photo.style.objectFit = objectFit === 'cover' ? 'cover' : 'contain';
    photo.style.objectPosition = objectPosition;
    dialog.setAttribute('aria-label', [content.dataset.glazeImageOpen, image.alt].filter(Boolean).join(' '));
    const ratio = image.naturalWidth / image.naturalHeight;
    const width = Math.min(1200, window.innerWidth - 32, (window.innerHeight - 48) * ratio);
    button.style.width = width + 'px';
    button.style.height = width / ratio + 'px';

    dialog.showModal();
    source.classList.add('is-lightbox-source');
    let expand;
    if (!reducedMotion.matches && typeof button.animate === 'function') {
      expand = button.animate([thumbnailFrame(), currentFrame()], { duration: 620, easing });
      media.animate(
        [{ transform: flat }, { offset: 0.4, transform: 'rotateX(22deg) rotateY(-14deg) translateZ(70px)' }, { transform: flat }],
        { duration: 620, easing },
      );
    }
    dialog.classList.add('is-open');

    if (originalUrl && originalUrl !== photo.src) {
      const original = new Image();
      fullPhoto = original;
      original.alt = '';
      original.addEventListener('load', async () => {
        await expand?.finished.catch(() => {});
        if (fullPhoto !== original || !dialog.open || closing) return;
        media.append(original);
        if (!reducedMotion.matches && typeof original.animate === 'function') {
          original.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 180, easing });
        }
      }, { once: true });
      original.src = originalUrl;
    }
  }

  async function close() {
    if (!dialog.open || closing) return;
    closing = true;
    dialog.classList.remove('is-open');
    if (!reducedMotion.matches && typeof button.animate === 'function') {
      const from = currentFrame();
      const to = thumbnailFrame();
      const tilt = getComputedStyle(media).transform;
      const original = fullPhoto?.isConnected ? fullPhoto : null;
      const opacity = original ? getComputedStyle(original).opacity : 0;
      const options = { duration: 420, easing, fill: 'forwards' };
      for (const animation of button.getAnimations({ subtree: true })) animation.cancel();

      const shrink = button.animate([from, to], options);
      const settle = media.animate(
        [{ transform: tilt }, { offset: 0.45, transform: 'rotateX(-10deg) rotateY(6deg) translateZ(28px)' }, { transform: flat }],
        options,
      );
      const fade = original?.animate([{ opacity }, { opacity: 0 }], options);
      await Promise.allSettled([shrink.finished, settle.finished, fade?.finished]);
    }

    source.classList.remove('is-lightbox-source');
    dialog.close();
    for (const animation of button.getAnimations?.({ subtree: true }) || []) animation.cancel();
    fullPhoto?.remove();
    invoker?.focus({ preventScroll: true });
    source = undefined;
    invoker = undefined;
    fullPhoto = undefined;
    closing = false;
  }

  button.addEventListener('click', close);
  dialog.addEventListener('click', (event) => {
    if (event.target === dialog) close();
  });
  dialog.addEventListener('cancel', (event) => {
    event.preventDefault();
    close();
  });

  for (const image of content.querySelectorAll('img:not(.emoji):not(.wp-smiley):not(.collection-site-image)')) {
    const link = image.closest('a[href]');
    if (link && !/\.(?:avif|bmp|gif|jpe?g|png|svg|webp)(?:[?#]|$)/i.test(link.href) && link.href !== image.src) continue;

    const nativeTrigger = image.closest('.wp-lightbox-container')?.querySelector('.lightbox-trigger');
    const trigger = link || nativeTrigger || image;
    image.classList.add('glaze-image-enlarge');
    if (trigger === image) {
      image.tabIndex = 0;
      image.setAttribute('role', 'button');
      image.setAttribute('aria-label', [content.dataset.glazeImageOpen, image.alt].filter(Boolean).join(' '));
      image.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        event.preventDefault();
        image.click();
      });
    }

    const onClick = (event) => {
      if (dialog.open || !image.naturalWidth || event.ctrlKey || event.metaKey || event.altKey || event.shiftKey) return;
      event.preventDefault();
      event.stopImmediatePropagation();
      open(image, trigger, link?.href);
    };
    trigger.addEventListener('click', onClick, true);
    if (nativeTrigger === trigger) image.addEventListener('click', onClick, true);
  }
}
