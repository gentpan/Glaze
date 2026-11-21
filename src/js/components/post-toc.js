const trigger = document.querySelector('[data-glaze-toc-open]');
const dialog = document.getElementById('glaze-post-toc');

if (trigger && typeof dialog?.show === 'function') {
  const headings = [...document.querySelectorAll('.entry-content :is(h2, h3, h4, h5, h6)')].filter((heading) => heading.textContent.trim());
  const list = dialog.querySelector('.post-toc-list');

  if (headings.length) {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const minimumLevel = Math.min(...headings.map((heading) => Number(heading.tagName[1])));
    let sectionNumber = 0;
    let subsectionNumber = 0;
    const links = headings.map((heading) => {
      const level = Number(heading.tagName[1]);
      // Keep existing H2–H4 bookmarks stable when adding H5 and H6 to the list.
      const isSection = level <= 4;
      const prefix = isSection ? 'glaze-section-' : 'glaze-subsection-';
      let suffix = isSection ? ++sectionNumber : ++subsectionNumber;
      if (!heading.id || document.getElementById(heading.id) !== heading) {
        while (document.getElementById(prefix + suffix)) suffix++;
        heading.id = prefix + suffix;
      }
      const item = document.createElement('li');
      const link = document.createElement('a');
      item.style.setProperty('--toc-level', level - minimumLevel);
      const anchor = '#' + encodeURIComponent(heading.id);
      link.href = anchor;
      link.textContent = heading.textContent.trim();
      link.className = 'squircle';
      link.addEventListener('click', (event) => {
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        dialog.close();

        if (window.location.hash !== anchor) history.pushState(history.state, '', anchor);
        if (!heading.hasAttribute('tabindex')) heading.tabIndex = -1;
        heading.focus({ preventScroll: true });
        heading.scrollIntoView({ block: 'start', behavior: reducedMotion.matches ? 'auto' : 'smooth' });
      });
      item.append(link);
      list.append(item);
      return link;
    });

    let active = -1;
    let framePending = false;

    function showActiveLink() {
      const item = links[active].getBoundingClientRect();
      const viewport = list.getBoundingClientRect();
      if (item.top < viewport.top) list.scrollTop -= viewport.top - item.top;
      else if (item.bottom > viewport.bottom) list.scrollTop += item.bottom - viewport.bottom;
    }

    function updateActiveHeading() {
      const atBottom = window.scrollY > 0 && window.scrollY + window.innerHeight >= document.documentElement.scrollHeight - 2;
      let next = 0;
      for (let index = 0; index < headings.length; index++) {
        const bounds = headings[index].getBoundingClientRect();
        if (!bounds.height) continue;
        if (bounds.top > 96 && (!atBottom || bounds.top >= window.innerHeight)) break;
        next = index;
      }
      if (next === active) return;

      links[active]?.removeAttribute('aria-current');
      links[next].setAttribute('aria-current', 'location');
      active = next;
      if (dialog.open) showActiveLink();
    }

    function scheduleUpdate() {
      if (framePending) return;
      framePending = true;
      window.requestAnimationFrame(() => {
        framePending = false;
        updateActiveHeading();
      });
    }

    updateActiveHeading();
    window.addEventListener('scroll', scheduleUpdate, { passive: true });
    window.addEventListener('resize', scheduleUpdate);
    trigger.hidden = false;
    trigger.addEventListener('click', () => {
      if (dialog.open) {
        dialog.close();
        return;
      }
      updateActiveHeading();
      dialog.show();
      trigger.setAttribute('aria-expanded', 'true');
      showActiveLink();
    });
    dialog.addEventListener('close', () => {
      if (!dialog.open) trigger.setAttribute('aria-expanded', 'false');
    });
    document.addEventListener('click', (event) => {
      if (dialog.open && !dialog.contains(event.target) && !trigger.contains(event.target)) dialog.close();
    });
    document.addEventListener('keydown', (event) => {
      if (event.key !== 'Escape' || event.defaultPrevented || !dialog.open) return;
      event.preventDefault();
      dialog.close();
      trigger.focus({ preventScroll: true });
    });
  }
}
