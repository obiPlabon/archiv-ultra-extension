;( function( $ ) {
    'use strict';

    $( function() {
        elementor.hooks.addAction( 'panel/widgets/wp-widget-archiv-menu/controls/wp_widget/loaded', function() {
            var $container = $( '.archiv-fields' );
    
            $container.sortable( {
                axis: 'y',
                stop: function( event, ui ) {
                    $( '.archiv-fields__single' ).each( function( index, item ) {
                        $( item )
                            .find( '.archiv-fields__field-index' )
                            .prop( 'value', index )
                            .trigger( 'change' );
                    } );
                }
            } );
        } );
    } );
} ( jQuery ) );
