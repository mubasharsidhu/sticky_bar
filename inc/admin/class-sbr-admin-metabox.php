<?php
/**
 * Sticky Bar Post Metabox
 *
 * @package    Sticky_Bar
 * @subpackage Sticky_Bar/inc/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * SBR_Admin_Metabox class to manage Post Metabox-data
 */
class SBR_Admin_Metabox {


	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );
	}


	/**
	 * Add the meta box and link it with a screen.
	 *
	 * @return void
	 */
	public function add_metabox() {
		add_meta_box(
			'sbr-meta-box', // Metabox ID.
			__( 'Sticky Bar', 'stickybar' ), // Metabox Title.
			array( $this, 'render_metabox' ), // Metabox callback.
			'post', // Screen ID where Metabox has to display.
			'side'
		);
	}


	/**
	 * Renders the Metabox.
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return void Renders Metabox fields HTML.
	 */
	public function render_metabox( $post ) {
		// Add nonce for security and authentication.
		wp_nonce_field( 'sbr_nonce_action', 'sbr_nonce' );

		$sbr_sticky_bar = (array) get_option( 'sbr_sticky_bar' );

		$sbr_is_active    = 'no';
		$sbr_custom_title = '';
		$sbr_is_expirable = 'no';
		$sbr_expiry       = '';
		if ( isset( $sbr_sticky_bar['post_id'] ) && $post->ID === $sbr_sticky_bar['post_id'] ) {
			$sbr_is_active    = ( isset( $sbr_sticky_bar['sbr_is_active'] ) ) ? $sbr_sticky_bar['sbr_is_active'] : 'no';
			$sbr_custom_title = ( isset( $sbr_sticky_bar['sbr_custom_title'] ) ) ? $sbr_sticky_bar['sbr_custom_title'] : '';
			$sbr_is_expirable = ( isset( $sbr_sticky_bar['sbr_is_expirable'] ) ) ? $sbr_sticky_bar['sbr_is_expirable'] : 'no';
			$sbr_expiry       = ( isset( $sbr_sticky_bar['sbr_expiry'] ) && '' !== $sbr_sticky_bar['sbr_expiry'] ) ? date( 'Y-m-d\TH:i', strtotime( $sbr_sticky_bar['sbr_expiry'] ) ) : '';
		}

		$sbr_is_active_check    = ( 'yes' === $sbr_is_active ) ? 'checked="checked"' : '';
		$sbr_is_expirable_check = ( 'yes' === $sbr_is_expirable ) ? 'checked="checked"' : '';
		$sbr_min_date           = wp_date( 'Y-m-d\TH:i' );
		?>

		<table>
			<tr>
				<td>
					<label for="sbr_is_active"><?php esc_html_e( 'Make this post sticky bar: ', 'stickybar' ); ?></label>
					<input type="checkbox" name="sbr_is_active" id="sbr_is_active" value="yes" <?php echo esc_attr( $sbr_is_active_check ); ?> />
				</td>
			</tr>
			<tr>
				<td>
					<br />
					<label for="sbr_custom_title"><?php esc_html_e( 'Custom Title: ', 'stickybar' ); ?></label>
					<input type="text" name="sbr_custom_title" id="sbr_custom_title" value="<?php echo esc_attr( $sbr_custom_title ); ?>" />
				</td>
			</tr>
			<tr>
				<td>
					<br />
					<label for="sbr_is_expirable"><?php esc_html_e( 'Add Expiry: ', 'stickybar' ); ?></label>
					<input type="checkbox" name="sbr_is_expirable" id="sbr_is_expirable" value="yes" <?php echo esc_attr( $sbr_is_expirable_check ); ?> />
				</td>
			</tr>
			<tr id="sbr-expiry-wrap">
				<td>
					<br />
					<label for="sbr_expiry"><?php esc_html_e( 'Expiry Date: ', 'stickybar' ); ?></label>
					<input type="datetime-local" name="sbr_expiry" id="sbr_expiry" min="<?php echo esc_attr( $sbr_min_date ); ?>" value="<?php echo esc_attr( $sbr_expiry ); ?>" />
				</td>
			</tr>
		</table>

		<?php
	}


	/**
	 * Handles saving the Metabox fields.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @return null
	 */
	public function save_metabox( $post_id, $post ) {
		// Add nonce for security and authentication.
		$nonce_name   = isset( $_POST['sbr_nonce'] ) ? sanitize_key( $_POST['sbr_nonce'] ) : '';
		$nonce_action = 'sbr_nonce_action';

		// Check if nonce is valid.
		if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
			return;
		}

		// Check if user has permissions to save data.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if not an autosave.
		if ( wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Check if not a revision.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$valid_fields    = $this->validate( $_POST );
		$is_save_allowed = $this->is_valid_post_data( $post_id, $valid_fields );

		if ( 'yes' === $is_save_allowed ) {
			$sbr_sticky_bar = array(
				'post_id'          => $post_id,
				'sbr_is_active'    => $valid_fields['sbr_is_active'],
				'sbr_custom_title' => $valid_fields['sbr_custom_title'],
				'sbr_is_expirable' => $valid_fields['sbr_is_expirable'],
				'sbr_expiry'       => $valid_fields['sbr_expiry'],
			);
			update_option( 'sbr_sticky_bar', $sbr_sticky_bar );
		}

	}


	/**
	 * Sanitize/Validate each Metabox field as needed.
	 *
	 * @param array $fields Contains Metabox fields.
	 *
	 * @return array Valid Fields.
	 */
	public function validate( $fields ) {
		$valid_fields = array(
			'sbr_is_active'    => 'no',
			'sbr_custom_title' => '',
			'sbr_is_expirable' => 'no',
			'sbr_expiry'       => '',
		);
		if ( isset( $fields['sbr_is_active'] ) ) {
			$valid_fields['sbr_is_active'] = sanitize_text_field( $fields['sbr_is_active'] );
		}
		if ( isset( $fields['sbr_custom_title'] ) ) {
			$valid_fields['sbr_custom_title'] = sanitize_text_field( $fields['sbr_custom_title'] );
		}
		if ( isset( $fields['sbr_is_expirable'] ) ) {
			$valid_fields['sbr_is_expirable'] = sanitize_text_field( $fields['sbr_is_expirable'] );
		}
		if ( isset( $fields['sbr_expiry'] ) ) {
			$sbr_expiry = sanitize_text_field( $fields['sbr_expiry'] );

			if ( 'yes' === $this->is_valid_datetime( $sbr_expiry ) ) {
				$valid_fields['sbr_expiry'] = $sbr_expiry;
			}
		}

		return apply_filters( 'validate_sbr_metabox_fields', $valid_fields, $fields );
	}


	/**
	 * Check post validity, so that previous data should not overwrite.
	 *
	 * @param int   $post_id      Post ID.
	 * @param array $valid_fields Valid Fields.
	 *
	 * @return string yes/no.
	 */
	public function is_valid_post_data( $post_id, $valid_fields ) {
		$prev_sbr_sticky_bar = get_option( 'sbr_sticky_bar' );
		$prev_post_id           = isset( $prev_sbr_sticky_bar['post_id'] ) ? $prev_sbr_sticky_bar['post_id'] : '';

		if ( '' === $prev_post_id || $post_id === $prev_post_id || ( $post_id !== $prev_post_id && 'yes' === $valid_fields['sbr_is_active'] ) ) {
			return 'yes';
		}
		return 'no';
	}


	/**
	 * Verify if date is valid
	 *
	 * @param string $datetime The date string that needs to be checked.
	 * @param string $format date formate.
	 *
	 * @return yes/no.
	 */
	public function is_valid_datetime( $datetime, $format = 'Y-m-d H:i' ) {
		$datetime = str_replace( 'T', ' ', $datetime );
		$d        = DateTime::createFromFormat( $format, $datetime );
		return ( $d && $d->format( $format ) === $datetime ) ? 'yes' : 'no';
	}


}

if ( is_admin() ) {
	new SBR_Admin_Metabox();
}
