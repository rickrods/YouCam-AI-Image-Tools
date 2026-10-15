<?php
/**
 * Frontend display logic.
 *
 * Adds the "Virtual Try-On" button to WooCommerce product pages
 * and enqueues the required CSS/JS assets.
 *
 * @package Youcam_Ai_Image_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the try-on button and enqueues frontend assets.
 */
class YCAIT_Frontend {

	/**
	 * Settings instance.
	 *
	 * @var YCAIT_Settings
	 */
	protected $settings;

	/**
	 * Constructor.
	 *
	 * @param YCAIT_Settings $settings Settings instance.
	 */
	public function __construct( YCAIT_Settings $settings ) {
		$this->settings = $settings;

		add_action(
			'woocommerce_after_add_to_cart_button',
			array( $this, 'render_tryon_button' )
		);

		add_action(
			'wp_enqueue_scripts',
			array( $this, 'enqueue_assets' )
		);
	}

	/**
	 * Render the "Virtual Try-On" button on the product page.
	 */
	public function render_tryon_button() {
		global $product;

		if ( ! class_exists( 'WC_Product' ) || ! $product instanceof WC_Product ) {
			return;
		}

		/*
		 * Only show the button when the product has an image
		 * that can be used as the clothing reference.
		 */
		$product_image = wp_get_attachment_image_src(
			$product->get_image_id(),
			'full'
		);

		if ( empty( $product_image ) ) {
			return;
		}

		$api_configured = ! empty(
			$this->settings->get( 'api_key' )
		);

		?>
		<div class="ycait-button-wrapper">
			<button
				type="button"
				class="button ycait-tryon-btn"
				data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
				data-product-type="<?php echo esc_attr( $this->get_product_api_type( $product->get_id() ) ); ?>"
				<?php echo $api_configured ? '' : 'disabled'; ?>
			>
				<span class="ycait-tryon-icon" aria-hidden="true">
					<svg
						xmlns="http://www.w3.org/2000/svg"
						width="20"
						height="20"
						viewBox="0 0 24 24"
						fill="none"
						stroke="currentColor"
						stroke-width="2"
						stroke-linecap="round"
						stroke-linejoin="round"
					>
						<path d="M3 3h18v18H3z" opacity="0"/>
						<path d="M20.38 3.46L16 2a4 4 0 01-8 0L3.62 3.46a2 2 0 00-1.34 2.23l.58 3.47a1 1 0 00.99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 002-2V10h2.15a1 1 0 00.99-.84l.58-3.47a2 2 0 00-1.34-2.23z"/>
					</svg>
				</span>

				<?php esc_html_e( 'Virtual Try-On', 'youcam-ai-image-tools' ); ?>
			</button>

			<?php if ( ! $api_configured ) : ?>
				<p class="ycait-unavailable-msg">
					<?php
					esc_html_e(
						'Virtual try-on is not available at this time.',
						'youcam-ai-image-tools'
					);
					?>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	/** Guess the product API from its WooCommerce product category. */
	protected function get_product_api_type( $product_id ) {
		$terms = get_the_terms( $product_id, 'product_cat' );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return 'clothes';
		}
		$labels = '';
		foreach ( $terms as $term ) {
			$labels .= ' ' . strtolower( $term->slug . ' ' . $term->name );
		}
		if ( preg_match( '/(^|[^a-z])(hat|hats|cap|caps|headwear)([^a-z]|$)/', $labels ) ) return 'hat';
		if ( preg_match( '/(^|[^a-z])(scarf|scarves|scarfs)([^a-z]|$)/', $labels ) ) return 'scarf';
		if ( preg_match( '/(^|[^a-z])(bag|bags|purse|purses|handbag|handbags)([^a-z]|$)/', $labels ) ) return 'bag';
		return 'clothes';
	}

	/**
	 * Enqueue CSS and JS for the frontend.
	 */
	public function enqueue_assets() {
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}

		/*
		 * Default model images are passed to JavaScript as URLs.
		 * frontend.js is responsible for uploading a selected default
		 * model through the File API before creating the try-on task.
		 */
		$default_female = $this->settings->get_model_url( 'default_model_female' );
		$default_male   = $this->settings->get_model_url( 'default_model_male' );

		wp_enqueue_style(
			'ycait-frontend',
			YOUCAM_AI_IMAGE_TOOLS_URL . 'assets/css/frontend.css',
			array(),
			YOUCAM_AI_IMAGE_TOOLS_VERSION
		);

		wp_enqueue_script(
			'ycait-frontend',
			YOUCAM_AI_IMAGE_TOOLS_URL . 'assets/js/frontend.js',
			array(),
			YOUCAM_AI_IMAGE_TOOLS_VERSION,
			true
		);

		wp_localize_script(
			'ycait-frontend',
			'ycaitData',
			array(
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'nonce'         => wp_create_nonce( 'ycait_nonce' ),
				'defaultFemale' => $default_female,
				'defaultMale'   => $default_male,
				'modelNameFemale' => $this->settings->get( 'model_name_female', __( 'Female Model', 'youcam-ai-image-tools' ) ),
				'modelNameMale' => $this->settings->get( 'model_name_male', __( 'Male Model', 'youcam-ai-image-tools' ) ),
				'photoStorageKey' => 'ycaitUploadedPhoto_' . ( is_user_logged_in() ? 'user_' . get_current_user_id() : 'guest' ),
				'i18n'          => array(
					'uploading'   => __( 'Uploading your photo...', 'youcam-ai-image-tools' ),
					'processing'  => __( 'AI is generating your try-on...', 'youcam-ai-image-tools' ),
					'success'     => __( 'Here is your virtual try-on result!', 'youcam-ai-image-tools' ),
					'error'       => __( 'Something went wrong. Please try again.', 'youcam-ai-image-tools' ),
					'noPhoto'     => __( 'Please upload a photo or select a default model.', 'youcam-ai-image-tools' ),
					'invalidFile' => __( 'Please upload a JPG, PNG, or WebP image.', 'youcam-ai-image-tools' ),
					'close'       => __( 'Close', 'youcam-ai-image-tools' ),
					'tryAgain'    => __( 'Try Again', 'youcam-ai-image-tools' ),
					'useModel'    => __( 'Use this model', 'youcam-ai-image-tools' ),
					'uploadOwn'   => __( 'Upload your own photo', 'youcam-ai-image-tools' ),
					'femaleModel' => __( 'Female Model', 'youcam-ai-image-tools' ),
					'maleModel'   => __( 'Male Model', 'youcam-ai-image-tools' ),
					'title'       => __( 'Virtual Try-On', 'youcam-ai-image-tools' ),
					'dragDrop'    => __( 'Drag & drop your photo here, or click to browse', 'youcam-ai-image-tools' ),
					'orChoose'    => __( 'or choose a default model below', 'youcam-ai-image-tools' ),
				),
			)
		);
	}
}
