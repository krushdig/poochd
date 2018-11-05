window.wp = window.wp || {};

/**
 * Astoundify Favorites.
 *
 * @since 2.0.0
 */
(function( window, undefined ){

	window.wp = window.wp || {};
	var document = window.document;
	var $ = window.jQuery;

	/**
	 * Open Login PopUp.
	 *
	 * @since 2.1.0
	 */
	listifyAstoundifyFavoritesLoginPopUp = function( el ) {
		$.magnificPopup.close();

		return $.magnificPopup.open( {
			items: {
				src: '#add-favorite',
				type: 'inline',
			},
			tClose: listifySettings.l10n.magnific.tClose,
			tLoading: listifySettings.l10n.magnific.tLoading,
			fixedContentPos: false,
			fixedBgPos: true,
			overflowY: 'scroll'
		} );
	};

	/**
	 * Wait for DOM ready.
	 *
	 * @since 2.0.0
	 */
	$(document).ready( function() {

		// On listing page.
		$( '.job_listings' ).on( 'click', '.astoundify-favorites-link[data-user_id="0"]', function(e) {
			e.preventDefault();
			listifyAstoundifyFavoritesLoginPopUp();
		} );

		// On other pages.
		$( '.astoundify-favorites-link[data-user_id="0"]' ).click( function(e) {
			e.preventDefault();
			listifyAstoundifyFavoritesLoginPopUp();
		} );

	} );

})( window );
