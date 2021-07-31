;( function( $ ) {
	'use strict';

	$( window ).on( 'elementor/frontend/init', function() {
		elementorFrontend.hooks.addAction( 'frontend/element_ready/wp-widget-archiv-menu.default', function( $scope ) {
            $scope.find( '.archiv-menu__item-link' ).on( 'click', function() {
                // var id = parseInt( $( this ).attr( 'href' ), 10 );

                window.parent.location = $( this ).attr( 'href' );

                // elementorCommon.api.internal( 'panel/state-loading' );
                // elementorCommon.api.run( 'editor/documents/switch', {
                //     id: id,
                //     mode: 'autosave',
                // } ).finally( function () {
                //     return elementorCommon.api.internal( 'panel/state-ready' );
                // } );
            } );
		} );
	} );
} ( jQuery ) );
