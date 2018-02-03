var eddRI = {

	/**
	 * Call methods needed.
	 *
	 * @since 1.0
	 * @returns {void}
	 */
	init: function() {
		var self = this;

		self.openOverlay();
		self.closeOverlay();
		self.activateButton();
	},

	/**
	 * Closes the overlay when the "Close" button is clicked.
	 *
	 * @since 1.0
	 * @returns {void}
	 */
	closeOverlay: function() {
		jQuery( '.edd-ri-close-overlay' ).on( 'click', function( e ) {
			e.preventDefault();
			jQuery( '.edd-ri-overlay-installer' ).addClass( 'hidden' );
		} );
	},

	/**
	 * Open the overlay.
	 *
	 * @since 1.0
	 * @returns {void}
	 */
	openOverlay: function() {

		// Open the overlay and add any properties necessary in there.
		jQuery( 'button.edd-ri-install' ).on( 'click', function( e ) {
			var $button = jQuery( e.target ),
				data = $button.data(),
				$input = jQuery( 'input.edd-ri-license' );

			jQuery( '.edd-ri-overlay-installer' ).removeClass( 'hidden' );

			// Empty the input field at first.
			$input.attr( 'value', '' );

			// If we've got a license saved, add it.
			if ( data.license ) {
				$input.attr( 'value', data.license );
			}

			// Add the data we need to the primary button.
			_.each( data, function( value, key ) {
				jQuery( '.button.edd-ri-action' ).attr( 'data-' + key, value );
			} );
		} );
	},

	/**
	 * Actions to run when the activate button is clicked.
	 *
	 * @since 1.0
	 * @returns {void}
	 */
	activateButton: function() {
		var self = this;

		jQuery( '.edd-ri-action' ).on( 'click', function( e ) {
			var $target = jQuery( e.target ),
				data = jQuery.extend( {}, $target.data(), {
					license: jQuery( 'input.edd-ri-license' ).val(),
					action: 'edd_ri_activate_license'
				} );

			jQuery.post( ajaxurl, data, function( response ) {

				if ( response.success ) {
					self.success();
					return;
				}
				self.fail( response );
			} );
		} );
	},

	/**
	 * Action that runs on success.
	 *
	 * @since 1.0
	 * @returns {void}
	 */
	success: function() {
		jQuery( 'button.edd-ri-install' ).attr( 'data-license', jQuery( 'input.edd-ri-license' ).val() );
	},

	/**
	 * Action that runs on fail.
	 *
	 * @since 1.0
	 * @returns {void}
	 */
	fail: function( response ) {
		console.log( response );
	}
};

eddRI.init();
