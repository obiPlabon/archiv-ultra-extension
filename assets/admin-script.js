;( function( $ ) {
    'use strict';

    $( document ).on( 'widget-added', function() {
        var $container = $( '.archiv-fields' );

        $container.sortable( {
            axis: 'y',
            stop: function( event, ui ) {
                var ids = [];
                $( '.archiv-fields__single' ).each( function() {
                    ids.push( parseInt( $( this ).find( '[type="hidden"]' ).val(), 10 ) );
                } );
                $(event.target).next().prop('value', ids.join(','));
            }
        } );

        console.log('worked');
    } );

} ( jQuery ) );