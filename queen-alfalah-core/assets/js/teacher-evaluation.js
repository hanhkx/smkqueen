(function () {
	'use strict';

	var form = document.querySelector('[data-qaf-evaluation-form]');
	if (!form) {
		return;
	}

	var tokenField = form.querySelector('[data-qaf-evaluation-token]');
	var stories = form.querySelectorAll('[data-qaf-story]');
	var storageKey = 'qaf_teacher_evaluation_token';

	function randomToken() {
		var bytes = new Uint8Array(16);
		if (window.crypto && window.crypto.getRandomValues) {
			window.crypto.getRandomValues(bytes);
			return Array.prototype.map.call(bytes, function (value) {
				return value.toString(16).padStart(2, '0');
			}).join('');
		}
		return (Date.now().toString(16) + Math.random().toString(16).slice(2) + Math.random().toString(16).slice(2)).slice(0, 32).padEnd(32, '0');
	}

	var token = '';
	try {
		token = window.localStorage.getItem(storageKey) || '';
		if (!/^[a-f0-9]{32}$/.test(token)) {
			token = randomToken();
			window.localStorage.setItem(storageKey, token);
		}
	} catch (error) {
		token = randomToken();
	}
	if (tokenField) {
		tokenField.value = token;
	}

	Array.prototype.forEach.call(stories, function (story) {
		var targetId = story.getAttribute('data-counter-target');
		var count = targetId ? document.getElementById(targetId) : null;
		function updateCount() {
			if (count) {
				count.textContent = String(story.value.length);
			}
		}
		story.addEventListener('input', updateCount);
		updateCount();
	});
}());
