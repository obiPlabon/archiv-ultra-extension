<?php
/**
 * Archiv menu widget class.
 * 
 * @package Archiv_Ultra_Extension
 */

namespace Archiv_Ultra_Extension;

defined( 'ABSPATH' ) || die();

use Elementor\Plugin;
use WP_Widget;

class Archiv_Menu extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'archiv-menu',
			__( 'Archiv Viewing Rooms', 'archiv' )
		);

		add_action( 'admin_enqueue_scripts', function() {
			wp_enqueue_style(
				'archiv-menu-admin',
				archiv_ultra_extension()->plugin_url . 'assets/admin-style.css',
				null,
				archiv_ultra_extension()->version
			);

			wp_enqueue_script(
				'archiv-menu-admin',
				archiv_ultra_extension()->plugin_url . 'assets/admin-script.js',
				[ 'jquery', 'jquery-ui-sortable' ],
				archiv_ultra_extension()->version
			);
		} );

		add_action( 'elementor/preview/enqueue_scripts', function() {
			wp_enqueue_script(
				'archiv-menu-preview',
				archiv_ultra_extension()->plugin_url . 'assets/elementor-preview.js',
				[ 'elementor-frontend' ],
				archiv_ultra_extension()->version,
				true
			);
		} );

		add_action( 'wp_enqueue_scripts', function() {
			wp_enqueue_style(
				'archiv-menu',
				archiv_ultra_extension()->plugin_url . 'assets/style.css',
				null,
				archiv_ultra_extension()->version
			);
		} );
	}

	protected function have_rooms( $rooms ) {
		if ( empty( $rooms ) ) {
			return false;
		}

		$have_rooms = array_filter( $rooms, function( $room ) {
			return ! empty( $room['id'] );
		} );

		return count( $have_rooms );
	}

	public function widget( $args, $instance ) {
		$rooms = ( ! empty( $instance['viewing_rooms'] ) && is_array( $instance['viewing_rooms'] ) ) ? $instance['viewing_rooms'] : [];

		if ( ! $this->have_rooms( $rooms ) ) {
			return;
		}

		if ( is_admin() ||
			! empty( $_GET['elementor-preview'] ) ||
			( isset( $_GET['action'] ) && $_GET['action'] === 'elementor' )
			) {
			$this->render_backend( $rooms );
		} else {
			$this->render_frontend( $rooms );
		}
	}

	protected function get_edit_url( $id ) {
		$url = add_query_arg( [
			'post'   => $id,
			'action' => 'elementor',
		], admin_url( 'post.php' ) );

		return esc_url( $url );
	}

	protected function render_backend( $rooms ) {
		?>
		<ul class="archiv-menu">
			<?php foreach ( $rooms as $room ) : ?>
				<li class="archiv-menu__item">
					<a title="<?php esc_attr_e( 'Click to open on editor', 'archiv' ); ?>" class="archiv-menu__item-link <?php echo is_single( $room['id'] ) ? 'archiv--is-active' : ''; ?>" href="<?php echo $this->get_edit_url( $room['id'] ); ?>"><?php echo $room['title']; ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	protected function render_frontend( $rooms ) {
		$room_ids = wp_list_pluck( $rooms, 'id' );
		$rooms    = $this->get_viewing_rooms_by_ids( $room_ids );
		?>
		<ul class="archiv-menu">
			<?php foreach ( $rooms as $room ) : ?>
				<li class="archiv-menu__item">
					<a class="archiv-menu__item-link <?php echo is_single( $room->ID ) ? 'archiv--is-active' : ''; ?>" href="<?php the_permalink( $room ); ?>"><?php echo get_the_title( $room ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	protected function get_viewing_rooms_by_ids( $ids ) {
		$args = [
			'post_type'              => Post_Types::VIEWING_ROOM,
			'post_status'            => 'publish',
			'post__in'               => $ids,
			'posts_per_page'         => count( $ids ),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'cache_results'          => false,
			'orderby'                => 'post__in',
		];

		$query = new \WP_Query( $args );

		return $query->have_posts() ? $query->posts : [];
	}

	protected function transform_posts_to_settings( $posts ) {
		return array_map( function( $post ) {
			return [
				'id'    => $post->ID,
				'slug'  => $post->post_name,
				'title' => $post->post_title,
			];
		}, $posts );
	}

	public function form( $instance ) {
		$base_id   = ! empty( $instance['viewing_rooms_base'] ) ? $instance['viewing_rooms_base'] : 0;
		$rooms     = ( ! empty( $instance['viewing_rooms'] ) && is_array( $instance['viewing_rooms'] ) ) ? $instance['viewing_rooms'] : [];
		$base_room = [];

		if ( ! empty( $rooms ) ) {
			$room_ids = wp_list_pluck( $rooms, 'id' );
			$rooms = $this->get_viewing_rooms_by_ids( $room_ids );
			$rooms = $this->transform_posts_to_settings( $rooms );
		}

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
			<input class="archiv-fields__field-index" type="hidden" name="<?php echo esc_attr( $this->get_field_name( $prefix . '[_index]' ) ); ?>" value="<?php echo ( $index + 1 ); ?>">
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

add_action( 'widgets_init', function() {
	register_widget( __NAMESPACE__ . '\\Archiv_Menu');
} );
