const media = document.querySelectorAll('.post-card-image img, .entry-cover img, .entry-content img:not(.collection-site-image)');
const pending = new WeakMap();
const visible = new Map();
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
let frame = 0;

function draw(now) {
  for (const [image, state] of visible) {
    const rect = image.getBoundingClientRect();
    const width = Math.round(rect.width);
    const height = Math.round(rect.height);
    const { canvas, context } = state;
    canvas.style.left = rect.left + 'px';
    canvas.style.top = rect.top + 'px';

    const resized = state.width !== width || state.height !== height;
    if (resized) {
      state.width = width;
      state.height = height;
      const ratio = Math.min(window.devicePixelRatio || 1, 2);
      const pitch = Math.max(5, Math.sqrt(width * height / 850));
      state.dots = [];
      canvas.style.width = width + 'px';
      canvas.style.height = height + 'px';
      canvas.width = Math.round(width * ratio);
      canvas.height = Math.round(height * ratio);
      context.setTransform(ratio, 0, 0, ratio, 0, 0);
      context.fillStyle = getComputedStyle(canvas).color;

      for (let y = pitch / 2; y < height; y += pitch) {
        for (let x = pitch / 2; x < width; x += pitch) {
          state.dots.push({ x, y, cycle: 1260 + Math.random() * 280, phase: Math.random() });
        }
      }
    }

    if (state.revealing || (reducedMotion.matches && !resized)) continue;
    context.clearRect(0, 0, width, height);
    for (const dot of state.dots) {
      const t = reducedMotion.matches ? dot.phase : (now / dot.cycle + dot.phase) % 1;
      const amplitude = (1 - Math.cos(t * Math.PI * 2)) / 2;
      context.globalAlpha = amplitude * 0.45;
      context.beginPath();
      context.arc(dot.x, dot.y, 0.9 * amplitude, 0, Math.PI * 2);
      context.fill();
    }
  }
  frame = visible.size ? requestAnimationFrame(draw) : 0;
}

function clear(image) {
  const state = visible.get(image);
  state?.canvas.remove();
  visible.delete(image);
  image.classList.remove('glaze-image-loading', 'glaze-image-revealing');
}

function show(image) {
  const rect = image.getBoundingClientRect();
  if (image.complete || rect.width < 48 || rect.height < 32) return;

  const canvas = document.createElement('canvas');
  const context = canvas.getContext('2d');
  if (!context) return;

  canvas.className = 'glaze-image-placeholder';
  canvas.setAttribute('aria-hidden', 'true');
  canvas.style.borderRadius = getComputedStyle(image).borderRadius;
  canvas.style.left = rect.left + 'px';
  canvas.style.top = rect.top + 'px';
  canvas.style.width = rect.width + 'px';
  canvas.style.height = rect.height + 'px';
  document.body.append(canvas);
  visible.set(image, { canvas, context });
  image.classList.add('glaze-image-loading');
  if (!frame) frame = requestAnimationFrame(draw);
}

if (media.length && 'IntersectionObserver' in window) {
  const observer = new IntersectionObserver((entries) => {
    for (const { target: image, isIntersecting } of entries) {
      const state = pending.get(image);
      if (!state) continue;
      clearTimeout(state.delay);
      if (isIntersecting) {
        if (!visible.has(image)) state.delay = setTimeout(() => show(image), 120);
      } else {
        clear(image);
      }
    }
  });

  for (const image of media) {
    if (image.complete) continue;

    const state = {};
    pending.set(image, state);
    observer.observe(image);
    const finish = (success) => {
      observer.unobserve(image);
      pending.delete(image);
      clearTimeout(state.delay);
      const active = visible.get(image);
      if (!active || !success || reducedMotion.matches) {
        clear(image);
        return;
      }

      active.revealing = true;
      active.canvas.classList.add('is-revealed');
      image.classList.add('glaze-image-revealing');
      image.classList.remove('glaze-image-loading');
      setTimeout(() => clear(image), 650);
    };
    image.addEventListener('load', () => finish(true), { once: true });
    image.addEventListener('error', () => finish(false), { once: true });
  }
}
