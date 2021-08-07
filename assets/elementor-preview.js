;( function( $ ) {
	'use strict';

	$( window ).on( 'elementor/frontend/init', function() {
		elementorFrontend.hooks.addAction( 'frontend/element_ready/wp-widget-archiv-menu.default', function( $scope ) {
            $scope.find( '.archiv-menu__item-link' ).on( 'click', function() {
                var link = $( this ).attr( 'href' );

                if ( link ) {
                    window.parent.location = link;
                }
            } );
		} );
	} );
} ( jQuery ) );
