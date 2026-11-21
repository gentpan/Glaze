const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
const moves = new WeakMap();

function showDismissButtons(rail) {
  for (const button of rail.querySelectorAll('[data-glaze-rail-close]')) button.hidden = false;
}

function flipCards(list, entering) {
  if (motionPreference.matches || typeof list.animate !== 'function') return;
  const cards = [...list.querySelectorAll('[data-glaze-rail-card]:not([hidden]):not([inert])')];
  const front = { opacity: 1, transform: 'perspective(650px) rotateY(0deg)' };
  const edge = { opacity: 0, transform: `perspective(650px) rotateY(${entering ? '-80deg' : '80deg'})` };
  return Promise.allSettled(cards.map((card, index) => card.animate(
    entering ? [edge, front] : [front, edge],
    { duration: entering ? 310 : 180, delay: index * (entering ? 24 : 14), easing: 'ease-in-out', fill: entering ? 'backwards' : 'forwards' },
  ).finished));
}

async function refreshBlogroll(rail, button) {
  const list = rail.querySelector('.home-rail-list');
  const status = rail.querySelector('[data-glaze-blogroll-status]');
  const endpoint = new URL(button.dataset.glazeBlogrollRefresh, window.location.href);
  const current = [...list.querySelectorAll('[data-glaze-rail-link-id]')].map((card) => card.dataset.glazeRailLinkId);
  if (current.length) endpoint.searchParams.set('exclude', current.join(','));
  button.setAttribute('aria-busy', 'true');
  button.setAttribute('aria-disabled', 'true');
  list.setAttribute('aria-busy', 'true');
  status.textContent = '';
  status.classList.add('screen-reader-text');

  try {
    const response = await fetch(endpoint, { cache: 'no-store', mode: 'same-origin', signal: AbortSignal.timeout?.(10000) });
    if (!response.ok) throw new Error();
    const { html } = await response.json();
    if (typeof html !== 'string') throw new Error();
    const template = document.createElement('template');
    template.innerHTML = html.trim();

    if (list.contains(document.activeElement)) button.focus({ preventScroll: true });
    list.inert = true;
    await flipCards(list, false);
    list.replaceChildren(template.content);
    showDismissButtons(list);
    await flipCards(list, true);
    status.textContent = list.childElementCount ? status.dataset.success : status.dataset.empty;
    status.classList.toggle('screen-reader-text', list.childElementCount > 0);
  } catch {
    status.textContent = status.dataset.error;
    status.classList.remove('screen-reader-text');
  } finally {
    list.inert = false;
    list.removeAttribute('aria-busy');
    button.removeAttribute('aria-busy');
    button.removeAttribute('aria-disabled');
  }
}

for (const rail of document.querySelectorAll('[data-glaze-home-rail]')) {
  const refresh = rail.querySelector('[data-glaze-blogroll-refresh]');
  if (refresh) {
    refresh.closest('[data-glaze-blogroll-actions]').hidden = false;
    refresh.addEventListener('click', () => {
      if (!refresh.hasAttribute('aria-busy')) refreshBlogroll(rail, refresh);
    });
  }
  showDismissButtons(rail);

  rail.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-glaze-rail-close]');
    if (!button || button.disabled || refresh?.hasAttribute('aria-busy')) return;

    const card = button.closest('[data-glaze-rail-card]');
    const restoreFocus = button.matches(':focus-visible');
    const animate = !motionPreference.matches && typeof card.animate === 'function';
    button.disabled = true;
    card.inert = true;

    let exit;
    if (animate) {
      const offset = rail.dataset.glazeHomeRail === 'right' ? '18px' : '-18px';
      exit = card.animate(
        [{ opacity: 1 }, { opacity: 0, transform: `translateX(${offset}) scale(.9)`, filter: 'blur(4px)' }],
        { duration: 240, easing: 'cubic-bezier(.22,1,.36,1)', fill: 'forwards' },
      );
      await exit.finished.catch(() => {});
    }

    const neighbors = [...rail.querySelectorAll('[data-glaze-rail-card]:not([hidden]):not([inert])')];
    const before = animate ? neighbors.map((item) => item.getBoundingClientRect().top) : [];
    card.hidden = true;
    exit?.cancel();

    if (!refresh && !rail.querySelector('[data-glaze-rail-card]:not([hidden])')) rail.hidden = true;
    if (animate) {
      for (const [index, item] of neighbors.entries()) {
        moves.get(item)?.cancel();
        const offset = before[index] - item.getBoundingClientRect().top;
        if (Math.abs(offset) < 0.5) continue;

        const move = item.animate(
          [{ transform: `translateY(${offset}px)` }, { transform: 'translateY(0)' }],
          { duration: 420, easing: 'cubic-bezier(.22,1,.36,1)' },
        );
        moves.set(item, move);
        move.finished.catch(() => {});
      }
    }

    if (restoreFocus) {
      const next = neighbors.find((item) => card.compareDocumentPosition(item) & Node.DOCUMENT_POSITION_FOLLOWING) || neighbors.at(-1);
      const target = next?.querySelector('[data-glaze-rail-close]') || refresh || document.querySelector('#content a[href]');
      target?.focus({ preventScroll: true });
    }
  });
}
