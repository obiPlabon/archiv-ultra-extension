<?php
/**
 * Auto post class.
 * 
 * @package Archiv_Ultra_Extension
 */

namespace Archiv_Ultra_Extension;

defined( 'ABSPATH' ) || die();

class Auto_Post {

	const SUB_POSTS_META_KEY = '_archiv_sub_posts';

	public function __construct() {
		add_action( 'wp_trash_post', [ $this, 'on_trash_post' ] );
		add_action( 'save_post', [ $this, 'on_create_post' ], 10, 2 );
		add_filter( 'display_post_states', [ $this, 'add_post_states' ], 100, 2 );
		add_filter( 'elementor/frontend/admin_bar/settings', [ $this, 'add_admin_bar_menu' ], 10, 2 );

		add_filter( 'post_row_actions', [ $this, 'filter_post_row_actions' ], 15, 2 );
		add_filter( 'page_row_actions', [ $this, 'filter_post_row_actions' ], 15, 2 );

		add_action( 'load-post.php', [ $this, 'setup_redirect' ] );

		add_filter( 'elementor/document/urls/exit_to_dashboard', [ $this, 'update_exit_to_dashboard_url' ], 10, 2 );

		// Load parent viewing room post types only
		add_filter( 'archiv_main_room_only', [ $this, 'filter_main_room_only' ] );
	}

	/**
	 * Update query args and load parent rooms only.
	 * 
	 * Use `archiv_main_room_only` filter hook in AE - Post Blocks widget
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function filter_main_room_only( $args ) {
		if ( $args['post_type'] && is_string( $args['post_type'] ) && $args['post_type'] === Post_Types::VIEWING_ROOM ) {
			$args['post_parent'] = 0;
		}
		return $args;
	}

	/**
	 * Update exit to dashboard url to viewing rooms listings page.
	 *
	 * @param string $url
	 * @param object $document
	 *
	 * @return string
	 */
	public function update_exit_to_dashboard_url( $url, $document ) {
		$post = get_post( $document->get_main_id() );
		if ( empty( $post ) || $post->post_type !== Post_Types::VIEWING_ROOM ) {
			return $url;
		}
		
		return self_admin_url( add_query_arg( [ 'post_type' => Post_Types::VIEWING_ROOM ], 'edit.php' ) );
	}

	/**
	 * Redirect child viewing rooms to elementor editor.
	 *
	 * @return void
	 */
	public function setup_redirect() {
		$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
		$action  = isset( $_GET['action'] ) ? $_GET['action'] : '';

		if ( empty( $post_id ) || empty( $action ) ) {
			return;
		}

		$post = get_post( $post_id );
		if ( empty( $post ) || $post->post_type !== Post_Types::VIEWING_ROOM ) {
			return;
		}

		if ( $post->post_parent ) {
			if ( $action === 'edit' ) {
				$url = add_query_arg( [ 'action' => 'elementor' ] );

				wp_safe_redirect( $url );
				die();
			}

			if ( $action === 'trash' ) {
				$url = self_admin_url( add_query_arg( [ 'post_type' => Post_Types::VIEWING_ROOM ], 'edit.php' ) );

				wp_safe_redirect( $url );
				die();
			}
		}
	}

