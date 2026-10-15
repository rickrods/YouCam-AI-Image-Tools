(function( $ ) {
	'use strict';

	$( function() {
		$( document ).on( 'click', '.ycait-select-image', function( event ) {
			event.preventDefault();
			if ( ! window.wp || ! wp.media ) {
				window.alert( 'The WordPress Media Library did not load. Refresh this page and try again.' );
				return;
			}
			var target = $( this ).data( 'target' );
			var frame = wp.media( { title: 'Choose model image', button: { text: 'Use this image' }, multiple: false, library: { type: 'image' } } );
			frame.on( 'select', function() {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				$( '#' + target ).val( attachment.id ).trigger( 'change' );
				var imageUrl = attachment.url || ( attachment.sizes && attachment.sizes.full ? attachment.sizes.full.url : '' );
				$( '#' + target + '_preview' ).empty().append( $( '<img>', { src: imageUrl, width: 80, height: 80, alt: '' } ).css( 'object-fit', 'cover' ) );
			} );
			frame.open();
		} );
		$( document ).on( 'click', '.ycait-remove-image', function( event ) {
			event.preventDefault();
			var target = $( this ).data( 'target' );
			$( '#' + target ).val( '' ).trigger( 'change' );
			$( '#' + target + '_preview' ).empty();
		} );
	} );

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

})( jQuery );
