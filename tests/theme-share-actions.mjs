/** Run: node tests/theme-share-actions.mjs. Browser APIs are the only test doubles. */
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const script = fs.readFileSync(new URL('../queen-alfalah/assets/js/share.js', import.meta.url), 'utf8');
const url = 'https://school.test/berita/karya?bagian=1&bahasa=id';
function setup(navigator = {}, secure = true, ready = 'complete') {
  const control = () => ({ hidden: true, disabled: false, handlers: {}, addEventListener(event, handler) { this.handlers[event] = handler; } });
  const copy = control();
  const native = control();
  const status = { textContent: '', dataset: { copySuccess: 'copied', copyFallback: 'manual', shareError: 'unavailable' } };
  const input = { value: url, focus() { this.focused = true; }, select() { this.selected = true; }, setSelectionRange(start, end) { this.range = [start, end]; } };
  const fallback = { hidden: true, querySelector: () => input };
  const elements = { '[data-share-copy]': copy, '[data-share-native]': native, '[data-share-status]': status, '[data-share-fallback]': fallback };
  const panel = { dataset: { shareUrl: url, shareTitle: 'Karya & Prestasi Queen' }, querySelector: selector => elements[selector] };
  const document = { readyState: ready, querySelectorAll: () => [panel], addEventListener(event, handler) { this.readyHandler = handler; } };
  vm.runInNewContext(script, { document, window: { isSecureContext: secure }, navigator });
  return { copy, native, status, input, fallback, document };
}

let writes = [];
let shares = [];
let ui = setup({ clipboard: { writeText: async text => writes.push(text) }, share: async data => shares.push(data) });
assert.equal(ui.copy.hidden, false);
assert.equal(ui.native.hidden, false);
assert.equal(writes.length, 0, 'No clipboard action without a click.');
assert.equal(shares.length, 0, 'No automatic sharing.');
await ui.copy.handlers.click();
assert.deepEqual(writes, [url]);
assert.equal(ui.status.textContent, 'copied');
assert.equal(ui.copy.disabled, false);
assert.equal(ui.fallback.hidden, true);
await ui.native.handlers.click();
assert.equal(shares[0].url, url);
assert.equal(shares[0].title, 'Karya & Prestasi Queen');
assert.equal(ui.native.disabled, false);

ui = setup();
assert.equal(ui.native.hidden, true, 'Unsupported native sharing stays hidden.');
await ui.copy.handlers.click();
assert.equal(ui.status.textContent, 'manual');
assert.equal(ui.fallback.hidden, false);
assert.equal(ui.input.focused, true);
assert.equal(ui.input.selected, true);
assert.deepEqual(ui.input.range, [0, url.length]);
assert.equal(ui.copy.disabled, false);

ui = setup({ clipboard: { writeText: async () => { throw new Error('denied'); } } });
await ui.copy.handlers.click();
assert.equal(ui.status.textContent, 'manual', 'Denied clipboard access gets a usable fallback.');
assert.equal(ui.copy.disabled, false);

ui = setup({ clipboard: { writeText: async () => { throw new Error('must not call'); } }, share() {} }, false);
assert.equal(ui.native.hidden, true, 'Insecure contexts must not expose native sharing.');
await ui.copy.handlers.click();
assert.equal(ui.status.textContent, 'manual');

ui = setup({ share: async () => { throw { name: 'AbortError' }; } });
await ui.native.handlers.click();
assert.equal(ui.status.textContent, '', 'Cancel is not a failure and must not copy unexpectedly.');
assert.equal(ui.fallback.hidden, true);
assert.equal(ui.native.disabled, false);

ui = setup({ share: async () => { throw { name: 'NotAllowedError' }; } });
await ui.native.handlers.click();
assert.equal(ui.status.textContent, 'unavailable');
assert.equal(ui.native.disabled, false);

ui = setup({}, true, 'loading');
assert.equal(ui.copy.hidden, true, 'No dead buttons before initialization.');
ui.document.readyHandler();
assert.equal(ui.copy.hidden, false);
console.log('Theme share-action checks passed (copy, failure, native share, cancellation, fallback, readiness).');