	/**
	 * Remove "Edit" link from child posts and add "Edit with Elementor".
	 *
	 * @param array $actions
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	public function filter_post_row_actions( $actions, $post ) {
		if ( empty( $post ) || $post->post_type !== Post_Types::VIEWING_ROOM ) {
			return $actions;
		}

		$document = \Elementor\Plugin::instance()->documents->get( $post->ID );
		if ( empty( $document ) ) {
			return $actions;
		}

		if ( $post->post_parent && isset( $actions['edit'] ) ) {
			unset( $actions['edit'] );
		}

		if ( $post->post_parent && isset( $actions['trash'] ) ) {
			unset( $actions['trash'] );
		}
 
		if ( $document->is_editable_by_current_user() && ! isset( $actions['edit_with_elementor'] ) ) {
			$actions['edit_with_elementor'] = sprintf(
				'<a href="%1$s">%2$s</a>',
				$document->get_edit_url(),
				__( 'Edit with Elementor', 'archiv-core' )
			);
		}

		return $actions;
	}

	public function on_trash_post( $post_id ) {
		$post = get_post( $post_id );

		if ( empty( $post ) || get_post_type( $post ) !== Post_Types::VIEWING_ROOM ) {
			return false;
		}

		if ( ! empty( $post->post_parent ) ) {
			return false;
		}

		$args = [
			'post_type'              => Post_Types::VIEWING_ROOM,
			'posts_per_page'         => -1,
			'post_parent'            => $post_id,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		];

		$query = new \WP_Query( $args );

		if ( $query->have_posts() ) {
			remove_action( 'wp_trash_post', [ $this, 'on_trash_post' ] );

			foreach ( $query->posts as $post ) {
				wp_trash_post( $post->ID );
			}

			add_action( 'wp_trash_post', [ $this, 'on_trash_post' ] );
		}
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
		$_ids = self::get_sub_posts( $parent_post_id );

		if ( ! empty( $_ids ) ) {
			$post_ids = array_merge( $post_ids, $_ids );
		}

		if ( ! empty( $post_ids ) && ! empty( $settings['elementor_edit_page'] ) && ! empty( $settings['elementor_edit_page']['children'] ) ) {
			$children = [];
			$args     = [
				'post_type'      => Post_Types::VIEWING_ROOM,
				'post__in'       => $post_ids,
				'posts_per_page' => count( $post_ids ),
				'orderby'        => 'post__in',
			];
			
			$posts = get_posts( $args );

			foreach ( $posts as $post ) {
				$title     = wp_trim_words( esc_html( $post->post_title ), 2, '...' );
				$sub_title = wp_trim_words( esc_html( $post->post_title ), 3, '' );

				$children[] = [
					'title'     => $title,
					'id'        => "elementor_edit_doc_{$post->ID}",
					'sub_title' => ( $post->ID !== $parent_post_id ? $sub_title : $this->get_base_post() ),
					'href'      => get_the_permalink( $post->ID ),
				];
			}

			$settings['elementor_edit_page']['children'] = array_merge( $settings['elementor_edit_page']['children'], $children );
		}

		return $settings;
	}

	public function add_post_states( $post_states, $post ) {
		if ( get_post_type( $post ) !== Post_Types::VIEWING_ROOM ) {
			return false;
		}

		if ( $post->post_parent ) {
			return false;
		}

		unset( $post_states['elementor'] );
		
		$post_states['archiv-base-post'] = $this->get_base_post();

		return $post_states;
	}

	public function on_create_post( $post_id, $post ) {
		if ( get_post_type( $post_id ) !== Post_Types::VIEWING_ROOM || wp_is_post_autosave( $post_id ) ) {
			return false;
		}

		if ( empty( $post ) || 'auto-draft' === $post->post_status || $post->post_parent ) {
			return false;
		}

		$sub_posts = self::get_sub_posts( $post_id );
		if ( ! empty( $sub_posts ) && count( $sub_posts ) > 0 ) {
			// Get all acf fields from parent
			$acf_fields = function_exists( 'get_fields' ) ? get_fields( $post_id ) : [];

			foreach ( $sub_posts as $sub_post ) {
				wp_update_post( [
					'ID'          => $sub_post,
					'post_status' => $post->post_status,
				] );
				
				// Copy acf fields from parent -> child
				if ( $acf_fields && function_exists( 'update_field' ) ) {
					foreach ( $acf_fields as $field_key => $field_value ) {
						update_field( $field_key, $field_value, $sub_post );
					}
				}
			}

			return;
		}

		remove_action( 'save_post', [ $this, 'on_create_post' ] );

		$sub_posts = [];
		foreach ( $this->get_sub_posts_title() as $sub_post ) {
			$sub_posts[] = wp_insert_post( [
				'post_title'     => $sub_post,
				'post_author'    => $post->post_author,
				'post_status'    => $post->post_status,
				'comment_status' => $post->comment_status,
				'post_type'      => $post->post_type,
				'post_password'  => $post->post_password,
				'post_parent'    => $post_id,
			] );
		}

		self::update_sub_posts( $post_id, $sub_posts );

		add_action( 'save_post', [ $this, 'on_create_post' ], 10, 2 );
	}

	protected function get_sub_posts_title() {
		return [
			'IMMERSION',
			'LIST OF WORKS',
		];
	}

	protected function get_base_post() {
		return 'ACADEMIC';
	}

	public static function get_sub_posts( $post_id ) {
		return get_post_meta( $post_id, self::SUB_POSTS_META_KEY, true );
	}

	public static function has_sub_posts( $post_id ) {
		$sub_posts = self::get_sub_posts( $post_id );
		return ( ! empty( $sub_posts ) && count( $sub_posts ) > 1 );
	}

	public static function update_sub_posts( $post_id, $sub_posts = [] ) {
		return update_post_meta( $post_id, self::SUB_POSTS_META_KEY, $sub_posts );
	}

	public static function delete_sub_posts( $post_id ) {
		return delete_post_meta( $post_id, self::SUB_POSTS_META_KEY );
	}
}
