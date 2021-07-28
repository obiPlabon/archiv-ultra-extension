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
			__( 'Archiv Menu', 'archiv' )
		);
 
		add_action( 'widgets_init', function() {
			register_widget( __CLASS__ );
		} );
	}

	public function widget( $args, $instance ) {
		
	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		?>
		<ul class="archiv-menu">
			<li class="archiv-menu__item">
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'viewing_rooms[title][1]' ) ); ?>"><?php echo esc_html__( 'Title:', 'archiv' ); ?></label>
					<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'viewing_rooms[title][1]' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'viewing_rooms[title][1]' ) ); ?>" type="text" value="">
				</p>
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'viewing_rooms[slug][1]' ) ); ?>"><?php echo esc_html__( 'Slug:', 'archiv' ); ?></label>
					<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'viewing_rooms[slug][1]' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'viewing_rooms[slug][1]' ) ); ?>" type="text" value="">
				</p>
			</li>
			<li class="archiv-menu__item">
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'viewing_rooms[title][2]' ) ); ?>"><?php echo esc_html__( 'Title:', 'archiv' ); ?></label>
					<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'viewing_rooms[title][2]' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'viewing_rooms[title][2]' ) ); ?>" type="text" value="">
				</p>
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'viewing_rooms[slug][2]' ) ); ?>"><?php echo esc_html__( 'Slug:', 'archiv' ); ?></label>
					<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'viewing_rooms[slug][2]' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'viewing_rooms[slug][2]' ) ); ?>" type="text" value="">
				</p>
			</li>
			<li class="archiv-menu__item">
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'viewing_rooms[title][3]' ) ); ?>"><?php echo esc_html__( 'Title:', 'archiv' ); ?></label>
					<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'viewing_rooms[title][3]' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'viewing_rooms[title][3]' ) ); ?>" type="text" value="">
				</p>
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'viewing_rooms[slug][3]' ) ); ?>"><?php echo esc_html__( 'Slug:', 'archiv' ); ?></label>
					<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'viewing_rooms[slug][3]' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'viewing_rooms[slug][3]' ) ); ?>" type="text" value="">
				</p>
			</li>
		</ul>
		<input type="hidden" value="" class="archiv-menu-item-ids" name="<?php echo esc_attr( $this->get_field_name( 'viewing_rooms_ids' ) ); ?>">
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		// file_put_contents( __DIR__ . '/data.txt', print_r( $new_instance, 1), FILE_APPEND );
		return $instance;
	}
}

return new WP_Menu_Widget();
