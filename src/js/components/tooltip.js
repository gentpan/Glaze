const menu = document.querySelector('.site-navigation .site-menu');
const comments = document.getElementById('comments');
const targetSelector = '.site-navigation .site-menu a, [data-glaze-tooltip]';

if (menu || comments || document.querySelector('[data-glaze-tooltip]')) {
  const tooltip = document.createElement('div');
  tooltip.className = 'glaze-tooltip glaze-panel';
  tooltip.hidden = true;
  tooltip.setAttribute('aria-hidden', 'true');
  document.body.append(tooltip);

  const commentTooltip = comments ? document.createElement('span') : null;
  const commentText = comments ? document.createElement('span') : null;
  if (commentTooltip) {
    commentTooltip.className = 'glaze-comment-tooltip';
    commentTooltip.dataset.show = 'false';
    commentTooltip.setAttribute('aria-hidden', 'true');
    commentText.className = 'glaze-comment-tooltip-text';
    commentTooltip.append(commentText);
    document.body.append(commentTooltip);
  }

  let active;
  let activeTooltip;

  function hide(target) {
    if (!active || active !== target) return;
    if (activeTooltip === commentTooltip) commentTooltip.dataset.show = 'false';
    else tooltip.hidden = true;
    active = undefined;
    activeTooltip = undefined;
  }

  function positionComment(bounds) {
    const alreadyShowing = commentTooltip.dataset.show === 'true';
    const anchor = active.closest('.comment-device-capsule')?.getBoundingClientRect() ?? bounds;
    const padding = Number.parseFloat(getComputedStyle(commentTooltip).paddingLeft) * 2;
    const width = Math.min(window.innerWidth - 16, Math.ceil(commentText.scrollWidth + padding));
    const x = Math.max(8, Math.min(bounds.left + (bounds.width - width) / 2, window.innerWidth - width - 8));
    const above = anchor.top - commentTooltip.offsetHeight - 8;

    if (!alreadyShowing) commentTooltip.style.transition = 'none';
    commentTooltip.style.width = width + 'px';
    commentTooltip.style.setProperty('--tt-x', x + 'px');
    commentTooltip.style.top = (above >= 8 ? above : anchor.bottom + 8) + 'px';
    commentTooltip.style.transformOrigin = above >= 8 ? '50% 100%' : '50% 0%';
    if (!alreadyShowing) {
      commentTooltip.getBoundingClientRect();
      commentTooltip.style.transition = '';
      commentTooltip.dataset.show = 'true';
    }
  }

  function position() {
    if (!active) return;
    const bounds = active.getBoundingClientRect();
    const inMenu = menu?.contains(active);
    if (!active.isConnected || bounds.bottom <= 0 || bounds.top >= window.innerHeight) {
      hide(active);
      return;
    }
    const scroller = inMenu ? menu : active.closest('.comment-meta-details');
    if (scroller) {
      const clip = scroller.getBoundingClientRect();
      if (bounds.right <= clip.left || bounds.left >= clip.right || bounds.bottom <= clip.top || bounds.top >= clip.bottom) {
        hide(active);
        return;
      }
    }

    if (activeTooltip === commentTooltip) {
      positionComment(bounds);
      return;
    }

    const width = tooltip.offsetWidth;
    const height = tooltip.offsetHeight;
    const dockGroup = inMenu ? menu : active.closest('.site-jump-navigation');
    const vertical = dockGroup && getComputedStyle(dockGroup).flexDirection === 'column';
    const right = bounds.right + 10;
    const beside = right + width + 8 <= window.innerWidth ? right : bounds.left - width - 10;
    const above = bounds.top - height - 10;
    const x = vertical ? beside : bounds.left + (bounds.width - width) / 2;
    const y = vertical ? bounds.top + (bounds.height - height) / 2 : above >= 8 ? above : bounds.bottom + 10;
    tooltip.style.left = Math.max(8, Math.min(x, window.innerWidth - width - 8)) + 'px';
    tooltip.style.top = Math.max(8, Math.min(y, window.innerHeight - height - 8)) + 'px';
  }

  function show(target) {
    const inMenu = menu?.contains(target);
    const label = inMenu ? target.querySelector('.site-nav-label')?.textContent : target.dataset.glazeTooltip || target.getAttribute('aria-label');
    if (!label || target.getAttribute('aria-expanded') === 'true' || (inMenu && !target.querySelector('.site-nav-icon'))) return;

    const bubble = commentTooltip && target.closest('.comment-meta-details, .comment-meta-actions') ? commentTooltip : tooltip;
    if (activeTooltip && activeTooltip !== bubble) hide(active);
    active = target;
    activeTooltip = bubble;
    if (bubble === commentTooltip) {
      commentText.textContent = label;
    } else {
      tooltip.textContent = label;
      tooltip.toggleAttribute('data-single-line', target.matches('[data-glaze-rss-copy], [data-glaze-tooltip-single-line]'));
      tooltip.toggleAttribute('data-site-menu', Boolean(inMenu));
      tooltip.hidden = false;
    }
    position();
  }

  function findTarget(node) {
    return node instanceof Element ? node.closest(targetSelector) : null;
  }

  document.addEventListener('pointerover', (event) => {
    const target = findTarget(event.target);
    if (event.pointerType === 'mouse' && target && !target.contains(event.relatedTarget)) show(target);
  });
  document.addEventListener('pointerout', (event) => {
    if (!active || active.matches(':focus-visible')) return;
    const boundary = active.closest('.comment-device-capsule') ?? active;
    if (boundary.contains(event.target) && !boundary.contains(event.relatedTarget)) hide(active);
  });
  document.addEventListener('focusin', (event) => {
    const target = findTarget(event.target);
    if (target?.matches(':focus-visible')) show(target);
  });
  document.addEventListener('focusout', (event) => {
    const target = findTarget(event.target);
    if (!target || target.matches(':hover')) return;
    const boundary = target.closest('.comment-device-capsule') ?? target;
    if (!boundary.contains(findTarget(event.relatedTarget))) hide(target);
  });
  document.addEventListener('click', (event) => hide(findTarget(event.target)));
  window.addEventListener('scroll', position, { capture: true, passive: true });
  window.addEventListener('resize', position);
  document.addEventListener('keydown', (event) => {
    if (active && event.key === 'Escape') hide(active);
  });
}
