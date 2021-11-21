<?php
/**
 * Archiv settings page class.
 * 
 * @package Archiv_Ultra_Extension
 */

namespace Archiv_Ultra_Extension;

defined( 'ABSPATH' ) || die();

class Settings {

	const SETTINGS_KEY = 'archiv_settings';

	public function __construct() {
		add_action( 'admin_init', [ $this, 'settings_init' ] );
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
	}

	public function register_menu() {
		add_options_page(
			__( 'Archiv Rooms Settings', 'archiv' ),
			__( 'Archiv Rooms', 'archiv' ),
			'manage_options',
			'archiv-rooms',
			[ $this, 'render_page' ]
		);
	}

	public function render_page() {
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				// output security fields for the registered setting "archiv_options"
				settings_fields( 'archiv_options' );
				// output setting sections and their fields
				// (sections are registered for "archiv_options", each field is registered to a specific section)
				do_settings_sections( 'archiv_options' );
				// output save settings button
				submit_button( __( 'Save Settings', 'archiv' ) );
				?>
			</form>
		</div>
		<?php
	}

	public function settings_init() {
		// Register a new setting for "archiv_options" page.
		register_setting(
			'archiv_options',
			self::SETTINGS_KEY,
			[
				'type'              => 'object',
				'sanitize_callback' => [ $this, 'sanitize_callback' ],
			]
		);
	 
		// Register a new section in the "archiv_options" page.
		add_settings_section(
			'archiv_rooms_section',
			__( 'Archiv Rooms', 'archiv' ),
			'__return_empty_string',
			'archiv_options'
		);
	 
		// Register a new field in the "archiv_rooms_section" section, inside the "wporg" page.

		foreach ( $this->get_fields() as $field_key => $args ) {
			add_settings_field(
				$args['label_for'],
				$args['label'],
				$this->get_render_callback( $args['type'] ),
				'archiv_options',
				'archiv_rooms_section',
				[
					'field_key'   => $field_key,
					'label_for'   => $args['label_for'],
					'description' => $args['description'],
					'default'     => $args['default'],
				]
			);
		}
	}
	
	function render_text_field( $args ) {
		// Get the value of the setting we've registered with register_setting()
		$options = get_option( self::SETTINGS_KEY );
		$value   = ( ! empty( $options[ $args['field_key'] ] ) ? $options[ $args['field_key'] ] : $args['default'] );
		?>
		<input
			id="<?php echo esc_attr( $args['label_for'] ); ?>"
			type="text"
			required="required"
			class="regular-text"
			name="<?php echo esc_attr( self::SETTINGS_KEY . '[' . $args['field_key'] . ']' ); ?>"
			value="<?php echo esc_attr( $value ); ?>"
			>
		<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
		<?php
	}

	public function sanitize_callback( $values ) {
		foreach ( $this->get_fields() as $field_key => $args ) {
			if ( empty( $values[ $field_key ] ) ) {
				continue;
			}

			if ( $args['type'] === 'text' ) {
				$value = trim( $values[ $field_key ] );
				$value = ! empty( $value ) ? $value : $args['default'];
				$values[ $field_key ] = sanitize_text_field( $value ); 
			}
		}

		return $values;
	}

	public function get_render_callback( $type ) {
		$callback = [ $this, 'render_text_field' ];

		return $callback;
	}

	protected function get_fields() {
		$fields = [
			'viewing_room_1' => [
				'label_for'   => 'archiv_vr_1',
				'label'       => __( 'Academic - Room Name', 'archiv' ),
				'description' => __( 'Set a name for "Academic" viewing room.', 'archiv' ),
				'type'        => 'text',
				'default'     => __( 'ACADEMIC', 'archiv' ),
			],
			'viewing_room_2' => [
				'label_for'   => 'archiv_vr_2',
				'label'       => __( 'Immersion - Room Name', 'archiv' ),
				'description' => __( 'Set a name for "Immersion" viewing room.', 'archiv' ),
				'type'        => 'text',
				'default'     => __( 'IMMERSION', 'archiv' ),
			],
			'viewing_room_3' => [
				'label_for'   => 'archiv_vr_3',
				'label'       => __( 'List Of Works - Room Name', 'archiv' ),
				'description' => __( 'Set a name for "List Of Works" viewing room.', 'archiv' ),
				'type'        => 'text',
				'default'     => __( 'LIST OF WORKS', 'archiv' ),
			],
		];

		return $fields;
	}
}

return new Settings();
