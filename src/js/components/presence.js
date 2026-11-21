const presence = document.querySelector('[data-glaze-presence]');
const onlineCount = presence?.querySelector('[data-glaze-online-count]');
const footer = presence?.closest('.site-footer-meta');
const totalViews = footer?.querySelector('[data-glaze-views]');
const lastVisit = footer?.querySelector('[data-glaze-last-visit]');
const lastVisitFlag = footer?.querySelector('[data-glaze-last-visit-flag]');

if (onlineCount && globalThis.crypto?.getRandomValues) {
  const identifier = () => Array.from(crypto.getRandomValues(new Uint8Array(16)), (value) => value.toString(16).padStart(2, '0')).join('');
  let visitor = identifier();
  let event = identifier();
  try {
    const stored = localStorage.getItem('westnote-glaze-visitor');
    if (/^[a-f0-9]{32}$/.test(stored)) visitor = stored;
    else localStorage.setItem('westnote-glaze-visitor', visitor);
  } catch {
    // Keep the identifier in memory when storage is unavailable.
  }

  let refreshing = false;
  async function refreshPresence() {
    if (refreshing || document.hidden) return;
    refreshing = true;
    const controller = new AbortController();
    const timeout = window.setTimeout(() => controller.abort(), 12000);
    try {
      const response = await fetch(presence.dataset.glazePresence, {
        method: 'POST',
        mode: 'same-origin',
        credentials: 'omit',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ visitor, event }),
        signal: controller.signal,
      });
      if (!response.ok) throw new Error('Presence request failed');
      const { count, views, lastVisit: recent, lastVisitFlag: flag } = await response.json();
      if (!Number.isSafeInteger(count) || count < 1) throw new Error('Invalid presence count');
      event = '';
      const locale = document.documentElement.lang || undefined;
      onlineCount.textContent = count.toLocaleString(locale);
      if (totalViews && Number.isSafeInteger(views)) totalViews.textContent = views.toLocaleString(locale);
      if (lastVisit && typeof recent === 'string' && recent) lastVisit.textContent = recent;
      if (lastVisitFlag && typeof flag === 'string') {
        const hasFlag = /^\/(?!\/)[^\s\\?#]*\/assets\/images\/flags\/1x1\/[a-z]{2}\.svg$/.test(flag);
        if (hasFlag && lastVisitFlag.getAttribute('src') !== flag) lastVisitFlag.src = flag;
        if (!hasFlag) lastVisitFlag.removeAttribute('src');
        lastVisitFlag.hidden = !hasFlag;
      }
      presence.hidden = false;
    } catch {
      presence.hidden = true;
    } finally {
      window.clearTimeout(timeout);
      refreshing = false;
    }
  }

  refreshPresence();
  window.setInterval(refreshPresence, 60000);
  document.addEventListener('visibilitychange', refreshPresence);
}
