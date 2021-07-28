<?php
/**
 * Post types class.
 * 
 * @package Archiv_Ultra_Extension
 */

namespace Archiv_Ultra_Extension;

defined( 'ABSPATH' ) || die();

class Post_Types {

	const VIEWING_ROOM = 'viewing-room';

	public function __construct() {
		add_action( 'init', [ $this, 'register_viewing_room' ] );
	}

	public function register_viewing_room() {
		$labels = [
			'name'                     => __( 'Viewing Rooms', 'archiv-core' ),
			'singular_name'            => __( 'Viewing Room', 'archiv-core' ),
			'menu_name'                => __( 'Viewing Rooms', 'archiv-core' ),
			'all_items'                => __( 'All Viewing Rooms', 'archiv-core' ),
			'add_new'                  => __( 'Add New Room', 'archiv-core' ),
			'add_new_item'             => __( 'Add new Viewing Room', 'archiv-core' ),
			'edit_item'                => __( 'Edit Viewing Room', 'archiv-core' ),
			'new_item'                 => __( 'New Viewing Room', 'archiv-core' ),
			'view_item'                => __( 'View Viewing Room', 'archiv-core' ),
			'view_items'               => __( 'View Viewing Rooms', 'archiv-core' ),
			'search_items'             => __( 'Search Viewing Rooms', 'archiv-core' ),
			'not_found'                => __( 'No Viewing Rooms found', 'archiv-core' ),
			'not_found_in_trash'       => __( 'No Viewing Rooms found in trash', 'archiv-core' ),
			'parent'                   => __( 'Parent Viewing Room:', 'archiv-core' ),
			'featured_image'           => __( 'Featured image for this Viewing Room', 'archiv-core' ),
			'set_featured_image'       => __( 'Set featured image for this Viewing Room', 'archiv-core' ),
			'remove_featured_image'    => __( 'Remove featured image for this Viewing Room', 'archiv-core' ),
			'use_featured_image'       => __( 'Use as featured image for this Viewing Room', 'archiv-core' ),
			'archives'                 => __( 'Viewing Room archives', 'archiv-core' ),
			'insert_into_item'         => __( 'Insert into Viewing Room', 'archiv-core' ),
			'uploaded_to_this_item'    => __( 'Upload to this Viewing Room', 'archiv-core' ),
			'filter_items_list'        => __( 'Filter Viewing Rooms list', 'archiv-core' ),
			'items_list_navigation'    => __( 'Viewing Rooms list navigation', 'archiv-core' ),
			'items_list'               => __( 'Viewing Rooms list', 'archiv-core' ),
			'attributes'               => __( 'Viewing Rooms attributes', 'archiv-core' ),
			'name_admin_bar'           => __( 'Viewing Room', 'archiv-core' ),
			'item_published'           => __( 'Viewing Room published', 'archiv-core' ),
			'item_published_privately' => __( 'Viewing Room published privately.', 'archiv-core' ),
			'item_reverted_to_draft'   => __( 'Viewing Room reverted to draft.', 'archiv-core' ),
			'item_scheduled'           => __( 'Viewing Room scheduled', 'archiv-core' ),
			'item_updated'             => __( 'Viewing Room updated.', 'archiv-core' ),
			'parent_item_colon'        => __( 'Parent Viewing Room:', 'archiv-core' ),
		];
	
		$args = [
			'label'                 => __( 'Viewing Rooms', 'archiv-core' ),
			'labels'                => $labels,
			'description'           => '',
			'public'                => true,
			'publicly_queryable'    => true,
			'show_ui'               => true,
			'show_in_rest'          => true,
			'rest_base'             => '',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'has_archive'           => false,
			'show_in_menu'          => true,
			'show_in_nav_menus'     => true,
			'delete_with_user'      => false,
			'exclude_from_search'   => false,
			'capability_type'       => 'post',
			'map_meta_cap'          => true,
			'hierarchical'          => true,
			'rewrite'               => [ 'slug' => 'viewing-room', 'with_front' => true ],
			'query_var'             => true,
			'menu_position'         => 6,
			'menu_icon'             => 'dashicons-groups',
			'supports'              => [ 'title', 'thumbnail', 'custom-fields', 'page-attributes', 'elementor' ],
			'show_in_graphql'       => false,
		];
	
		register_post_type( self::VIEWING_ROOM, $args );
	}
}
