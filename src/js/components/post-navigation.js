const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

for (const link of document.querySelectorAll('.post-navigation .nav-links a')) {
  function moveLight(event) {
    if (event.pointerType !== 'mouse' || reducedMotion.matches) return;

    const { left, top } = link.getBoundingClientRect();
    link.style.setProperty('--post-nav-light-x', (event.clientX - left) + 'px');
    link.style.setProperty('--post-nav-light-y', (event.clientY - top) + 'px');
  }

  link.addEventListener('pointerenter', moveLight);
  link.addEventListener('pointermove', moveLight);
  link.addEventListener('focus', () => {
    if (!link.matches(':focus-visible')) return;

    link.style.removeProperty('--post-nav-light-x');
    link.style.removeProperty('--post-nav-light-y');
  });
}
