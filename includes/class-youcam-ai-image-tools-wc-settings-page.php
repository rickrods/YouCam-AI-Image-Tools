<?php
/** Virtual Try On settings tab for WooCommerce. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class YCAIT_WC_Settings_Page extends WC_Settings_Page {
	public function __construct() {
		$this->id = 'ycait_virtual_try_on';
		$this->label = __( 'Virtual Try On', 'youcam-ai-image-tools' );
		parent::__construct();
		add_action( 'woocommerce_admin_field_ycait_model_image', array( $this, 'output_model_image_field' ) );
		add_action( 'woocommerce_admin_field_ycait_separator', array( $this, 'output_separator' ) );
		add_action( 'woocommerce_update_option_ycait_model_image', array( $this, 'save_model_image_field' ) );

		// Woo checkboxes use yes/no; convert the existing 1/0 values without changing their meaning.
		foreach ( array( 'ycait_guest_enabled', 'ycait_user_enabled' ) as $option ) {
			$value = get_option( $option, null );
			if ( is_numeric( $value ) ) { update_option( $option, absint( $value ) ? 'yes' : 'no' ); }
		}
	}

	public function output_separator() {
		echo '<hr class="ycait-settings-separator">';
	}

	/** Persist the custom model image field when WooCommerce saves settings. */
	public function save_model_image_field( $setting ) {
		if ( empty( $setting['id'] ) ) {
			return;
		}
		$option_id = $setting['id'];
		// phpcs:ignore WordPress.Security.NonceVerification -- WooCommerce already verified the nonce.
		$raw_value = isset( $_POST[ $option_id ] ) ? wp_unslash( $_POST[ $option_id ] ) : '';
		if ( is_numeric( $raw_value ) ) {
			$value = absint( $raw_value );
			$value = $value && wp_attachment_is_image( $value ) ? $value : '';
		} elseif ( is_string( $raw_value ) && '' !== $raw_value ) {
			$url   = esc_url_raw( trim( $raw_value ) );
			$parts = wp_parse_url( $url );
			$value = is_array( $parts ) && 'https' === strtolower( $parts['scheme'] ?? '' ) && ! empty( $parts['host'] ) && ! isset( $parts['user'] ) && ! isset( $parts['pass'] ) && wp_http_validate_url( $url ) ? $url : '';
		} else {
			$value = '';
		}
		update_option( $option_id, $value );
	}

	public function output_model_image_field( $setting ) {
		$image_name = $setting['id'];
		$stored      = get_option( $image_name, $setting['default'] ?? '' );
		$id          = is_numeric( $stored ) ? absint( $stored ) : 0;
		?>
		<tr>
			<th scope="row" class="titledesc"><?php echo esc_html( $setting['title'] ); ?></th>
			<td class="forminp">
				<input type="hidden" id="<?php echo esc_attr( $image_name ); ?>" name="<?php echo esc_attr( $image_name ); ?>" value="<?php echo esc_attr( $stored ); ?>">
				<div class="ycait-model-image-controls">
					<div class="ycait-model-image-buttons">
						<button type="button" class="button ycait-select-image" data-target="<?php echo esc_attr( $image_name ); ?>"><?php esc_html_e( 'Choose Image', 'youcam-ai-image-tools' ); ?></button>
						<button type="button" class="button ycait-remove-image" data-target="<?php echo esc_attr( $image_name ); ?>"><?php esc_html_e( 'Remove', 'youcam-ai-image-tools' ); ?></button>
					</div>
					<span id="<?php echo esc_attr( $image_name ); ?>_preview" class="ycait-model-image-preview">
						<?php if ( $id ) { echo wp_get_attachment_image( $id, 'full', false, array( 'style' => 'max-width:420px;max-height:640px;width:auto;height:auto;object-fit:contain;' ) ); } elseif ( $stored ) { ?><img src="<?php echo esc_url( $stored ); ?>" alt="" style="max-width:420px;max-height:640px;width:auto;height:auto;object-fit:contain;"><?php } ?>
					</span>
				</div>
			</td>
		</tr>
		<?php
	}

	protected function get_settings_for_default_section() {
		return array(
			array(
				'title' => __( 'Guest Access:', 'youcam-ai-image-tools' ),
				'type'  => 'title',
				'id'    => 'ycait_virtual_try_on_guest_options',
			),
			array(
				'title'   => __( 'Enable virtual try-on for guests', 'youcam-ai-image-tools' ),
				'desc'    => __( 'Allow visitors who are not signed in to use virtual try-on.', 'youcam-ai-image-tools' ),
				'id'      => 'ycait_guest_enabled',
				'type'    => 'checkbox',
				'default' => 'no',
			),
			array(
				'title'             => __( 'Monthly guest try-on limit', 'youcam-ai-image-tools' ),
				'desc'              => __( 'Maximum try-on tasks per guest each month. Set to 0 for unlimited.', 'youcam-ai-image-tools' ),
				'id'                => 'ycait_guest_limit',
				'type'              => 'number',
				'default'           => 10,
				'css'               => 'width:100px;',
				'custom_attributes' => array( 'min' => 0, 'step' => 1 ),
			),
			array( 'type' => 'sectionend', 'id' => 'ycait_virtual_try_on_guest_options' ),
			array(
				'title' => __( 'Signed-in User Access:', 'youcam-ai-image-tools' ),
				'type'  => 'title',
				'id'    => 'ycait_virtual_try_on_user_options',
			),
			array(
				'title'   => __( 'Enable virtual try-on for users', 'youcam-ai-image-tools' ),
				'desc'    => __( 'Allow signed-in customers to use virtual try-on.', 'youcam-ai-image-tools' ),
				'id'      => 'ycait_user_enabled',
				'type'    => 'checkbox',
				'default' => 'no',
			),
			array(
				'title'             => __( 'Monthly user try-on limit', 'youcam-ai-image-tools' ),
				'desc'              => __( 'Maximum try-on tasks per signed-in user each month. Set to 0 for unlimited.', 'youcam-ai-image-tools' ),
				'id'                => 'ycait_user_limit',
				'type'              => 'number',
				'default'           => 30,
				'css'               => 'width:100px;',
				'custom_attributes' => array( 'min' => 0, 'step' => 1 ),
			),
			array( 'type' => 'sectionend', 'id' => 'ycait_virtual_try_on_user_options' ),
			array( 'type' => 'ycait_separator' ),
			array(
				'title' => __( 'Model Settings:', 'youcam-ai-image-tools' ),
				'type'  => 'title',
				'id'    => 'ycait_virtual_try_on_model_options',
			),
			array(
				'title'   => __( 'Female Model Name:', 'youcam-ai-image-tools' ),
				'id'      => 'ycait_model_name_female',
				'type'    => 'text',
				'default' => 'Female Model',
			),
			array(
				'title'   => __( 'Female Model Image:', 'youcam-ai-image-tools' ),
				'id'      => 'ycait_default_model_female',
				'type'    => 'ycait_model_image',
				'default' => '',
			),
			array(
				'title'   => __( 'Male Model Name:', 'youcam-ai-image-tools' ),
				'id'      => 'ycait_model_name_male',
				'type'    => 'text',
				'default' => 'Male Model',
			),
			array(
				'title'   => __( 'Male Model Image:', 'youcam-ai-image-tools' ),
				'id'      => 'ycait_default_model_male',
				'type'    => 'ycait_model_image',
				'default' => '',
			),
			array( 'type' => 'sectionend', 'id' => 'ycait_virtual_try_on_model_options' ),
		);
	}
}
