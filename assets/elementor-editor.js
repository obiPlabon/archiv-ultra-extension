;( function( $ ) {
    'use strict';

    $( function() {
        elementor.hooks.addAction( 'panel/widgets/wp-widget-archiv-menu/controls/wp_widget/loaded', function() {
            var $container = $( '.archiv-fields' ),
                $archivSelect2 = $( '.archiv-viewing-rooms-select2' );
    
            $container.sortable( {
                axis: 'y',
                stop: function( event, ui ) {
                    $( '.archiv-fields__single' ).each( function( index, item ) {
                        $( item )
                            .find( '.archiv-fields__field-index' )
                            .prop( 'value', ( index + 1 ) )
                            .trigger( 'change' );
                    } );
                }
            } );

            $archivSelect2.select2( {
                minimumInputLength: 2,
                ajax: {
                    url: Archiv.endpoint,
                    dataType: 'json',
                    data: function ( params ) {
                        var query = {
                            search: params.term,
                            action: Archiv.action,
                            nonce: Archiv.nonce
                        }

                        return query;
                    },
                    processResults: function (data) {
                        var results = {
                            'id'  : -1,
                            'text': 'No results found'
                        };

                        if (data.success) {
                            results = data.data;
                        }

                        return {
                          results: results
                        };
                    }
                }
            } ).on( 'select2:select', function( e ) {
                $.get( Archiv.endpoint, {
                    action : 'archiv_get_sub_viewing_rooms',
                    base_id: e.params.data.id,
                    nonce  : Archiv.nonce
                } )
                .done( function( response ) {
                    if ( ! response.success ) {
                        return;
                    }

                    $( '.archiv-fields__single' ).each( function( index, item ) {
                        var data = response.data[ index ],
                            $item = $( item );

                        $item.find('.archiv-fields__field-title').val( data.title ).trigger('change');
                        $item.find('.archiv-fields__field-slug').val( data.slug );
                        $item.find('.archiv-fields__field-id').val( data.id );
                    } );
                } );
            } );
        } );
    } );
} ( jQuery ) );
