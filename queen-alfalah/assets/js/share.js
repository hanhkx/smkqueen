/** Local, opt-in sharing. No trackers, automatic shares, or clipboard reads. */
(() => {
  'use strict';

  const init = () => {
    document.querySelectorAll('.share-links[data-share-url]').forEach((panel) => {
      const copy = panel.querySelector('[data-share-copy]');
      const native = panel.querySelector('[data-share-native]');
      const status = panel.querySelector('[data-share-status]');
      const fallback = panel.querySelector('[data-share-fallback]');
      const input = fallback?.querySelector('input');
      const url = panel.dataset.shareUrl;
      if (!copy || !native || !status || !fallback || !input || !url) return;

      const manualCopy = () => {
        fallback.hidden = false;
        status.textContent = status.dataset.copyFallback;
        input.focus();
        input.select();
        input.setSelectionRange(0, input.value.length);
      };

      copy.hidden = false;
      copy.addEventListener('click', async () => {
        status.textContent = '';
        copy.disabled = true;
        try {
          if (!window.isSecureContext || !navigator.clipboard?.writeText) {
            manualCopy();
            return;
          }
          await navigator.clipboard.writeText(url);
          fallback.hidden = true;
          status.textContent = status.dataset.copySuccess;
        } catch (error) {
          manualCopy();
        } finally {
          copy.disabled = false;
        }
      });

      if (window.isSecureContext && typeof navigator.share === 'function') {
        native.hidden = false;
        native.addEventListener('click', async () => {
          status.textContent = '';
          native.disabled = true;
          try {
            await navigator.share({ title: panel.dataset.shareTitle, url });
          } catch (error) {
            if (error.name !== 'AbortError') status.textContent = status.dataset.shareError;
          } finally {
            native.disabled = false;
          }
        });
      }
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
