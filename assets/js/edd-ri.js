var eddRI = {
	init: function() {
		var self = this;

		// Close the overlay.
		jQuery( '.edd-ri-close-overlay' ).on( 'click', function( e ) {
			e.preventDefault();
			jQuery( '.edd-ri-overlay-installer' ).addClass( 'hidden' );
		} );

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

		jQuery( '.edd-ri-action' ).on( 'click', function( e ) {
			self.ajaxCall( e, {
				action: 'edd_ri_activate_license'
			} );
		} );
	},

	ajaxCall: function( e, args ) {
		var $target = jQuery( e.target ),
			data = jQuery.extend( {}, $target.data(), args, {
				license: jQuery( 'input.edd-ri-license' ).val()
			} );

		jQuery.post( ajaxurl, data, function( response ) {
			jQuery( 'button.edd-ri-install' ).attr( 'data-license', jQuery( 'input.edd-ri-license' ).val() );
		} );
	}
};

eddRI.init();
