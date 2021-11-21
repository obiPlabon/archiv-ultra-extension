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

	public function widget( $args, $instance ) {
		if ( is_admin() ||
			! empty( $_GET['elementor-preview'] ) ||
			( isset( $_GET['action'] ) && $_GET['action'] === 'elementor' )
			) {
			$this->render_backend( $instance );
		} else {
			$this->render_frontend();
		}
	}

	protected function render_backend( $instance ) {
		$is_widget_rendering = false;

		// Dig deep and find the post id
		if ( isset( $_REQUEST['post'], $_REQUEST['action'] ) && $_REQUEST['action'] === 'elementor' && ! empty( $_REQUEST['post'] ) ) {
			// Elementor main editor
			$post_id = absint( $_REQUEST['post'] );
		} elseif ( isset( $_REQUEST['elementor-preview'] ) && ! empty( $_REQUEST['elementor-preview'] ) ) {
			// Elementor header / footer editor
			$post_id = absint( $_REQUEST['elementor-preview'] );
		} elseif ( isset( $_REQUEST['initial_document_id'], $_REQUEST['action'] ) && $_REQUEST['action'] === 'elementor_ajax' && ! empty( $_REQUEST['initial_document_id'] ) ) {
			// Elementor widget render ajax request
			$post_id             = absint( $_REQUEST['initial_document_id'] );
			$current_action_data = Plugin::$instance->common->get_component( 'ajax' )->get_current_action_data();
			$is_widget_rendering = ( $current_action_data['action'] === 'render_widget' );
		} else {
			$post_id = get_queried_object_id();
		}

		// Return for unsupported post types
		if ( get_post_type( $post_id ) !== Post_Types::VIEWING_ROOM ) {
			return;
		}

		// Could be an optimzied solution, need more testing
		if ( $is_widget_rendering && ! empty( $instance['viewing_rooms'] ) && is_array( $instance['viewing_rooms'] ) ) {
			$post_ids = wp_list_pluck( $instance['viewing_rooms'], 'id' );
			$rooms    = $this->get_collection_by_item_ids( $post_ids );
		} else {
			$rooms = $this->get_collection_by_item_id( $post_id );
		}
		?>
		<ul class="archiv-menu">
			<?php foreach ( $rooms as $room ) : ?>
				<li class="archiv-menu__item">
					<?php
					$title = ( $room->post_parent ? get_the_title( $room ) : Auto_Post::get_base_post_title( $room ) );

					printf(
						'<a title="%s" class="archiv-menu__item-link %s" href="%s">%s</a>',
						esc_attr__( 'Click to open on editor', 'archiv' ),
						( $room->ID === $post_id ? 'archiv--is-active' : '' ),
						$this->get_edit_url( $room->ID ),
						esc_html( $title )
					);
					?>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	protected function render_frontend() {
		// Show only on viewing rooms
		if ( ! is_singular( Post_Types::VIEWING_ROOM ) ) {
			return;
		}

		$status = ( current_user_can( 'edit_posts' ) ? 'any' : 'publish' );
		$rooms  = $this->get_collection_by_item_id( get_queried_object_id(), $status );
		?>
		<ul class="archiv-menu">
			<?php foreach ( $rooms as $room ) :
				$title = ( $room->post_parent ? get_the_title( $room ) : Auto_Post::get_base_post_title( $room ) );
				?>
				<li class="archiv-menu__item">
					<a class="archiv-menu__item-link <?php echo is_single( $room->ID ) ? 'archiv--is-active' : ''; ?>" href="<?php the_permalink( $room ); ?>"><?php echo $title; ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	public function form( $instance ) {
		$post_id = isset( $_REQUEST['initial_document_id'] ) ? absint( $_REQUEST['initial_document_id'] ) : 0;

		if ( empty( $post_id ) || get_post_type( $post_id ) !== Post_Types::VIEWING_ROOM ) {
			printf(
				'<p class="archiv-info archiv-info--warning"><i class="eicon-info-circle-o"></i> %s</p>',
				esc_html__( 'This post does not support Viewing Room menu.', 'archiv' )
			);
			return;
		}

		?>
		<p class="archiv-info"><i class="eicon-info-circle-o"></i> <?php esc_html_e( 'You can easily drag to sort the items.', 'archiv' ); ?></p>
		<ul class="archiv-fields">
			<?php
			$posts = $this->get_collection_by_item_id( $post_id );

			foreach ( $posts as $_key => $post ) {
				$data = [
					'id'    => $post->ID,
					'slug'  => $post->post_name,
					'title' => $post->post_title,
				];

				$this->render_menu_item( $_key, $data );
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

	protected function get_collection_by_item_id( $post_id, $status = 'any' ) {
		$post_ids = [];
		$post     = get_post( $post_id );

		if ( empty( $post ) || $post->post_type !== Post_Types::VIEWING_ROOM ) {
			return $post_ids;
		}

		$parent_post_id = $post->ID;
		if ( ! empty( $post->post_parent ) ) {
			$parent_post_id = $post->post_parent;
		}
		
		$post_ids = [ $parent_post_id ];
		$_ids     = Auto_Post::get_sub_posts( $parent_post_id );

		if ( ! empty( $_ids ) ) {
			$post_ids = array_merge( $post_ids, $_ids );
		}

		$args = [
			'post_type'              => Post_Types::VIEWING_ROOM,
			'post_status'            => $status,
			'post__in'               => $post_ids,
			'posts_per_page'         => count( $post_ids ),
			'meta_key'               => '_archiv_menu_index',
			'orderby'                => 'meta_value_num',
			'order'                  => 'ASC',
			'update_post_term_cache' => false,
			'no_found_rows'          => true,
		];

		$query = new \WP_Query( $args );

		return $query->have_posts() ? $query->posts : [];
	}

	protected function get_collection_by_item_ids( $post_ids = [] ) {
		$args = [
			'post_type'              => Post_Types::VIEWING_ROOM,
			'post_status'            => 'any',
			'post__in'               => $post_ids,
			'posts_per_page'         => count( $post_ids ),
			'orderby'                => 'post__in',
			'order'                  => 'ASC',
			'update_post_term_cache' => false,
			'no_found_rows'          => true,
		];

		$query = new \WP_Query( $args );

		return $query->have_posts() ? $query->posts : [];
	}

	protected function get_edit_url( $id ) {
		$url = add_query_arg( [
			'post'   => $id,
			'action' => 'elementor',
		], admin_url( 'post.php' ) );

		return esc_url( $url );
	}
}

add_action( 'widgets_init', function() {
	register_widget( __NAMESPACE__ . '\\Archiv_Menu');
} );
