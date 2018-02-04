var eddRI = {

	/**
	 * Call methods needed.
	 *
	 * @since 1.0
	 * @returns {void}
	 */
	init: function() {
		var self = this;

		self.buttonHandler();
	},

	/**
	 * Actions to run when the activate button is clicked.
	 *
	 * @since 1.0
	 * @returns {void}
	 */
	buttonHandler: function() {
		var self = this;

		jQuery( '.edd-ri-button' ).on( 'click', function( event ) {
			var $target = jQuery( event.target ),
				data    = jQuery.extend( {}, $target.data(), {
					license: jQuery( 'input.edd-ri-license[data-slug="' + $target.data( 'slug' ) + '"]' ).val()
				} );

			jQuery.post( ajaxurl, data, function( response ) {

				if ( response.success ) {
					self.success( response, data );
					return;
				}
				self.fail( response, data );
			} );
		} );
	},

	/**
	 * Action that runs on success.
	 *
	 * @since 1.0
	 * @param {Object} response - The response we got from the server.
	 * @param {Object} data - The data that was passed-on to the AJAX call.
	 * @returns {void}
	 */
	success: function( response, data ) {

		// License was successfully activated.
		if ( 'edd_ri_activate_license' === data.action ) {

			// Change intro text.
			jQuery( '.edd-ri-register-text' ).addClass( 'hidden' );
			jQuery( '.edd-ri-thankyou-text' ).removeClass( 'hidden' );

			// Switch buttons.
			jQuery( '.license-actions.edd-ri-register' ).addClass( 'hidden' );
			jQuery( '.license-actions.edd-ri-update' ).removeClass( 'hidden' );

			// Hide the footer text & show the install button.
			jQuery( '.edd-ri-install-pending-text' ).addClass( 'hidden' );
			jQuery( '.edd-ri-install-button' ).removeClass( 'hidden' );

		}

		if ( 'edd_ri_install' === data.action ) {
			jQuery( '.edd-ri-response' ).html( response );
		}
		console.log( data );
		console.log( response );
	},

	/**
	 * Action that runs on fail.
	 *
	 * @since 1.0
	 * @param {Object} response - The response we got from the server.
	 * @param {Object} data - The data that was passed-on to the AJAX call.
	 * @returns {void}
	 */
	fail: function( response, data ) {
		console.log( data );
		console.log( response );
	}
};

eddRI.init();
