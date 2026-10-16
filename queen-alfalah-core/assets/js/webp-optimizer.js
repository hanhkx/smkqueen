(function () {
	'use strict';

	var config = window.qafWebpOptimizer || {};
	var start = document.getElementById('qaf-webp-start');
	var rebuild = document.getElementById('qaf-webp-rebuild');
	var replace = document.getElementById('qaf-webp-replace');
	var progressBox = document.getElementById('qaf-webp-progress');
	var progress = progressBox ? progressBox.querySelector('progress') : null;
	var status = progressBox ? progressBox.querySelector('p') : null;
	var resultBox = document.getElementById('qaf-webp-result');
	var resultText = resultBox ? resultBox.querySelector('p') : null;

	if (!start || !rebuild || !replace || !progressBox || !progress || !status || !resultBox || !resultText) {
		return;
	}

	function setBusy(busy) {
		start.disabled = busy;
		rebuild.disabled = busy;
		replace.disabled = busy;
	}

	function showFailure(message) {
		setBusy(false);
		resultBox.hidden = false;
		resultBox.className = 'notice notice-error inline';
		resultText.textContent = message || (config.i18n && config.i18n.failed) || 'Optimasi gagal.';
	}

	async function runBatch(page, force, deleteOriginals) {
		var body = new URLSearchParams();
		body.set('action', config.action);
		body.set('nonce', config.nonce);
		body.set('page', String(page));
		body.set('force', force ? '1' : '0');
		body.set('deleteOriginals', deleteOriginals ? '1' : '0');

		var response = await fetch(config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
			body: body.toString()
		});
		var payload = await response.json();
		if (!response.ok || !payload.success) {
			throw new Error(payload && payload.data && payload.data.message ? payload.data.message : 'Permintaan optimasi gagal.');
		}

		var data = payload.data;
		var percentage = Math.min(100, Math.round((data.page / data.totalPages) * 100));
		progress.value = percentage;
		status.textContent = 'Batch ' + data.page + ' dari ' + data.totalPages + ' — ' + data.message;

		if (!data.done) {
			return runBatch(page + 1, force, deleteOriginals);
		}
		setBusy(false);
		resultBox.hidden = false;
		resultBox.className = 'notice notice-success inline';
		resultText.textContent = ((config.i18n && config.i18n.done) || 'Optimasi selesai.') + ' ' + data.message;
	}

	function begin(force, deleteOriginals) {
		setBusy(true);
		progressBox.hidden = false;
		resultBox.hidden = true;
		progress.value = 0;
		status.textContent = (config.i18n && config.i18n.running) || 'Sedang memproses…';
		runBatch(1, force, deleteOriginals).catch(function (error) {
			showFailure(error.message);
		});
	}

	start.addEventListener('click', function () { begin(false, false); });
	rebuild.addEventListener('click', function () { begin(true, false); });
	replace.addEventListener('click', function () { begin(false, true); });
}());
