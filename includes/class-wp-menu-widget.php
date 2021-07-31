<?php
/**
 * WP menu widget class.
 * 
 * @package Archiv_Ultra_Extension
 */

namespace Archiv_Ultra_Extension;

defined( 'ABSPATH' ) || die();

use WP_Widget;

class WP_Menu_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'archiv-menu',
			__( 'Archiv Viewing Rooms', 'archiv' )
		);

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
	register_widget( __NAMESPACE__ . '\\WP_Menu_Widget');
} );
