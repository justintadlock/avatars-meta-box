jQuery( document ).ready( function() {

	// Copies the current tab item title to the box header.
	jQuery( '.amb-which-author' ).text( jQuery( '[name="post_author_override"]:checked + span' ).text() );

	jQuery( '[name="post_author_override"]' ).change(
		function() {
			jQuery( '.amb-which-author' ).text( jQuery( '[name="post_author_override"]:checked + span' ).text() );
		}
	);

} ); // ready()
