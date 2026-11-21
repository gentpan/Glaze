const overlay = document.querySelector('[data-glaze-page-loading]');

if (overlay) {
  const spinner = overlay.querySelector('.glaze-page-loading-spinner');
  let revealTimer;
  let resetTimer;
  let spinnerCheck;
  let spinnerFrames;

  function reset() {
    clearTimeout(revealTimer);
    clearTimeout(resetTimer);
    clearTimeout(spinnerCheck);
    clearInterval(spinnerFrames);
    overlay.hidden = true;
  }

  function keepSpinnerMoving() {
    const animation = spinner?.getAnimations?.()[0];
    if (!animation) return;

    const initialTime = animation.currentTime;
    spinnerCheck = setTimeout(() => {
      if (animation.currentTime !== initialTime) return;

      // Safari can stall the animation clock while waiting for a navigation response.
      const startedAt = performance.now() - (initialTime ?? 0);
      animation.pause();
      const advance = () => { animation.currentTime = performance.now() - startedAt; };
      advance();
      spinnerFrames = setInterval(advance, 32);
    }, 100);
  }

  function revealWhileNavigating(event) {
    clearTimeout(revealTimer);
    revealTimer = setTimeout(() => {
      if (event.defaultPrevented) return;
      if (overlay.hidden) {
        overlay.hidden = false;
        keepSpinnerMoving();
      }
      clearTimeout(resetTimer);
      resetTimer = setTimeout(reset, 8000);
    }, 120);
  }

  function opensHere(target) {
    return !target || target.toLowerCase() === '_self';
  }

  window.addEventListener('click', (event) => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
    if (!link || link.origin !== location.origin || !opensHere(link.target) || link.hasAttribute('download')) return;
    if (link.pathname === location.pathname && link.search === location.search && link.href.includes('#')) return;
    revealWhileNavigating(event);
  });

  window.addEventListener('submit', (event) => {
    const form = event.target;
    if (event.defaultPrevented || !(form instanceof HTMLFormElement)) return;

    const submitter = event.submitter;
    const method = submitter?.getAttribute('formmethod') ?? form.method;
    if (method.toLowerCase() === 'dialog' || !opensHere(submitter?.getAttribute('formtarget') ?? form.target)) return;

    const action = submitter?.hasAttribute('formaction') ? submitter.formAction : form.action;
    try {
      if (new URL(action, document.baseURI).origin === location.origin) revealWhileNavigating(event);
    } catch {
      // Leave an invalid action to the browser's normal form handling.
    }
  });

  window.addEventListener('pageshow', reset);
  window.addEventListener('pagehide', reset);
  window.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') reset();
  });
}
