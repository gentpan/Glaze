for (const group of document.querySelectorAll('.t-avatar-group')) {
  const items = [...group.querySelectorAll('.t-avatar')];

  for (const [activeIndex, item] of items.entries()) {
    item.addEventListener('pointerenter', (event) => {
      if (event.pointerType !== 'mouse') return;

      const style = getComputedStyle(group);
      const lift = Number.parseFloat(style.getPropertyValue('--avatar-lift'));
      const falloff = Number.parseFloat(style.getPropertyValue('--avatar-falloff'));
      for (const [index, sibling] of items.entries()) {
        sibling.style.transitionTimingFunction = 'var(--avatar-ease-in)';
        sibling.style.setProperty('--shift', (lift * falloff ** Math.abs(index - activeIndex)).toFixed(3) + 'px');
        sibling.style.setProperty('--scale-active', index === activeIndex ? 'var(--avatar-scale)' : '1');
      }
    });
  }

  group.addEventListener('pointerleave', () => {
    for (const item of items) {
      item.style.transitionTimingFunction = 'var(--avatar-ease-out)';
      item.style.removeProperty('--shift');
      item.style.removeProperty('--scale-active');
    }
  });
}
