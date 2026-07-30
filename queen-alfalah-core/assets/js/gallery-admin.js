(function ($) {
	'use strict';

	function initGalleryFields() {
		var $box = $('#qaf-core-details');
		var $source = $('#qaf-meta-_qaf_gallery_source');
		var $mediaType = $('#qaf-meta-_qaf_gallery_media_type');
		var $socialUrl = $('#qaf-meta-_qaf_video_url');
		var socialSources = ['instagram', 'tiktok', 'facebook', 'youtube'];

		if (!$box.length || !$source.length || !$mediaType.length) {
			return;
		}

		function fieldRow(metaKey) {
			return $box.find('[data-qaf-meta-key="' + metaKey + '"]');
		}

		function toggleFields() {
			var source = String($source.val() || '');
			var mediaType = String($mediaType.val() || 'photo');
			var isSocial = socialSources.indexOf(source) !== -1;
			var isAutomatic = source === '';
			var hasSocialUrl = $.trim(String($socialUrl.val() || '')) !== '';

			fieldRow('_qaf_video_url').prop('hidden', !(isSocial || isAutomatic));
			fieldRow('_qaf_gallery_local_video_id').prop(
				'hidden',
				!((source === 'local' || isAutomatic) && (mediaType === 'video' || mediaType === 'mixed'))
			);
			fieldRow('_qaf_gallery_embed_behavior').prop(
				'hidden',
				!(isSocial || (isAutomatic && hasSocialUrl))
			);

			validateSocialUrl();
		}

		function validateSocialUrl() {
			var source = String($source.val() || '');
			var value = $.trim(String($socialUrl.val() || ''));
			var hosts = window.qafGalleryAdmin && window.qafGalleryAdmin.hosts
				? window.qafGalleryAdmin.hosts
				: {};
			var message = window.qafGalleryAdmin && window.qafGalleryAdmin.invalidHost
				? window.qafGalleryAdmin.invalidHost
				: 'URL tidak sesuai dengan sumber sosial yang dipilih.';
			var invalid = false;

			if (value && socialSources.indexOf(source) !== -1) {
				try {
					var parsed = new URL(value);
					var allowed = Array.isArray(hosts[source]) ? hosts[source] : [];
					var hostname = parsed.hostname.toLowerCase().replace(/\.$/, '');
					invalid = parsed.protocol !== 'https:'
						|| Boolean(parsed.username || parsed.password)
						|| Boolean(parsed.port && parsed.port !== '443')
						|| allowed.indexOf(hostname) === -1;
				} catch (error) {
					invalid = true;
				}
			}

			$socialUrl
				.toggleClass('qaf-gallery-field--invalid', invalid)
				.attr('aria-invalid', invalid ? 'true' : 'false')
				.get(0)
				.setCustomValidity(invalid ? message : '');

			var $warning = $socialUrl.siblings('.qaf-gallery-url-warning');
			if (!$warning.length) {
				$warning = $('<span class="qaf-gallery-url-warning" role="alert"></span>');
				$socialUrl.after($warning);
			}
			$warning.text(invalid ? message : '').prop('hidden', !invalid);
		}

		$source.on('change', toggleFields);
		$mediaType.on('change', toggleFields);
		$socialUrl.on('input change', toggleFields);
		toggleFields();
	}

	function initAttachmentPickers() {
		$('.qaf-attachment-picker').each(function () {
			var $picker = $(this);
			var $input = $picker.find('input[type="hidden"]');
			var $label = $picker.find('.qaf-attachment-picker__label');
			var $remove = $picker.find('.qaf-attachment-picker__remove');
			var mimePrefix = String($picker.attr('data-mime-prefix') || '');
			var libraryType = mimePrefix.indexOf('/') > 0 ? mimePrefix.split('/')[0] : '';
			var frame;

			$picker.find('.qaf-attachment-picker__select').on('click', function (event) {
				event.preventDefault();

				if (!window.wp || !wp.media) {
					return;
				}

				if (frame) {
					frame.open();
					return;
				}

				frame = wp.media({
					title: window.qafGalleryAdmin ? qafGalleryAdmin.mediaTitle : 'Pilih video Galeri',
					button: {
						text: window.qafGalleryAdmin ? qafGalleryAdmin.mediaButton : 'Gunakan video ini'
					},
					library: libraryType ? { type: libraryType } : {},
					multiple: false
				});

				frame.on('select', function () {
					var attachment = frame.state().get('selection').first().toJSON();
					var attachmentMime = attachment ? String(attachment.mime || '') : '';
					var attachmentType = attachment ? String(attachment.type || '') : '';
					var matchesMime = !mimePrefix
						|| attachmentMime.indexOf(mimePrefix) === 0
						|| (libraryType && attachmentType === libraryType);

					if (!attachment || !matchesMime) {
						return;
					}

					$input.val(parseInt(attachment.id, 10) || 0).trigger('change');
					$label.text(attachment.filename || attachment.title || attachment.url || '');
					$remove.prop('hidden', false);
				});

				frame.open();
			});

			$remove.on('click', function (event) {
				event.preventDefault();
				$input.val('0').trigger('change');
				$label.text(
					window.qafGalleryAdmin
						? qafGalleryAdmin.emptyLabel
						: 'Belum ada video lokal dipilih.'
				);
				$remove.prop('hidden', true);
			});
		});
	}

	$(function () {
		initGalleryFields();
		initAttachmentPickers();
	});
})(jQuery);
