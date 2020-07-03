(function( $ ) {
	'use strict';

	$(function () {
		function deleteBatch( limit = 1 ) {
			var stopped = $( '[name="stopped"]' ).is( ':checked' );
			if ( ! stopped ) {
				$.post(
					$( '[name="ajax_url"]' ).val(),
					{
						action  : 'actionscheduler_purging',
						limit   : limit,
					},
					function ( data ) {
						if ( false === data.success ) {
							var offset = data.current_offset,
							    limit  = data.current_limit,
							    html  = '<div class="batch">';

							html  += 'Deletion stopped when offset = ' + offset + ' and ';
							html  += 'limit = ' + limit;
							html += '</div>';

							$( '#deletionStatus' ).append( html );

						} else if ( true === data.success ) {
							var nextOffset = data.next_offset,
							    nextLimit  = data.next_limit,
							    html       = '<div class="batch">';

							html      += 'Deleted logs ' + data.deleted_logs + ' and ';
							html      += 'Deleted actions ' + data.deleted_actions;
							html      += '</div>';

							$( '#deletionStatus' ).append( html );

							setTimeout( function () {
								deleteBatch( data.limit );
							}, 500 );

						} else {
							throw 'An error occured: ' + JSON.stringify( data );
						}
					},
				)
				.fail( function( data ) {
					throw 'An error occured: ' + JSON.stringify( data );
				} );
			}
		}

		$( '#initiateDelete' ).click( function () {
			var limit  = $( '[name="limit"]' ).val();

			deleteBatch( limit );
		} );
	} );
} )( jQuery );