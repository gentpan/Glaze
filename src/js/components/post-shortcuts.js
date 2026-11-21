const shortcuts = document.querySelector('.site-jump-navigation');

if (shortcuts) {
  const topLink = shortcuts.querySelector('a[href="#top"], a[href="#site-top"]');
  const bottomLink = shortcuts.querySelector('a[href="#site-bottom"]');
  const commentsLink = shortcuts.querySelector('a[href="#comments"]');
  const comments = document.getElementById('comments');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  let scheduled = false;

  function update() {
    scheduled = false;
    const viewport = document.documentElement.clientHeight;
    const atTop = window.scrollY <= 1;
    const atBottom = document.scrollingElement.scrollHeight - viewport - window.scrollY <= 2;
    const inComments = commentsLink && comments && comments.getBoundingClientRect().top <= viewport / 4;
    const onlyTop = atBottom || Boolean(inComments);

    topLink.hidden = atTop;
    bottomLink.hidden = onlyTop;
    if (commentsLink) commentsLink.hidden = onlyTop || !comments;
    shortcuts.hidden = topLink.hidden && bottomLink.hidden && (!commentsLink || commentsLink.hidden);
  }

  function scheduleUpdate() {
    if (scheduled) return;
    scheduled = true;
    requestAnimationFrame(update);
  }

  shortcuts.addEventListener('click', (event) => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

    const link = event.target.closest('a[href^="#"]');
    const target = link && document.getElementById(link === topLink ? 'site-top' : link.hash.slice(1));
    if (!target) return;

    event.preventDefault();
    if (window.location.hash !== link.hash) history.pushState(history.state, '', link.hash);

    if (target.getAttribute('aria-hidden') !== 'true') {
      const focusTarget = target.querySelector('#comments-title') ?? target;
      if (!focusTarget.hasAttribute('tabindex')) focusTarget.tabIndex = -1;
      focusTarget.focus({ preventScroll: true });
    }

    if (link === topLink) window.scrollTo({ top: 0, behavior: 'instant' });
    else target.scrollIntoView({ block: 'start', behavior: reducedMotion.matches ? 'auto' : 'smooth' });
  });

  update();
  window.addEventListener('scroll', scheduleUpdate, { passive: true });
  window.addEventListener('resize', scheduleUpdate);
  window.addEventListener('pageshow', scheduleUpdate);
  if ('ResizeObserver' in window) new ResizeObserver(scheduleUpdate).observe(document.body);
}
