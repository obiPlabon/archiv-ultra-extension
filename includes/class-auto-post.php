<?php
/**
 * Auto post class.
 * 
 * @package Archiv_Core
 */

namespace Archiv_Core;

defined( 'ABSPATH' ) || die();

class Auto_Post {

	const SUB_POSTS_META_KEY = '_archiv_sub_posts';

	public function __construct() {
		add_action( 'wp_insert_post', [ $this, 'create_posts' ], 10, 2 );
		add_filter( 'display_post_states', [ $this, 'add_post_states' ], 10, 2 );
		add_filter( 'the_title', [ $this, 'the_title' ], 10, 2 );
		add_filter( 'elementor/frontend/admin_bar/settings', [ $this, 'add_admin_bar_menu' ], 10, 2 );
	}

	public function add_admin_bar_menu( $settings ) {
		if ( ! is_singular( Post_Types::VIEWING_ROOM ) ) {
			return $settings;
		}

		$post = get_post( get_queried_object_id() );
		if ( empty( $post ) ) {
			return $settings;
		}

		$parent_post_id = $post->ID;
		if ( ! empty( $post->post_parent ) ) {
			$parent_post_id = $post->post_parent;
		}
		
		$post_ids = [ $parent_post_id ];
		$_ids     = get_post_meta( $parent_post_id, self::SUB_POSTS_META_KEY, true );

		if ( ! empty( $_ids ) ) {
			$post_ids = array_merge( $post_ids, $_ids );
		}

		if ( ! empty( $post_ids ) && ! empty( $settings['elementor_edit_page'] ) && ! empty( $settings['elementor_edit_page']['children'] ) ) {
			$children = [];
			$args     = [
				'post_type'      => Post_Types::VIEWING_ROOM,
				'post__in'       => $post_ids,
				'posts_per_page' => 3
			];
			
			$posts = get_posts( $args );
			foreach ( $posts as $post ) {
				$title = wp_trim_words( esc_html( $post->post_title ), 2, '...' );
				$sub_title = wp_trim_words( esc_html( $post->post_title ), 3, '' );

				$children[] = [
					'id'        => "elementor_edit_doc_{$post->ID}",
					'title'     => $title,
					'sub_title' => ( $post->ID !== $parent_post_id ? $sub_title : $this->get_base_post() ),
					'href'      => get_the_permalink( $post->ID ),
				];	
			}

			$settings['elementor_edit_page']['children'] = array_merge( $settings['elementor_edit_page']['children'], $children );
		}

		return $settings;
	}

	public function the_title( $title, $post_id ) {
		$post = get_post( $post_id );

		if ( ! is_admin() || get_post_type( $post ) !== Post_Types::VIEWING_ROOM || ! empty( $post->post_parent ) ) {
			return $title;
		}

		return $this->get_base_post();
	}

	public function add_post_states( $post_states, $post ) {
		if ( get_post_type( $post ) !== Post_Types::VIEWING_ROOM ) {
			return false;
		}

		if ( $post->post_parent ) {
			return false;
		}

		$sub_post_ids = get_post_meta( $post->ID, self::SUB_POSTS_META_KEY, true );

		if ( empty( $sub_post_ids ) ) {
			return false;
		}

		$post_states['title_as_states'] = strip_tags( $post->post_title );

		return $post_states;
	}

	public function create_posts( $post_id, $post ) {
		if ( get_post_type( $post_id ) !== Post_Types::VIEWING_ROOM || wp_is_post_autosave( $post_id ) ) {
			return false;
		}

		if ( empty( $post ) || 'auto-draft' === $post->post_status ) {
			return false;
		}

		$sub_post_ids = get_post_meta( $post_id, self::SUB_POSTS_META_KEY, true );

		if ( $post->post_parent || ! empty( $sub_post_ids ) ) {
			return false;
		}

		remove_action( 'wp_insert_post', [ $this, 'create_posts' ], 10 );

		$sub_post_ids = [];

		foreach ( $this->get_sub_posts() as $sub_post ) {
			$sub_post_ids[] = wp_insert_post( [
				'post_title'     => $sub_post,
				'post_author'    => $post->post_author,
				'post_status'    => $post->post_status,
				'comment_status' => $post->comment_status,
				'post_type'      => $post->post_type,
				'post_password'  => $post->post_password,
				'post_parent'    => $post_id,
			] );
		}

		update_post_meta( $post_id, self::SUB_POSTS_META_KEY, $sub_post_ids );

		add_action( 'wp_insert_post', [ $this, 'create_posts' ], 10, 2 );
	}

	protected function get_sub_posts() {
		return [
			'IMMERSION',
			'LIST OF WORKS',
		];
	}

	protected function get_base_post() {
		return 'ACADEMIC';
	}
}
