const sizeControls = document.querySelector('[data-glaze-reading-size]');
const article = sizeControls?.closest('.entry-main')?.querySelector('.entry-content');

if (article) {
  const smaller = sizeControls.querySelector('[data-glaze-reading-smaller]');
  const larger = sizeControls.querySelector('[data-glaze-reading-larger]');
  const status = sizeControls.querySelector('[data-glaze-reading-size-status]');
  const minimum = -1;
  const maximum = 3;
  let step = 0;

  function changeSize(delta) {
    step = Math.max(minimum, Math.min(maximum, step + delta));
    const scale = 1 + step / 8;
    article.style.setProperty('--glaze-reading-scale', scale);
    smaller.disabled = step === minimum;
    larger.disabled = step === maximum;
    status.textContent = `${Math.round(scale * 100)}%`;
  }

  smaller.addEventListener('click', () => changeSize(-1));
  larger.addEventListener('click', () => changeSize(1));
}
