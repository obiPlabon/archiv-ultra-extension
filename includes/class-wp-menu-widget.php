<?php
/**
 * WP menu widget class.
 * 
 * @package Archiv_Ultra_Extension
 */

namespace Archiv_Ultra_Extension;

defined( 'ABSPATH' ) || die();

use WP_Widget;
use Elementor\Plugin;

class WP_Menu_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'archiv-menu',
			__( 'Archiv Viewing Rooms', 'archiv' )
		);
 
		add_action( 'widgets_init', function() {
			register_widget( __CLASS__ );
		} );

		add_action( 'admin_enqueue_scripts', function() {
			wp_enqueue_style(
				'archiv-menu',
				archiv_ultra_extension()->plugin_url . 'assets/admin-style.css',
				null,
				archiv_ultra_extension()->version
			);

			wp_enqueue_script(
				'archiv-menu',
				archiv_ultra_extension()->plugin_url . 'assets/admin-script.js',
				[ 'jquery', 'jquery-ui-sortable' ],
				archiv_ultra_extension()->version
			);
		} );

		add_action( 'elementor/editor/after_enqueue_styles', function() {
			wp_enqueue_style(
				'archiv-menu',
				archiv_ultra_extension()->plugin_url . 'assets/admin-style.css',
				['elementor-select2'],
				archiv_ultra_extension()->version
			);

			wp_enqueue_script(
				'archiv-menu',
				archiv_ultra_extension()->plugin_url . 'assets/elementor-editor.js',
				[ 'jquery', 'jquery-ui-sortable', 'jquery-elementor-select2' ],
				archiv_ultra_extension()->version
			);

			wp_localize_script(
				'archiv-menu',
				'Archiv',
				[
					'endpoint' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'archiv_ajax_nonce' ),
					'action'   => 'archiv_get_viewing_rooms'
				]
			);
		} );

		add_action( 'wp_ajax_archiv_get_viewing_rooms', function() {
			$nonce = ! empty( $_GET['nonce'] ) ? $_GET['nonce'] : '';
			
			try {
				if ( ! wp_verify_nonce( $nonce, 'archiv_ajax_nonce' ) ) {
					throw new \Exception( 'Invalid request!', 404 );
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

				$query = new \WP_Query( $args );

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
			} catch ( \Exception $e ) {
				wp_send_json_error( $e->getMessage(), $e->getCode() );
			}
		} );

		add_action( 'wp_ajax_archiv_get_sub_viewing_rooms', function() {
			$nonce = ! empty( $_GET['nonce'] ) ? $_GET['nonce'] : '';
			
			try {
				if ( ! wp_verify_nonce( $nonce, 'archiv_ajax_nonce' ) ) {
					throw new \Exception( 'Invalid request!', 404 );
				}

				$base_id = ! empty( $_GET['base_id'] ) ? absint( $_GET['base_id'] ) : 0;
				if ( ! $base_id ) {
					throw new \Exception( 'Invalid request!', 404 );
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

				$query = new \WP_Query( $args );

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
		} );

		add_filter( 'posts_where', function ( $where, $query ) {
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
		}, 10, 2 );
	}

	public function widget( $args, $instance ) {
		echo '<pre style="font-size: 12px;">';
		print_r( $instance );
		echo '</pre>';
	}

	public function form( $instance ) {
		$base_id  = ! empty( $instance['viewing_rooms_base'] ) ? $instance['viewing_rooms_base'] : 0;
		$rooms = ( ! empty( $instance['viewing_rooms'] ) && is_array( $instance['viewing_rooms'] ) ) ? $instance['viewing_rooms'] : [];

		if ( $base_id && ! empty( $rooms ) ) {
			$base_room = wp_list_filter( $rooms, [ 'id' => $base_id ] );
			$base_room = ! empty( $base_room ) ? current( $base_room ) : [];
		}
		?>
		<div class="archiv-fields__group archiv-fields__group-base">
			<label for="<?php echo esc_attr( $this->get_field_id( 'viewing_rooms_base' ) ); ?>"><?php echo esc_html__( 'Select Viewing Room:', 'archiv' ); ?></label>
			<select
				class="widefat archiv-viewing-rooms-select2"
				id="<?php echo esc_attr( $this->get_field_id( 'viewing_rooms_base' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'viewing_rooms_base' ) ); ?>">
				<?php
				if ( ! empty( $base_room ) ) {
					printf( '<option selected value="%s">%s</option>', $base_room['id'], $base_room['title'] );
				}
				?>
			</select>
		</div>
		<p class="archiv-info"><i class="eicon-info-circle-o"></i> <?php esc_html_e( 'You can easily drag to sort the items.', 'archiv' ); ?></p>
		<ul class="archiv-fields">
			<?php
			$_data = [
				'id'    => 0,
				'slug'  => '',
				'title' => '',
			];

			for ( $i = 0; $i < 3; $i++ ) {
				$this->render_menu_item( $i, isset( $rooms[ $i ] ) ? $rooms[ $i ] : $_data );
			}
			?>
		</ul>
		<?php
	}

	protected function render_menu_item( $index = 0, $data ) {
		$prefix = 'viewing_rooms[' . $index . ']';
		?>
		<li class="archiv-fields__single">
			<div class="archiv-fields__group">
				<label for="<?php echo esc_attr( $this->get_field_id( $prefix . '[title]' ) ); ?>"><?php echo esc_html__( 'Title:', 'archiv' ); ?></label>
				<input class="widefat archiv-fields__field-title" id="<?php echo esc_attr( $this->get_field_id( $prefix . '[title]' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $prefix . '[title]' ) ); ?>" type="text" value="<?php echo esc_attr( $data['title'] ); ?>">
			</div>
			<div class="archiv-fields__group">
				<label for="<?php echo esc_attr( $this->get_field_id( $prefix . '[slug]' ) ); ?>"><?php echo esc_html__( 'Slug:', 'archiv' ); ?></label>
				<input class="widefat archiv-fields__field-slug" id="<?php echo esc_attr( $this->get_field_id( $prefix . '[slug]' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $prefix . '[slug]' ) ); ?>" type="text" value="<?php echo esc_attr( $data['slug'] ); ?>">
			</div>
			<input class="archiv-fields__field-id" type="hidden" name="<?php echo esc_attr( $this->get_field_name( $prefix . '[id]' ) ); ?>" value="<?php echo esc_attr( $data['id'] ); ?>">
			<input class="archiv-fields__field-index" type="hidden" name="<?php echo esc_attr( $this->get_field_name( $prefix . '[_index]' ) ); ?>" value="<?php echo $index; ?>">
		</li>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		if ( ! empty( $new_instance['viewing_rooms'] ) && is_array( $new_instance['viewing_rooms'] ) ) {
			usort( $new_instance['viewing_rooms'], [ $this, 'sort_items' ] );
		}

		return $new_instance;
	}

	public function sort_items( $item_a, $item_b ) {
		return ( $item_a['_index'] - $item_b['_index']);
	}
}

return new WP_Menu_Widget();