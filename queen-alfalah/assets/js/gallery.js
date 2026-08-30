( function () {
	'use strict';

	var instagramLoader = null;

	function processInstagram() {
		if (
			window.instgrm &&
			window.instgrm.Embeds &&
			typeof window.instgrm.Embeds.process === 'function'
		) {
			window.instgrm.Embeds.process();
		}
	}

	function loadInstagram() {
		if ( window.instgrm && window.instgrm.Embeds ) {
			processInstagram();
			return Promise.resolve();
		}

		if ( instagramLoader ) {
			return instagramLoader;
		}

		instagramLoader = new Promise( function ( resolve, reject ) {
			var script = document.getElementById( 'queen-instagram-embed-script' );
			var timeout = window.setTimeout( function () {
				reject( new Error( 'Instagram embed timed out.' ) );
			}, 10000 );

			function ready() {
				window.clearTimeout( timeout );
				processInstagram();
				resolve();
			}

			function failed() {
				window.clearTimeout( timeout );
				reject( new Error( 'Instagram embed failed to load.' ) );
			}

			if ( script ) {
				if ( script.dataset.galleryLoaded === 'true' ) {
					if ( window.instgrm && window.instgrm.Embeds ) {
						ready();
					} else {
						failed();
					}
					return;
				}
				script.addEventListener( 'load', ready, { once: true } );
				script.addEventListener( 'error', failed, { once: true } );
				return;
			}

			script = document.createElement( 'script' );
			script.id = 'queen-instagram-embed-script';
			script.async = true;
			script.src = 'https://www.instagram.com/embed.js';
			script.addEventListener( 'load', function () {
				script.dataset.galleryLoaded = 'true';
				ready();
			}, { once: true } );
			script.addEventListener( 'error', failed, { once: true } );
			document.body.appendChild( script );
		} );

		return instagramLoader;
	}

	function activateEmbed( root ) {
		if ( ! root || root.dataset.galleryActivated === 'true' ) {
			return;
		}

		var content = root.querySelector( '.gallery-embed__content' );
		var button = root.querySelector( '[data-gallery-load]' );
		var status = root.querySelector( '[data-gallery-status]' );
		var shouldMoveFocus = button && button === document.activeElement;

		if ( ! content ) {
			return;
		}

		root.dataset.galleryActivated = 'true';
		content.hidden = false;

		content.querySelectorAll( 'iframe[data-src]' ).forEach( function ( iframe ) {
			if ( ! iframe.getAttribute( 'src' ) ) {
				iframe.setAttribute( 'src', iframe.dataset.src );
			}
		} );

		if ( button ) {
			button.remove();
		}

		root.classList.add( 'is-loaded' );
		if ( status ) {
			status.textContent = root.dataset.loadedMessage || '';
		}
		if ( shouldMoveFocus ) {
			try {
				content.focus( { preventScroll: true } );
			} catch ( error ) {
				content.focus();
			}
		}

		if ( root.dataset.provider === 'instagram' ) {
			loadInstagram().catch( function () {
				root.classList.add( 'has-load-error' );
				if ( status ) {
					status.textContent = root.dataset.errorMessage || '';
				}
			} );
		}
	}

	function initGalleryEmbeds() {
		document.querySelectorAll( '[data-gallery-embed][data-behavior="auto"]' ).forEach( activateEmbed );

		document.addEventListener( 'click', function ( event ) {
			var trigger = event.target.closest( '[data-gallery-load]' );
			if ( ! trigger ) {
				return;
			}

			activateEmbed( trigger.closest( '[data-gallery-embed]' ) );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initGalleryEmbeds, { once: true } );
	} else {
		initGalleryEmbeds();
	}
}() );
