<?php
/**
 * Ajax request handler.
 * 
 * @package Archiv_Ultra_Extension
 */

namespace Archiv_Ultra_Extension;

defined( 'ABSPATH' ) || die();

use Exception;
use WP_Query;

class Ajax {

	public function __construct() {
		add_action( 'wp_ajax_archiv_get_viewing_rooms', [ $this, 'get_viewing_rooms' ] );
		add_action( 'wp_ajax_archiv_get_sub_viewing_rooms', [ $this, 'get_sub_viewing_rooms' ] );
		add_filter( 'posts_where', [ $this, 'query_viewing_rooms_including_base_room' ], 10, 2 );
	}

	public function query_viewing_rooms_including_base_room( $where, $query ) {
		if ( ! $query->get( 'archiv_include_base' ) ) {
			return $where;
		}

		$post_parent = filter_var( $query->get( 'post_parent' ), FILTER_VALIDATE_INT );
		if ( ! $post_parent ) {
			return $where;
		}

		global $wpdb;
		$where .= " OR $wpdb->posts.ID = $post_parent";

		return $where;
	}

	public function get_viewing_rooms() {
		$nonce = ! empty( $_GET['nonce'] ) ? $_GET['nonce'] : '';
		
		try {
			if ( ! wp_verify_nonce( $nonce, 'archiv_ajax_nonce' ) ) {
				throw new Exception( 'Invalid request!', 404 );
			}

			$search = ! empty( $_GET['search'] ) ? sanitize_title_for_query( $_GET['search'] ) : '';
			if ( empty( $search ) ) {
				wp_send_json_success( [] );
			}

			$args = [
				'post_type'              => Post_Types::VIEWING_ROOM,
				'post_status'            => 'publish',
				'posts_per_page'         => 10,
				'post_parent'            => 0,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'cache_results'          => false,
				's'                      => $search,
				'orderby'                => 'relevance',
				'order'                  => 'ASC',
			];

			$query = new WP_Query( $args );

			if ( ! $query->have_posts() ) {
				wp_send_json_success( [] );
			}

			$retdata = [];
			foreach ( $query->posts as $post ) {
				$retdata[] = [
					'id'   => $post->ID,
					'text' => $post->post_title,
				];
			}

			wp_send_json_success( $retdata );
		} catch ( Exception $e ) {
			wp_send_json_error( $e->getMessage(), $e->getCode() );
		}
	}

	public function get_sub_viewing_rooms() {
		$nonce = ! empty( $_GET['nonce'] ) ? $_GET['nonce'] : '';
		
		try {
			if ( ! wp_verify_nonce( $nonce, 'archiv_ajax_nonce' ) ) {
				throw new Exception( 'Invalid request!', 404 );
			}

			$base_id = ! empty( $_GET['base_id'] ) ? absint( $_GET['base_id'] ) : 0;
			if ( ! $base_id ) {
				throw new Exception( 'Invalid request!', 404 );
			}

			$args = [
				'post_type'              => Post_Types::VIEWING_ROOM,
				'post_status'            => 'publish',
				'posts_per_page'         => 3,
				'post_parent'            => $base_id,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'cache_results'          => false,
				'archiv_include_base'    => true,
				'order'                  => 'ASC',
			];

			$query = new WP_Query( $args );

			if ( ! $query->have_posts() ) {
				wp_send_json_success( [] );
			}

			$retdata = [];
			foreach ( $query->posts as $post ) {
				$retdata[ $post->ID ] = [
					'id'    => $post->ID,
					'title' => $post->post_title,
					'slug'  => $post->post_name,
				];
			}

			// Make sure base data is always at the top.
			$base_data = $retdata[ $base_id ];
			unset( $retdata[ $base_id ] );
			array_unshift( $retdata, $base_data );
			unset( $base_data );

			wp_send_json_success( $retdata );
		} catch ( \Exception $e ) {
			wp_send_json_error( $e->getMessage(), $e->getCode() );
		}
	}
}

return new Ajax();
