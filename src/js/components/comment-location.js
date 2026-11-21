const section = document.getElementById('comments');
const markerSelector = '[data-comment-location-url]';
const queue = [];
let active = 0;

async function showLocation(marker) {
  try {
    const response = await fetch(marker.dataset.commentLocationUrl, { mode: 'same-origin' });
    if (!response.ok) return;
    const location = await response.json();
    if (typeof location?.flag !== 'string') return;
    const flagUrl = new URL(location.flag, window.location.href);
    if (flagUrl.origin !== window.location.origin || !/\/assets\/images\/flags\/1x1\/[a-z]{2}\.svg$/.test(flagUrl.pathname)) return;
    if (!marker.isConnected) return;

    const label = location.label || location.country;
    const flag = document.createElement('img');
    flag.width = 16;
    flag.height = 16;
    flag.alt = '';
    flag.referrerPolicy = 'no-referrer';
    flag.decoding = 'async';
    flag.src = flagUrl.href;
    const name = document.createElement('span');
    name.className = 'comment-location-name';
    name.textContent = location.place || location.country;
    marker.dataset.glazeTooltip = label;
    marker.setAttribute('aria-label', label);
    marker.tabIndex = 0;
    marker.append(flag, name);
    marker.hidden = false;
  } catch {
    // Location metadata can be omitted while the service is unavailable.
  }
}

function loadNext() {
  while (active < 2 && queue.length) {
    const marker = queue.shift();
    if (!marker.isConnected) continue;
    active++;
    showLocation(marker).finally(() => {
      active--;
      loadNext();
    });
  }
}

if (section && 'fetch' in window) {
  const visibility = 'IntersectionObserver' in window ? new IntersectionObserver((entries, observer) => {
    for (const { target, isIntersecting } of entries) {
      if (!isIntersecting) continue;
      observer.unobserve(target);
      const marker = target.querySelector(markerSelector);
      if (marker) queue.push(marker);
    }
    loadNext();
  }, { rootMargin: '200px 0px' }) : null;

  function observeList(list) {
    if (!list) return;
    for (const marker of list.querySelectorAll(markerSelector)) {
      if (visibility) {
        visibility.observe(marker.closest('.comment-meta'));
      } else {
        queue.push(marker);
      }
    }
    loadNext();
  }

  observeList(section.querySelector('[data-glaze-comment-list]'));
  const updates = new MutationObserver((records) => {
    for (const record of records) {
      for (const element of record.removedNodes) {
        if (visibility && element.matches?.('[data-glaze-comment-list]')) {
          for (const marker of element.querySelectorAll(markerSelector)) {
            visibility.unobserve(marker.closest('.comment-meta'));
          }
        }
      }
      for (const element of record.addedNodes) {
        if (element.matches?.('[data-glaze-comment-list]')) observeList(element);
      }
    }
  });
  updates.observe(section, { childList: true });
}
