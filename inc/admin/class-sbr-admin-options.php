<?php
/**
 * Sticky Bar Options
 *
 * @package    Sticky_Bar
 * @subpackage Sticky_Bar/inc/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * SBR_Admin_Options class to manage plugin options on admin side.
 */
class SBR_Admin_Options {

	/**
	 * Holds the values to be used in the fields callbacks
	 *
	 * @var array
	 */
	private $options;


	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_sbr_options_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );
		add_filter( 'plugin_action_links_' . SBR_BASENAME, array( $this, 'sbr_plugin_action_links' ) );
	}


	/**
	 * Add/Create the `Sticky Bar` options admin-page
	 *
	 * @return void
	 */
	public function add_sbr_options_page() {
		// This page will be under "Settings".
		add_options_page(
			__( 'Sticky Bar Options', 'stickybar' ),
			__( 'Sticky Bar', 'stickybar' ),
			'manage_options',
			'sbr-settings',
			array( $this, 'sbr_options_page_content' )
		);
	}


	/**
	 * Callback function to add content to `Sticky Bar` options admin-page.
	 *
	 * @return void
	 */
	public function sbr_options_page_content() {
		// Set class property.
		$this->options = get_option( 'sbr_options' );

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Sticky Bar Options', 'stickybar' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields.
				settings_fields( 'sbr_fields_group' );
				do_settings_sections( 'sbr-settings' );
				submit_button();
				?>
			</form>
		</div>

		<?php
		sbr_the_sticky_bar(); // Display Active Sticky Bar.
	}


	/**
	 * Register setting, Add section and the Option Fields on `admin_init` hook
	 *
	 * @return void
	 */
	public function page_init() {
		register_setting(
			'sbr_fields_group', // Option group.
			'sbr_options', // Option name.
			array( $this, 'validate' ) // Callback to validate.
		);

		add_settings_section(
			'sbr_setings_section', // Section ID.
			'', // Section Title.
			'', // Callback.
			'sbr-settings' // `Breakin News` Page Slug.
		);

		add_settings_field(
			'title', // Option Field ID.
			__( 'Title', 'stickybar' ), // Option Field Title.
			array( $this, 'cb_title' ), // Callback.
			'sbr-settings', // `Breakin News` Page Slug.
			'sbr_setings_section' // `Breakin News` Section ID.
		);
		add_settings_field(
			'background',
			__( 'Background', 'stickybar' ),
			array( $this, 'cb_background' ),
			'sbr-settings',
			'sbr_setings_section'
		);
		add_settings_field(
			'color',
			__( 'Color', 'stickybar' ),
			array( $this, 'cb_color' ),
			'sbr-settings',
			'sbr_setings_section'
		);
	}


	/**
	 * Sanitize/Validate each setting field as needed.
	 *
	 * @param array $fields Contains all `Sticky Bar` fields as array.
	 *
	 * @return array Valid fields.
	 */
	public function validate( $fields ) {
		$valid_fields = array();

		if ( isset( $fields['title'] ) ) {
			$valid_fields['title'] = sanitize_text_field( $fields['title'] );
		}

		if ( isset( $fields['background'] ) && '' !== trim( $fields['background'] ) ) {
			$valid_fields['background'] = $this->validate_color( $fields['background'], 'Background' );
		}

		if ( isset( $fields['color'] ) && '' !== trim( $fields['color'] ) ) {
			$valid_fields['color'] = $this->validate_color( $fields['color'], 'Color' );
		}

		return apply_filters( 'validate_options', $valid_fields, $fields );

	}


	/**
	 * Function to sanitize and validate colors fields.
	 *
	 * @param string $field Color Value (Hex Code Expected).
	 * @param string $key   Color Field Key/String for error message.
	 *
	 * @return string
	 */
	public function validate_color( $field, $key ) {
		// Validate Background Color.
		$field = sanitize_text_field( $field );

		// Check if is a valid hex color.
		if ( false === $this->check_color( $field ) ) {
			// Set the error message.
			add_settings_error( 'sbr_options', $key . 'error', 'Insert a valid color for ' . $key, 'error' );

			// Get the previous valid value.
			return $this->options[ $key ];
		} else {
			return $field;
		}
	}


	/**
	 * Function that will check if value is a valid HEX color.
	 *
	 * @param mixed $value Hex-color string to validate.
	 *
	 * @return bool
	 */
	public function check_color( $value ) {
		if ( preg_match( '/^#[a-f0-9]{6}$/i', $value ) ) { // if user insert a HEX color with a hash (#).
			return true;
		}
		return false;
	}


	/**
	 * Get the settings options array and print one of its values
	 *
	 * @return void
	 */
	public function cb_title() {
		printf(
			'<input type="text" id="title" name="sbr_options[title]" value="%s" />',
			isset( $this->options['title'] ) ? esc_attr( $this->options['title'] ) : ''
		);
	}


	/**
	 * Get the settings options array and print one of its values
	 *
	 * @return void
	 */
	public function cb_background() {
		printf(
			'<input type="text" id="background" name="sbr_options[background]" value="%s" class="color-picker" />',
			isset( $this->options['background'] ) ? esc_attr( $this->options['background'] ) : ''
		);
	}


	/**
	 * Get the settings options array and print one of its values
	 *
	 * @return void
	 */
	public function cb_color() {
		printf(
			'<input type="text" id="color" name="sbr_options[color]" value="%s" class="color-picker" />',
			isset( $this->options['color'] ) ? esc_attr( $this->options['color'] ) : ''
		);
	}


	/**
	 * Merging the Settings link in the plugin links, on installed plugins page.
	 *
	 * @param array $links Array of links.
	 *
	 * @return array Array of links.
	 */
	public function sbr_plugin_action_links( $links ) {
		$action_links[] = '<a href="' . admin_url( 'options-general.php?page=sbr-settings' ) . '">' . esc_html__( 'Settings', 'stickybar' ) . '</a>';
		return array_merge( $action_links, $links );
	}


}

if ( is_admin() ) {
	new SBR_Admin_Options();
}
