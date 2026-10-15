<?php
/** Admin image generation and enhancement tools. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class YCAIT_Tools {
	private $api;
	private $settings;

	public function __construct( YCAIT_API_Client $api, YCAIT_Settings $settings ) {
		$this->api = $api;
		$this->settings = $settings;
		add_action( 'admin_menu', array( $this, 'add_pages' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_ajax_ycait_tool_run', array( $this, 'run_task' ) );
		add_action( 'wp_ajax_ycait_tool_poll', array( $this, 'poll_task' ) );
		add_action( 'wp_ajax_ycait_tool_save_result', array( $this, 'save_result' ) );
		add_action( 'wp_ajax_ycait_media_generator', array( $this, 'render_media_generator' ) );
		add_action( 'wp_ajax_ycait_media_model_product', array( $this, 'render_media_model_product' ) );
		add_action( 'wp_ajax_ycait_attachment_enhancement', array( $this, 'render_attachment_enhancement' ) );
		add_action( 'wp_ajax_ycait_tool_save_enhancement', array( $this, 'save_enhancement' ) );
	}

	public function add_pages() {
		add_submenu_page( 'youcam-ai-image-tools', __( 'Generate Images', 'youcam-ai-image-tools' ), __( 'Generate Images', 'youcam-ai-image-tools' ), 'edit_posts', 'ycait-image-generation', array( $this, 'render_generation' ) );
		add_submenu_page( 'youcam-ai-image-tools', __( 'Enhance Images', 'youcam-ai-image-tools' ), __( 'Enhance Images', 'youcam-ai-image-tools' ), 'edit_posts', 'ycait-image-enhancement', array( $this, 'render_enhancement' ) );
		add_submenu_page( 'youcam-ai-image-tools', __( 'Enhance Face & Body', 'youcam-ai-image-tools' ), __( 'Enhance Face & Body', 'youcam-ai-image-tools' ), 'edit_posts', 'ycait-face-body-enhancement', array( $this, 'render_face_body' ) );
		add_submenu_page( 'youcam-ai-image-tools', __( 'Enhance Beauty', 'youcam-ai-image-tools' ), __( 'Enhance Beauty', 'youcam-ai-image-tools' ), 'edit_posts', 'ycait-beauty-enhancements', array( $this, 'render_beauty' ) );
	}

	/** User-facing field guidance based on the input descriptions in the bundled YouCam API specifications. */
	private function get_field_descriptions() {
		return array(
			'text-to-image' => array(
				'prompt' => __( 'Describe the image content, style, and composition you want. Maximum 800 characters.', 'youcam-ai-image-tools' ),
				'negative_prompt' => __( 'Describe what you do not want to appear in the generated image.', 'youcam-ai-image-tools' ),
				'size' => __( 'Choose the output dimensions and aspect ratio for your image.', 'youcam-ai-image-tools' ),
			),
			'headshot' => array(
				'photo' => __( 'Upload a clear portrait to create professional headshots.', 'youcam-ai-image-tools' ),
				'template_id' => __( 'Choose a predefined headshot style. Available styles are loaded from the API.', 'youcam-ai-image-tools' ),
			),
			'avatar' => array(
				'photo' => __( 'Upload a clear portrait to create avatar variations.', 'youcam-ai-image-tools' ),
				'template_id' => __( 'Choose a predefined avatar style. Available styles are loaded from the API.', 'youcam-ai-image-tools' ),
				'output_count' => __( 'Choose how many avatar images to generate.', 'youcam-ai-image-tools' ),
			),
			'studio-generator' => array(
				'photo' => __( 'Upload a portrait photo. The API requires the shorter image side to be at least 200 pixels.', 'youcam-ai-image-tools' ),
				'template_id' => __( 'Choose a predefined studio style. Available styles are loaded from the API.', 'youcam-ai-image-tools' ),
				'output_count' => __( 'Choose how many studio portraits to generate.', 'youcam-ai-image-tools' ),
			),
			'bg-replace' => array(
				'photo' => __( 'Upload the photo whose background you want to replace.', 'youcam-ai-image-tools' ),
				'prompt' => __( 'Describe the new background to generate. Maximum 500 characters.', 'youcam-ai-image-tools' ),
			),
			'enhance' => array(
				'photo' => __( 'Upload the image to sharpen and improve.', 'youcam-ai-image-tools' ),
				'scale' => __( 'Select the image enlargement ratio: 1×, 2×, or 4×.', 'youcam-ai-image-tools' ),
			),
			'lighting' => array( 'photo' => __( 'Upload the photo whose lighting you want to improve.', 'youcam-ai-image-tools' ) ),
			'color-correction' => array( 'photo' => __( 'Upload the photo whose color and balance you want to correct.', 'youcam-ai-image-tools' ) ),
			'bg-blur' => array(
				'photo' => __( 'Upload a photo with a subject in the foreground and a background to blur.', 'youcam-ai-image-tools' ),
				'intensity' => __( 'Set background blur from 0 (no blur) to 100 (maximum blur).', 'youcam-ai-image-tools' ),
			),
			'face-swap' => array(
				'photo' => __( 'Upload the target photo. The first detected face is replaced.', 'youcam-ai-image-tools' ),
				'reference' => __( 'Upload the reference photo containing the face to use.', 'youcam-ai-image-tools' ),
			),
			'image-extender' => array(
				'photo' => __( 'Upload a JPG image. The source is centered on the larger output canvas.', 'youcam-ai-image-tools' ),
				'output_width' => __( 'Set the output canvas width in pixels, up to 4096. It must be at least as wide as the source.', 'youcam-ai-image-tools' ),
				'output_height' => __( 'Set the output canvas height in pixels, up to 4096. It must be at least as tall as the source.', 'youcam-ai-image-tools' ),
			),
			'photo-colorize' => array( 'photo' => __( 'Upload a black-and-white photo to generate a colorized version.', 'youcam-ai-image-tools' ) ),
			'watermark-removal' => array( 'photo' => __( 'Upload an image you own or have permission to edit.', 'youcam-ai-image-tools' ) ),
			'abs-filter' => array(
				'photo' => __( 'Upload a clear photo showing the person’s torso.', 'youcam-ai-image-tools' ),
				'mode' => __( 'Choose the abs effect style to apply.', 'youcam-ai-image-tools' ),
				'intensity' => __( 'Choose the effect strength. Level 2 applies the stronger effect.', 'youcam-ai-image-tools' ),
			),
			'aging' => array( 'photo' => __( 'Upload a clear portrait photo to preview an aged appearance.', 'youcam-ai-image-tools' ) ),
			'body-reshape' => array(
				'photo' => __( 'Upload a photo with a clearly visible person. The adjustment applies to the first detected person.', 'youcam-ai-image-tools' ),
				'feature' => __( 'Choose the body area or overall proportion to adjust.', 'youcam-ai-image-tools' ),
				'amount' => __( 'Set the adjustment from -100 to 100. Negative and positive values change the selected feature in opposite directions; 0 makes no change.', 'youcam-ai-image-tools' ),
			),
			'breast-augmentation' => array(
				'photo' => __( 'Upload a clear photo showing the person’s upper body.', 'youcam-ai-image-tools' ),
				'intensity' => __( 'Choose the effect strength. Level 3 is the strongest.', 'youcam-ai-image-tools' ),
			),
			'face-reshape' => array(
				'photo' => __( 'Upload a clear portrait with a visible face. The adjustment applies to the first detected face.', 'youcam-ai-image-tools' ),
				'feature' => __( 'Choose the facial feature to adjust.', 'youcam-ai-image-tools' ),
				'amount' => __( 'Set the adjustment from -100 to 100. A value of 0 makes no change.', 'youcam-ai-image-tools' ),
			),
			'face-lift' => array(
				'photo' => __( 'Upload a clear, front-facing photo with one visible face.', 'youcam-ai-image-tools' ),
				'feature_eye_bag' => __( 'Set eye-bag enhancement strength from 0 (off) to 100 (maximum).', 'youcam-ai-image-tools' ),
				'feature_cheek' => __( 'Set cheek enhancement strength from 0 (off) to 100 (maximum).', 'youcam-ai-image-tools' ),
				'feature_forehead' => __( 'Set forehead enhancement strength from 0 (off) to 100 (maximum).', 'youcam-ai-image-tools' ),
				'feature_shape' => __( 'Set face-shape enhancement strength from 0 (off) to 100 (maximum).', 'youcam-ai-image-tools' ),
				'feature_mouth' => __( 'Set mouth enhancement strength from 0 (off) to 100 (maximum).', 'youcam-ai-image-tools' ),
			),
			'smile' => array(
				'photo' => __( 'Upload a clear portrait photo to adjust the person’s expression.', 'youcam-ai-image-tools' ),
				'expression_type' => __( 'Choose whether the generated smile shows teeth or keeps the mouth closed.', 'youcam-ai-image-tools' ),
			),
			'bangs-filter' => array( 'photo' => __( 'Upload a clear portrait with the person’s hair visible.', 'youcam-ai-image-tools' ), 'template_id' => __( 'Choose a predefined bangs style. Available styles are loaded from the API.', 'youcam-ai-image-tools' ) ),
			'beard-style' => array( 'photo' => __( 'Upload a clear JPG portrait with the lower face visible. The API supports images up to 1023 pixels per side.', 'youcam-ai-image-tools' ), 'template_id' => __( 'Choose a predefined beard style. Available styles are loaded from the API.', 'youcam-ai-image-tools' ) ),
			'hair-color' => array( 'photo' => __( 'Upload a clear portrait with visible hair. The image must be at least 320 pixels on each side and smaller than 1920 × 1080 pixels.', 'youcam-ai-image-tools' ), 'preset' => __( 'Choose a preset color or color combination to apply to the hair.', 'youcam-ai-image-tools' ) ),
			'hair-extension' => array( 'photo' => __( 'Upload a clear portrait with the person’s hair visible.', 'youcam-ai-image-tools' ), 'template_id' => __( 'Choose a predefined hair-extension style. Available styles are loaded from the API.', 'youcam-ai-image-tools' ) ),
			'hair-style' => array( 'photo' => __( 'Upload a clear portrait with the person’s hair visible.', 'youcam-ai-image-tools' ), 'template_id' => __( 'Choose a predefined hairstyle. Available styles are loaded from the API.', 'youcam-ai-image-tools' ) ),
			'hair-volume' => array( 'photo' => __( 'Upload a clear portrait with the person’s hair visible.', 'youcam-ai-image-tools' ), 'template_id' => __( 'Choose a predefined hair-volume style. Available styles are loaded from the API.', 'youcam-ai-image-tools' ) ),
			'look-try-on' => array( 'photo' => __( 'Upload a clear portrait to apply a complete makeup look.', 'youcam-ai-image-tools' ), 'template_id' => __( 'Choose a predefined makeup look. Available looks are loaded from the API.', 'youcam-ai-image-tools' ) ),
			'makeup-transfer' => array( 'photo' => __( 'Upload the face photo that will receive the makeup.', 'youcam-ai-image-tools' ), 'reference' => __( 'Upload the reference photo whose makeup should be transferred.', 'youcam-ai-image-tools' ) ),
			'nail-transfer' => array( 'photo' => __( 'Upload a clear photo of the hand that will receive the nail design.', 'youcam-ai-image-tools' ), 'reference' => __( 'Upload the reference image containing the nail design to transfer.', 'youcam-ai-image-tools' ) ),
			'teeth-whitening' => array( 'photo' => __( 'Upload a clear, front-facing portrait with visible teeth. Both image sides must be at least 320 pixels.', 'youcam-ai-image-tools' ) ),
			'wavy-hair' => array( 'photo' => __( 'Upload a clear portrait with the person’s hair visible.', 'youcam-ai-image-tools' ), 'template_id' => __( 'Choose a predefined wavy or curly style. Available styles are loaded from the API.', 'youcam-ai-image-tools' ) ),
		);
	}

	public function enqueue( $hook ) {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( in_array( $hook, array( 'post.php', 'post-new.php', 'upload.php' ), true ) && current_user_can( 'upload_files' ) ) {
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			$is_attachment_screen = 'post.php' === $hook && $screen && 'attachment' === $screen->post_type && isset( $_GET['post'] );
			if ( $is_attachment_screen ) {
				$attachment_id = absint( $_GET['post'] );
				if ( wp_attachment_is_image( $attachment_id ) && current_user_can( 'edit_post', $attachment_id ) ) {
					wp_enqueue_media();
					wp_enqueue_script( 'ycait-attachment-enhance', YOUCAM_AI_IMAGE_TOOLS_URL . 'admin/js/youcam-ai-image-tools-attachment-enhance.js', array(), YOUCAM_AI_IMAGE_TOOLS_VERSION, true );
					wp_localize_script( 'ycait-attachment-enhance', 'ycaitAttachmentEnhance', array(
						'attachmentId' => $attachment_id,
						'ajaxUrl' => admin_url( 'admin-ajax.php' ),
						'nonce' => wp_create_nonce( 'ycait_attachment_enhancement' ),
						'title' => __( 'Enhance Image', 'youcam-ai-image-tools' ),
						'faceBodyTitle' => __( 'Enhance Face & Body', 'youcam-ai-image-tools' ),
						'beautyTitle' => __( 'Enhance Beauty', 'youcam-ai-image-tools' ),
						'error' => __( 'The enhancement panel could not be opened. Reload the page and try again.', 'youcam-ai-image-tools' ),
					) );
				}
			}
			if ( 'upload.php' === $hook ) {
				wp_enqueue_media();
				wp_enqueue_script( 'ycait-attachment-enhance', YOUCAM_AI_IMAGE_TOOLS_URL . 'admin/js/youcam-ai-image-tools-attachment-enhance.js', array( 'media-views' ), YOUCAM_AI_IMAGE_TOOLS_VERSION, true );
				wp_localize_script( 'ycait-attachment-enhance', 'ycaitAttachmentEnhance', array(
					'attachmentId' => 0,
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'ycait_attachment_enhancement' ),
					'title' => __( 'Enhance Image', 'youcam-ai-image-tools' ),
					'faceBodyTitle' => __( 'Enhance Face & Body', 'youcam-ai-image-tools' ),
					'beautyTitle' => __( 'Enhance Beauty', 'youcam-ai-image-tools' ),
					'error' => __( 'The enhancement panel could not be opened. Reload the page and try again.', 'youcam-ai-image-tools' ),
					'mediaLibrary' => true,
				) );
			}
			if ( $screen && 'attachment' === $screen->post_type ) { return; }
			$post_type = $screen && ! empty( $screen->post_type ) ? $screen->post_type : 'post';
			if ( post_type_supports( $post_type, 'thumbnail' ) ) {
				wp_enqueue_media();
				wp_enqueue_script( 'ycait-media-generator', YOUCAM_AI_IMAGE_TOOLS_URL . 'admin/js/youcam-ai-image-tools-media-generator.js', array( 'media-views', 'wp-util' ), YOUCAM_AI_IMAGE_TOOLS_VERSION, true );
				wp_localize_script( 'ycait-media-generator', 'ycaitMediaGenerator', array(
					'title' => __( 'AI Generate', 'youcam-ai-image-tools' ),
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'ycait_media_generator' ),
					'error' => __( 'The AI Generate panel could not be opened. Reload the page and try again.', 'youcam-ai-image-tools' ),
					'modelProduct' => 'product' === $post_type,
					'modelProductTitle' => __( 'Model Product', 'youcam-ai-image-tools' ),
					'modelProductNonce' => wp_create_nonce( 'ycait_media_model_product' ),
				) );
			}
			return;
		}
		if ( ! in_array( $page, array( 'ycait-image-generation', 'ycait-image-enhancement', 'ycait-face-body-enhancement', 'ycait-beauty-enhancements' ), true ) ) { return; }
		wp_enqueue_media();
		wp_enqueue_script( 'ycait-tools', YOUCAM_AI_IMAGE_TOOLS_URL . 'admin/js/youcam-ai-image-tools-tools.js', array( 'media-views' ), YOUCAM_AI_IMAGE_TOOLS_VERSION, true );
		wp_localize_script( 'ycait-tools', 'ycaitTools', array( 'ajaxUrl' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'ycait_tools' ), 'polling' => __( 'Working…', 'youcam-ai-image-tools' ), 'saving' => __( 'Saving…', 'youcam-ai-image-tools' ), 'saved' => __( 'Saved to Media Library', 'youcam-ai-image-tools' ), 'save' => __( 'Save to Media Library', 'youcam-ai-image-tools' ), 'error' => __( 'The request could not be completed.', 'youcam-ai-image-tools' ), 'close' => __( 'Close', 'youcam-ai-image-tools' ), 'resultTitle' => __( 'Your result', 'youcam-ai-image-tools' ), 'resultReady' => __( 'Your image is ready.', 'youcam-ai-image-tools' ), 'resultMissing' => __( 'The task finished, but the API response did not include a result image URL.', 'youcam-ai-image-tools' ), 'openImage' => __( 'Open full-size image', 'youcam-ai-image-tools' ), 'viewAttachment' => __( 'View attachment', 'youcam-ai-image-tools' ), 'timeout' => __( 'Processing is taking longer than expected. Check again later.', 'youcam-ai-image-tools' ), 'dragDrop' => __( 'Drag & drop your photo here, or click to browse', 'youcam-ai-image-tools' ), 'fileHelp' => __( 'JPG or PNG, smaller than 10 MB', 'youcam-ai-image-tools' ), 'invalidFile' => __( 'Use a JPG or PNG image.', 'youcam-ai-image-tools' ), 'largeFile' => __( 'Image files must be smaller than 10 MB.', 'youcam-ai-image-tools' ), 'previewAlt' => __( 'Selected image preview', 'youcam-ai-image-tools' ), 'fieldDescriptions' => $this->get_field_descriptions() ) );
	}

	private function header( $title ) {
		if ( ! current_user_can( 'edit_posts' ) ) { wp_die( esc_html__( 'You do not have permission to use these tools.', 'youcam-ai-image-tools' ) ); }
		echo '<div class="wrap"><h1>' . esc_html( $title ) . '</h1><p>' . esc_html__( 'Choose a tool to enter its options and submit a task. Results are returned when processing finishes.', 'youcam-ai-image-tools' ) . '</p>';
	}

	private function render_tool_launcher( $tool, $title, $description, $action ) {
		echo '<section class="ycait-tool-card"><h2>' . esc_html( $title ) . '</h2><p>' . esc_html( $description ) . '</p><button type="button" class="button button-primary ycait-tool-launch" data-tool="' . esc_attr( $tool ) . '" data-title="' . esc_attr( $title ) . '">' . esc_html( $action ) . '</button></section>';
	}

	private function render_upload_field( $name, $label ) {
		?>
		<div class="ycait-upload-control" data-image-source-control>
			<span class="ycait-upload-field-label"><?php echo esc_html( $label ); ?></span>
			<div class="ycait-image-source-tabs" role="tablist">
				<button type="button" class="ycait-image-source-tab is-active" role="tab" aria-selected="true" data-image-source="upload"><?php esc_html_e( 'Upload your own photo', 'youcam-ai-image-tools' ); ?></button>
				<button type="button" class="ycait-image-source-tab" role="tab" aria-selected="false" data-image-source="library"><?php esc_html_e( 'Select from library', 'youcam-ai-image-tools' ); ?></button>
			</div>
			<div class="ycait-image-source-panel is-active" data-image-source-panel="upload">
				<label class="ycait-admin-dropzone">
					<input class="ycait-admin-file-input" type="file" name="<?php echo esc_attr( $name ); ?>" accept="image/jpeg,image/png">
					<span class="ycait-admin-dropzone-icon" aria-hidden="true">&#128247;</span>
					<span class="ycait-admin-dropzone-text"><?php esc_html_e( 'Drag & drop your photo here, or click to browse', 'youcam-ai-image-tools' ); ?></span>
					<span class="ycait-admin-file-name"><?php esc_html_e( 'JPG or PNG, smaller than 10 MB', 'youcam-ai-image-tools' ); ?></span>
				</label>
				<div class="ycait-admin-file-preview" hidden></div>
			</div>
			<div class="ycait-image-source-panel" data-image-source-panel="library" hidden>
				<button type="button" class="ycait-library-select ycait-admin-dropzone">
					<span class="ycait-admin-dropzone-icon" aria-hidden="true">&#128247;</span>
					<span class="ycait-admin-dropzone-text"><?php esc_html_e( 'Choose a photo from your Media Library', 'youcam-ai-image-tools' ); ?></span>
					<span class="ycait-admin-file-name"><?php esc_html_e( 'JPG or PNG, smaller than 10 MB', 'youcam-ai-image-tools' ); ?></span>
				</button>
				<input type="hidden" name="<?php echo esc_attr( $name ); ?>_attachment_id" value="">
				<div class="ycait-library-preview" hidden></div>
			</div>
		</div>
		<?php
	}

	private function render_tool_modal() {
		?>
		<div class="ycait-tools-modal" hidden>
			<div class="ycait-tools-modal-backdrop">
				<section class="ycait-tools-dialog" role="dialog" aria-modal="true" aria-labelledby="ycait-tools-dialog-title" tabindex="-1">
					<header class="ycait-tools-dialog-header"><h2 id="ycait-tools-dialog-title"></h2><button type="button" class="ycait-tools-dialog-close" aria-label="<?php esc_attr_e( 'Close', 'youcam-ai-image-tools' ); ?>">&times;</button></header>
					<div class="ycait-tools-dialog-content"></div>
				</section>
			</div>
		</div>
		<?php
	}

	public function render_generation() {
		$this->header( __( 'Generate Images', 'youcam-ai-image-tools' ) );
		$templates = $this->api->is_configured() ? $this->api->get_tool_task( '/s2s/v2.0/task/template/headshot?page_size=20' ) : new WP_Error( 'missing_api_key', __( 'API key is not configured.', 'youcam-ai-image-tools' ) );
		$avatar_templates = $this->api->is_configured() ? $this->api->get_tool_task( '/s2s/v2.0/task/template/ai-avatar?page_size=20' ) : new WP_Error( 'missing_api_key', __( 'API key is not configured.', 'youcam-ai-image-tools' ) );
		$studio_templates = $this->api->is_configured() ? $this->api->get_tool_task( '/s2s/v2.0/task/template/ai-studio?page_size=20' ) : new WP_Error( 'missing_api_key', __( 'API key is not configured.', 'youcam-ai-image-tools' ) );
		echo '<div class="ycait-tool-grid">';
		$this->render_tool_launcher( 'text-to-image', __( 'Image Generator', 'youcam-ai-image-tools' ), __( 'Create an image from a text description.', 'youcam-ai-image-tools' ), __( 'Generate image', 'youcam-ai-image-tools' ) );
		$this->render_tool_launcher( 'headshot', __( 'Headshot Generator', 'youcam-ai-image-tools' ), __( 'Create professional headshots from a photo and style.', 'youcam-ai-image-tools' ), __( 'Generate headshots', 'youcam-ai-image-tools' ) );
		$this->render_tool_launcher( 'avatar', __( 'Avatar Generator', 'youcam-ai-image-tools' ), __( 'Create multiple styled avatars from a photo.', 'youcam-ai-image-tools' ), __( 'Generate avatars', 'youcam-ai-image-tools' ) );
		$this->render_tool_launcher( 'studio-generator', __( 'Studio Generator', 'youcam-ai-image-tools' ), __( 'Create studio-style portraits from a photo and template.', 'youcam-ai-image-tools' ), __( 'Generate studio portraits', 'youcam-ai-image-tools' ) );
		echo '</div><div class="ycait-tool-templates" hidden>';
		?>
		<template data-tool-template="text-to-image"><form class="ycait-tool-form" data-tool="text-to-image"><label><?php esc_html_e( 'Prompt', 'youcam-ai-image-tools' ); ?><textarea name="prompt" maxlength="800" required></textarea></label><label><?php esc_html_e( 'Negative prompt (optional)', 'youcam-ai-image-tools' ); ?><textarea name="negative_prompt" maxlength="500"></textarea></label><label><?php esc_html_e( 'Image size', 'youcam-ai-image-tools' ); ?><select name="size"><option value="1664*928">Landscape</option><option value="1472*1104">Wide landscape</option><option value="1328*1328">Square</option><option value="1104*1472">Portrait</option><option value="928*1664">Tall portrait</option></select></label><button type="submit" class="button button-primary"><?php esc_html_e( 'Generate image', 'youcam-ai-image-tools' ); ?></button></form></template>
		<template data-tool-template="headshot"><form class="ycait-tool-form" data-tool="headshot"><?php $this->render_upload_field( 'photo', __( 'Photo', 'youcam-ai-image-tools' ) ); ?><label><?php esc_html_e( 'Headshot style', 'youcam-ai-image-tools' ); ?><select name="template_id" required><option value=""><?php esc_html_e( 'Choose a style', 'youcam-ai-image-tools' ); ?></option><?php if ( ! is_wp_error( $templates ) ) : foreach ( ( $templates['data']['templates'] ?? array() ) as $template ) : ?><option value="<?php echo esc_attr( $template['id'] ?? '' ); ?>"><?php echo esc_html( $template['title'] ?? $template['id'] ?? '' ); ?></option><?php endforeach; endif; ?></select></label><?php if ( is_wp_error( $templates ) ) : ?><p><?php esc_html_e( 'Headshot styles are unavailable. Check the API key and refresh this page.', 'youcam-ai-image-tools' ); ?></p><?php endif; ?><button type="submit" class="button button-primary"><?php esc_html_e( 'Generate headshots', 'youcam-ai-image-tools' ); ?></button></form></template>
		<template data-tool-template="avatar"><form class="ycait-tool-form" data-tool="avatar"><?php $this->render_upload_field( 'photo', __( 'Photo', 'youcam-ai-image-tools' ) ); ?><label><?php esc_html_e( 'Avatar style', 'youcam-ai-image-tools' ); ?><select name="template_id" required><option value=""><?php esc_html_e( 'Choose a style', 'youcam-ai-image-tools' ); ?></option><?php if ( ! is_wp_error( $avatar_templates ) ) : foreach ( ( $avatar_templates['data']['templates'] ?? array() ) as $template ) : ?><option value="<?php echo esc_attr( $template['id'] ?? '' ); ?>"><?php echo esc_html( $template['title'] ?? $template['id'] ?? '' ); ?></option><?php endforeach; endif; ?></select></label><label><?php esc_html_e( 'Number of images', 'youcam-ai-image-tools' ); ?><select name="output_count"><option value="4">4</option><option value="8">8</option><option value="12">12</option><option value="20">20</option></select></label><?php if ( is_wp_error( $avatar_templates ) || empty( $avatar_templates['data']['templates'] ) ) : ?><p><?php esc_html_e( 'Avatar styles are unavailable. Check the API key and refresh this page.', 'youcam-ai-image-tools' ); ?></p><?php endif; ?><button type="submit" class="button button-primary"><?php esc_html_e( 'Generate avatars', 'youcam-ai-image-tools' ); ?></button></form></template>
		<template data-tool-template="studio-generator"><form class="ycait-tool-form" data-tool="studio-generator"><?php $this->render_upload_field( 'photo', __( 'Photo', 'youcam-ai-image-tools' ) ); ?><label><?php esc_html_e( 'Studio style', 'youcam-ai-image-tools' ); ?><select name="template_id" required><option value=""><?php esc_html_e( 'Choose a style', 'youcam-ai-image-tools' ); ?></option><?php if ( ! is_wp_error( $studio_templates ) ) : foreach ( ( $studio_templates['data']['templates'] ?? array() ) as $template ) : ?><option value="<?php echo esc_attr( $template['id'] ?? '' ); ?>"><?php echo esc_html( $template['title'] ?? $template['id'] ?? '' ); ?></option><?php endforeach; endif; ?></select></label><label><?php esc_html_e( 'Number of images', 'youcam-ai-image-tools' ); ?><select name="output_count"><option value="2">2</option><option value="4">4</option><option value="6">6</option><option value="8">8</option></select></label><?php if ( is_wp_error( $studio_templates ) || empty( $studio_templates['data']['templates'] ) ) : ?><p><?php esc_html_e( 'Studio styles are unavailable. Check the API key and refresh this page.', 'youcam-ai-image-tools' ); ?></p><?php endif; ?><button type="submit" class="button button-primary"><?php esc_html_e( 'Generate studio portraits', 'youcam-ai-image-tools' ); ?></button></form></template>
		</div>
		<?php
		$this->render_tool_modal();
		echo '</div>';
	}

	/** Beauty tools page, including the documented preset catalogues. */
	public function render_beauty() {
		$this->header( __( 'Enhance Beauty', 'youcam-ai-image-tools' ) );
		$template_paths = array( 'bangs-filter' => 'hair-bang', 'beard-style' => 'beard-style', 'hair-extension' => 'hair-ext', 'hair-style' => 'hair-style', 'hair-volume' => 'hair-vol', 'look-try-on' => 'look-vto', 'wavy-hair' => 'hair-curl' );
		$templates = array();
		foreach ( $template_paths as $tool => $path ) { $templates[ $tool ] = $this->api->is_configured() ? $this->api->get_tool_task( '/s2s/v2.0/task/template/' . $path . '?page_size=20' ) : new WP_Error( 'missing_api_key', __( 'API key is not configured.', 'youcam-ai-image-tools' ) ); }
		$cards = array(
			'bangs-filter' => array( __( 'Bangs Filter', 'youcam-ai-image-tools' ), __( 'Preview a range of bangs styles.', 'youcam-ai-image-tools' ), __( 'Apply bangs', 'youcam-ai-image-tools' ) ),
			'beard-style' => array( __( 'Beard Style Generator', 'youcam-ai-image-tools' ), __( 'Preview a beard style on a portrait.', 'youcam-ai-image-tools' ), __( 'Generate beard style', 'youcam-ai-image-tools' ) ),
			'hair-color' => array( __( 'Hair Color', 'youcam-ai-image-tools' ), __( 'Try a preset hair color on a portrait.', 'youcam-ai-image-tools' ), __( 'Apply hair color', 'youcam-ai-image-tools' ) ),
			'hair-extension' => array( __( 'Hair Extension', 'youcam-ai-image-tools' ), __( 'Preview a hair extension style.', 'youcam-ai-image-tools' ), __( 'Try hair extension', 'youcam-ai-image-tools' ) ),
			'hair-style' => array( __( 'Hair Style', 'youcam-ai-image-tools' ), __( 'Preview a new hairstyle.', 'youcam-ai-image-tools' ), __( 'Try hairstyle', 'youcam-ai-image-tools' ) ),
			'hair-volume' => array( __( 'Hair Volume', 'youcam-ai-image-tools' ), __( 'Preview fuller hair styles.', 'youcam-ai-image-tools' ), __( 'Add hair volume', 'youcam-ai-image-tools' ) ),
			'look-try-on' => array( __( 'Look Try On', 'youcam-ai-image-tools' ), __( 'Apply a complete makeup look from a preset.', 'youcam-ai-image-tools' ), __( 'Try this look', 'youcam-ai-image-tools' ) ),
			'makeup-transfer' => array( __( 'Makeup Transfer', 'youcam-ai-image-tools' ), __( 'Transfer makeup from a reference photo.', 'youcam-ai-image-tools' ), __( 'Transfer makeup', 'youcam-ai-image-tools' ) ),
			'nail-transfer' => array( __( 'Nail Transfer', 'youcam-ai-image-tools' ), __( 'Transfer a nail design from a reference image to a hand photo.', 'youcam-ai-image-tools' ), __( 'Transfer nail design', 'youcam-ai-image-tools' ) ),
			'teeth-whitening' => array( __( 'Teeth Whitening', 'youcam-ai-image-tools' ), __( 'Whiten teeth in a clear, front-facing portrait.', 'youcam-ai-image-tools' ), __( 'Whiten teeth', 'youcam-ai-image-tools' ) ),
			'wavy-hair' => array( __( 'Wavy Hair', 'youcam-ai-image-tools' ), __( 'Preview a wavy or curly hairstyle.', 'youcam-ai-image-tools' ), __( 'Try wavy hair', 'youcam-ai-image-tools' ) ),
		);
		echo '<div class="ycait-tool-grid">';
		foreach ( $cards as $tool => $card ) { $this->render_tool_launcher( $tool, $card[0], $card[1], $card[2] ); }
		echo '</div><div class="ycait-tool-templates" hidden>';
		foreach ( $cards as $tool => $card ) { $this->render_beauty_form( $tool, $card[2], $templates ); }
		echo '</div>';
		$this->render_tool_modal();
		echo '</div>';
	}

	/** Render one Beauty tool's form in the shared modal. */
	private function render_beauty_form( $tool, $action, $templates ) {
		?>
		<template data-tool-template="<?php echo esc_attr( $tool ); ?>"><form class="ycait-tool-form" data-tool="<?php echo esc_attr( $tool ); ?>">
		<?php
		if ( in_array( $tool, array( 'makeup-transfer', 'nail-transfer' ), true ) ) {
			$this->render_upload_field( 'photo', __( 'Your photo', 'youcam-ai-image-tools' ) );
			$this->render_upload_field( 'reference', __( 'Reference photo', 'youcam-ai-image-tools' ) );
		} else {
			$this->render_upload_field( 'photo', __( 'Photo', 'youcam-ai-image-tools' ) );
		}
		if ( 'hair-color' === $tool ) :
			$presets = array( 'Jet Black', 'Chocolate Brown', 'Honey Blonde', 'Platinum Blonde', 'Ash Gray', 'Rose Gold', 'Burgundy', 'Copper Red', 'Lavender', 'Teal Blue', 'Dark Brown/Caramel Blonde', 'Jet Black/Silver Gray', 'Ash Brown/Lavender', 'Rose Gold/Peach Blonde', 'Burgundy/Magenta Pink', 'Deep Blue/Teal Green', 'Plum Purple/Pastel Lilac', 'Copper Red/Golden Blonde', 'Dark Gray/Ice Blonde', 'Midnight Blue/Denim Blue' );
			?><label><?php esc_html_e( 'Preset color', 'youcam-ai-image-tools' ); ?><select name="preset" required><?php foreach ( $presets as $preset ) : ?><option value="<?php echo esc_attr( $preset ); ?>"><?php echo esc_html( $preset ); ?></option><?php endforeach; ?></select></label><?php
		elseif ( in_array( $tool, array( 'bangs-filter', 'beard-style', 'hair-extension', 'hair-style', 'hair-volume', 'look-try-on', 'wavy-hair' ), true ) ) :
			$template_path = array( 'bangs-filter' => 'hair-bang', 'beard-style' => 'beard-style', 'hair-extension' => 'hair-ext', 'hair-style' => 'hair-style', 'hair-volume' => 'hair-vol', 'look-try-on' => 'look-vto', 'wavy-hair' => 'hair-curl' )[ $tool ];
			$template_result = $templates[ $tool ] ?? new WP_Error( 'missing_templates', __( 'Styles are unavailable.', 'youcam-ai-image-tools' ) );
			?><label><?php esc_html_e( 'Style', 'youcam-ai-image-tools' ); ?><select name="template_id" required><option value=""><?php esc_html_e( 'Choose a style', 'youcam-ai-image-tools' ); ?></option><?php if ( ! is_wp_error( $template_result ) ) : foreach ( ( $template_result['data']['templates'] ?? array() ) as $template ) : ?><option value="<?php echo esc_attr( $template['id'] ?? '' ); ?>"><?php echo esc_html( $template['title'] ?? $template['id'] ?? '' ); ?></option><?php endforeach; endif; ?></select></label><?php if ( is_wp_error( $template_result ) || empty( $template_result['data']['templates'] ) ) : ?><p><?php esc_html_e( 'Styles are unavailable. Check the API key and refresh this page.', 'youcam-ai-image-tools' ); ?></p><?php endif; ?><?php
		endif;
		if ( 'nail-transfer' === $tool ) { ?><p><?php esc_html_e( 'Choose a clear hand photo and a reference image with visible nails.', 'youcam-ai-image-tools' ); ?></p><?php }
		if ( 'makeup-transfer' === $tool ) { ?><p><?php esc_html_e( 'Use clear, front-facing photos with one fully visible face in each image.', 'youcam-ai-image-tools' ); ?></p><?php }
		?><button type="submit" class="button button-primary"><?php echo esc_html( $action ); ?></button></form></template>
		<?php
	}

	public function render_enhancement() {
		$this->header( __( 'Enhance Images', 'youcam-ai-image-tools' ) );
		echo '<div class="ycait-tool-grid">';
		$this->render_tool_launcher( 'bg-replace', __( 'Background Change', 'youcam-ai-image-tools' ), __( 'Replace the background using a text description.', 'youcam-ai-image-tools' ), __( 'Change background', 'youcam-ai-image-tools' ) );
		$this->render_tool_launcher( 'enhance', __( 'Photo Enhance', 'youcam-ai-image-tools' ), __( 'Upscale a photo at the selected enhancement scale.', 'youcam-ai-image-tools' ), __( 'Enhance photo', 'youcam-ai-image-tools' ) );
		$this->render_tool_launcher( 'lighting', __( 'Photo Lighting', 'youcam-ai-image-tools' ), __( 'Improve the lighting in a photo.', 'youcam-ai-image-tools' ), __( 'Improve lighting', 'youcam-ai-image-tools' ) );
		$this->render_tool_launcher( 'color-correction', __( 'Photo Color Correction', 'youcam-ai-image-tools' ), __( 'Correct color and balance in a photo.', 'youcam-ai-image-tools' ), __( 'Correct colors', 'youcam-ai-image-tools' ) );
		$this->render_tool_launcher( 'bg-blur', __( 'Background Blur', 'youcam-ai-image-tools' ), __( 'Blur the background while keeping the subject clear.', 'youcam-ai-image-tools' ), __( 'Blur background', 'youcam-ai-image-tools' ) );
		$this->render_tool_launcher( 'face-swap', __( 'Face Swap', 'youcam-ai-image-tools' ), __( 'Replace the target photo’s face with a reference face.', 'youcam-ai-image-tools' ), __( 'Swap face', 'youcam-ai-image-tools' ) );
		$this->render_tool_launcher( 'image-extender', __( 'Image Extender', 'youcam-ai-image-tools' ), __( 'Expand a photo onto a larger canvas.', 'youcam-ai-image-tools' ), __( 'Extend image', 'youcam-ai-image-tools' ) );
		$this->render_tool_launcher( 'photo-colorize', __( 'Photo Colorize', 'youcam-ai-image-tools' ), __( 'Generate colorized versions of a black-and-white photo.', 'youcam-ai-image-tools' ), __( 'Colorize photo', 'youcam-ai-image-tools' ) );
		$this->render_tool_launcher( 'watermark-removal', __( 'Watermark Removal', 'youcam-ai-image-tools' ), __( 'Remove marks from an image you have permission to edit.', 'youcam-ai-image-tools' ), __( 'Remove watermark', 'youcam-ai-image-tools' ) );
		echo '</div><div class="ycait-tool-templates" hidden>';
		?>
		<template data-tool-template="bg-replace"><form class="ycait-tool-form" data-tool="bg-replace"><?php $this->render_upload_field( 'photo', __( 'Photo', 'youcam-ai-image-tools' ) ); ?><label><?php esc_html_e( 'New background description', 'youcam-ai-image-tools' ); ?><textarea name="prompt" maxlength="500" placeholder="A sunny beach with palm trees" required></textarea></label><button type="submit" class="button button-primary"><?php esc_html_e( 'Change background', 'youcam-ai-image-tools' ); ?></button></form></template>
		<template data-tool-template="enhance"><form class="ycait-tool-form" data-tool="enhance"><?php $this->render_upload_field( 'photo', __( 'Photo', 'youcam-ai-image-tools' ) ); ?><label><?php esc_html_e( 'Enhancement scale', 'youcam-ai-image-tools' ); ?><select name="scale"><option value="1">1×</option><option value="2">2×</option><option value="4">4×</option></select></label><button type="submit" class="button button-primary"><?php esc_html_e( 'Enhance photo', 'youcam-ai-image-tools' ); ?></button></form></template>
		<template data-tool-template="lighting"><form class="ycait-tool-form" data-tool="lighting"><?php $this->render_upload_field( 'photo', __( 'Photo', 'youcam-ai-image-tools' ) ); ?><button type="submit" class="button button-primary"><?php esc_html_e( 'Improve lighting', 'youcam-ai-image-tools' ); ?></button></form></template>
		<template data-tool-template="color-correction"><form class="ycait-tool-form" data-tool="color-correction"><?php $this->render_upload_field( 'photo', __( 'Photo', 'youcam-ai-image-tools' ) ); ?><button type="submit" class="button button-primary"><?php esc_html_e( 'Correct colors', 'youcam-ai-image-tools' ); ?></button></form></template>
		<template data-tool-template="bg-blur"><form class="ycait-tool-form" data-tool="bg-blur"><?php $this->render_upload_field( 'photo', __( 'Photo', 'youcam-ai-image-tools' ) ); ?><label><?php esc_html_e( 'Blur intensity', 'youcam-ai-image-tools' ); ?> <span class="ycait-range-value">50</span><input type="range" name="intensity" min="0" max="100" value="50" oninput="this.previousElementSibling.textContent=this.value"></label><button type="submit" class="button button-primary"><?php esc_html_e( 'Blur background', 'youcam-ai-image-tools' ); ?></button></form></template>
		<template data-tool-template="face-swap"><form class="ycait-tool-form" data-tool="face-swap"><?php $this->render_upload_field( 'photo', __( 'Target photo', 'youcam-ai-image-tools' ) ); ?><?php $this->render_upload_field( 'reference', __( 'Reference face photo', 'youcam-ai-image-tools' ) ); ?><p><?php esc_html_e( 'Swaps the first detected face in the target photo with the reference photo.', 'youcam-ai-image-tools' ); ?></p><button type="submit" class="button button-primary"><?php esc_html_e( 'Swap face', 'youcam-ai-image-tools' ); ?></button></form></template>
		<template data-tool-template="image-extender"><form class="ycait-tool-form" data-tool="image-extender"><?php $this->render_upload_field( 'photo', __( 'Photo (JPG)', 'youcam-ai-image-tools' ) ); ?><p><?php esc_html_e( 'Set a larger canvas. The original photo is centered and the new area is generated around it.', 'youcam-ai-image-tools' ); ?></p><label><?php esc_html_e( 'Output width (pixels)', 'youcam-ai-image-tools' ); ?><input type="number" name="output_width" min="1" max="4096" value="1200" required></label><label><?php esc_html_e( 'Output height (pixels)', 'youcam-ai-image-tools' ); ?><input type="number" name="output_height" min="1" max="4096" value="1200" required></label><button type="submit" class="button button-primary"><?php esc_html_e( 'Extend image', 'youcam-ai-image-tools' ); ?></button></form></template>
		<template data-tool-template="photo-colorize"><form class="ycait-tool-form" data-tool="photo-colorize"><?php $this->render_upload_field( 'photo', __( 'Photo', 'youcam-ai-image-tools' ) ); ?><button type="submit" class="button button-primary"><?php esc_html_e( 'Colorize photo', 'youcam-ai-image-tools' ); ?></button></form></template>
		<template data-tool-template="watermark-removal"><form class="ycait-tool-form" data-tool="watermark-removal"><?php $this->render_upload_field( 'photo', __( 'Photo', 'youcam-ai-image-tools' ) ); ?><p><?php esc_html_e( 'Only remove marks from images you own or have permission to edit.', 'youcam-ai-image-tools' ); ?></p><button type="submit" class="button button-primary"><?php esc_html_e( 'Remove watermark', 'youcam-ai-image-tools' ); ?></button></form></template>
		</div>
		<?php
		$this->render_tool_modal();
		echo '</div>';
	}

	/** Face and body effects using the shared tool modal and result workflow. */
	public function render_face_body() {
		$this->header( __( 'Enhance Face & Body', 'youcam-ai-image-tools' ) );
		echo '<div class="ycait-tool-grid">';
		$this->render_tool_launcher( 'abs-filter', __( 'Abs Filter', 'youcam-ai-image-tools' ), __( 'Apply a six-pack or vest-line effect to a photo.', 'youcam-ai-image-tools' ), __( 'Apply Abs Filter', 'youcam-ai-image-tools' ) );
		$this->render_tool_launcher( 'aging', __( 'Aging Simulation', 'youcam-ai-image-tools' ), __( 'Preview an aged version of a portrait.', 'youcam-ai-image-tools' ), __( 'Simulate aging', 'youcam-ai-image-tools' ) );
		$this->render_tool_launcher( 'body-reshape', __( 'Body Reshape', 'youcam-ai-image-tools' ), __( 'Adjust body proportions and posture.', 'youcam-ai-image-tools' ), __( 'Reshape body', 'youcam-ai-image-tools' ) );
		$this->render_tool_launcher( 'breast-augmentation', __( 'Breast Augmentation', 'youcam-ai-image-tools' ), __( 'Adjust breast size with the selected intensity.', 'youcam-ai-image-tools' ), __( 'Augment breasts', 'youcam-ai-image-tools' ) );
		$this->render_tool_launcher( 'face-reshape', __( 'Face Reshape', 'youcam-ai-image-tools' ), __( 'Adjust facial features with custom controls.', 'youcam-ai-image-tools' ), __( 'Reshape face', 'youcam-ai-image-tools' ) );
		$this->render_tool_launcher( 'face-lift', __( 'Face Lift', 'youcam-ai-image-tools' ), __( 'Refine facial features with adjustable enhancements.', 'youcam-ai-image-tools' ), __( 'Apply face lift', 'youcam-ai-image-tools' ) );
		$this->render_tool_launcher( 'smile', __( 'AI Smile', 'youcam-ai-image-tools' ), __( 'Adjust the expression in a portrait.', 'youcam-ai-image-tools' ), __( 'Add a smile', 'youcam-ai-image-tools' ) );
		echo '</div><div class="ycait-tool-templates" hidden>';
		?>
		<template data-tool-template="abs-filter"><form class="ycait-tool-form" data-tool="abs-filter"><?php $this->render_upload_field( 'photo', __( 'Photo', 'youcam-ai-image-tools' ) ); ?><label><?php esc_html_e( 'Style', 'youcam-ai-image-tools' ); ?><select name="mode"><option value="Six-pack">Six-pack</option><option value="Vest-line">Vest-line</option></select></label><label><?php esc_html_e( 'Intensity', 'youcam-ai-image-tools' ); ?><select name="intensity"><option value="1">1</option><option value="2">2</option></select></label><button type="submit" class="button button-primary"><?php esc_html_e( 'Apply Abs Filter', 'youcam-ai-image-tools' ); ?></button></form></template>
		<template data-tool-template="aging"><form class="ycait-tool-form" data-tool="aging"><?php $this->render_upload_field( 'photo', __( 'Photo', 'youcam-ai-image-tools' ) ); ?><button type="submit" class="button button-primary"><?php esc_html_e( 'Simulate aging', 'youcam-ai-image-tools' ); ?></button></form></template>
		<template data-tool-template="body-reshape"><form class="ycait-tool-form" data-tool="body-reshape"><?php $this->render_upload_field( 'photo', __( 'Photo', 'youcam-ai-image-tools' ) ); ?><p><?php esc_html_e( 'Choose one adjustment from -100 to 100. The effect is applied to the first detected person.', 'youcam-ai-image-tools' ); ?></p><label><?php esc_html_e( 'Adjustment', 'youcam-ai-image-tools' ); ?><select name="feature"><option value="waist">Waist</option><option value="belly">Belly</option><option value="hip">Hip</option><option value="leg">Leg</option><option value="arm">Arm</option><option value="breast">Breast</option><option value="slim">Slim</option><option value="taller">Taller</option></select></label><label><?php esc_html_e( 'Amount (-100 to 100)', 'youcam-ai-image-tools' ); ?><input type="number" name="amount" min="-100" max="100" step="1" value="20" required></label><button type="submit" class="button button-primary"><?php esc_html_e( 'Reshape body', 'youcam-ai-image-tools' ); ?></button></form></template>
		<template data-tool-template="breast-augmentation"><form class="ycait-tool-form" data-tool="breast-augmentation"><?php $this->render_upload_field( 'photo', __( 'Photo', 'youcam-ai-image-tools' ) ); ?><label><?php esc_html_e( 'Intensity', 'youcam-ai-image-tools' ); ?><select name="intensity"><option value="1">1</option><option value="2" selected>2</option><option value="3">3</option></select></label><button type="submit" class="button button-primary"><?php esc_html_e( 'Augment breasts', 'youcam-ai-image-tools' ); ?></button></form></template>
		<template data-tool-template="face-reshape"><form class="ycait-tool-form" data-tool="face-reshape"><?php $this->render_upload_field( 'photo', __( 'Photo', 'youcam-ai-image-tools' ) ); ?><p><?php esc_html_e( 'Choose one facial adjustment from -100 to 100. The effect is applied to the first detected face.', 'youcam-ai-image-tools' ); ?></p><label><?php esc_html_e( 'Adjustment', 'youcam-ai-image-tools' ); ?><select name="feature"><?php foreach ( array( 'face_reshape' => 'Face shape', 'face_width' => 'Face width', 'jaw' => 'Jaw', 'cheekbones' => 'Cheekbones', 'eye_size' => 'Eye size', 'eye_distance' => 'Eye distance', 'nose_size' => 'Nose size', 'nose_lift' => 'Nose lift', 'lip_size' => 'Lip size', 'lip_width' => 'Lip width', 'chin_length' => 'Chin length' ) as $feature => $label ) : ?><option value="<?php echo esc_attr( $feature ); ?>"><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></label><label><?php esc_html_e( 'Amount (-100 to 100)', 'youcam-ai-image-tools' ); ?><input type="number" name="amount" min="-100" max="100" step="1" value="20" required></label><button type="submit" class="button button-primary"><?php esc_html_e( 'Reshape face', 'youcam-ai-image-tools' ); ?></button></form></template>
		<template data-tool-template="face-lift"><form class="ycait-tool-form" data-tool="face-lift"><?php $this->render_upload_field( 'photo', __( 'Photo', 'youcam-ai-image-tools' ) ); ?><p><?php esc_html_e( 'Use a clear, front-facing photo with one visible face. The Face Lift API supports images up to 1920 pixels per side.', 'youcam-ai-image-tools' ); ?></p><fieldset><legend><?php esc_html_e( 'Enhancement strength (0–100)', 'youcam-ai-image-tools' ); ?></legend><?php foreach ( array( 'eye_bag' => __( 'Eye bags', 'youcam-ai-image-tools' ), 'cheek' => __( 'Cheeks', 'youcam-ai-image-tools' ), 'forehead' => __( 'Forehead', 'youcam-ai-image-tools' ), 'shape' => __( 'Face shape', 'youcam-ai-image-tools' ), 'mouth' => __( 'Mouth', 'youcam-ai-image-tools' ) ) as $feature => $label ) : ?><label><?php echo esc_html( $label ); ?><input type="number" name="feature_<?php echo esc_attr( $feature ); ?>" min="0" max="100" step="1" value="0"></label><?php endforeach; ?></fieldset><button type="submit" class="button button-primary"><?php esc_html_e( 'Apply face lift', 'youcam-ai-image-tools' ); ?></button></form></template>
		<template data-tool-template="smile"><form class="ycait-tool-form" data-tool="smile"><?php $this->render_upload_field( 'photo', __( 'Photo', 'youcam-ai-image-tools' ) ); ?><label><?php esc_html_e( 'Smile style', 'youcam-ai-image-tools' ); ?><select name="expression_type"><option value="smile_with_teeth_visible"><?php esc_html_e( 'Smile with teeth visible', 'youcam-ai-image-tools' ); ?></option><option value="closed_mouth_smile"><?php esc_html_e( 'Closed-mouth smile', 'youcam-ai-image-tools' ); ?></option></select></label><button type="submit" class="button button-primary"><?php esc_html_e( 'Add a smile', 'youcam-ai-image-tools' ); ?></button></form></template>
		</div>
		<?php
		$this->render_tool_modal();
		echo '</div>';
	}

	private function check_request() {
		if ( ! isset( $_POST['nonce'] ) || ! is_string( $_POST['nonce'] ) ) { wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh the page and try again.', 'youcam-ai-image-tools' ) ), 403 ); }
		check_ajax_referer( 'ycait_tools', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) ) { wp_send_json_error( array( 'message' => __( 'Permission denied.', 'youcam-ai-image-tools' ) ), 403 ); }
	}

	/** Read one scalar AJAX field and sanitize it for its expected use. */
	private function post_value( $key, $default = '', $sanitizer = 'sanitize_text_field' ) {
		if ( ! isset( $_POST[ $key ] ) || ! is_scalar( $_POST[ $key ] ) ) { return $default; }
		return call_user_func( $sanitizer, wp_unslash( (string) $_POST[ $key ] ) );
	}

	/** Preserve signed URL query parameters; sanitize_text_field() can strip percent escapes. */
	private function post_url_value( $key ) {
		if ( ! isset( $_POST[ $key ] ) || ! is_string( $_POST[ $key ] ) ) { return ''; }
		return trim( wp_unslash( $_POST[ $key ] ) );
	}

	private function post_uint( $key, $default ) {
		$value = $this->post_value( $key, (string) $default );
		return preg_match( '/^\d+$/', $value ) ? absint( $value ) : $default;
	}

	private function requester_key() {
		return hash_hmac( 'sha256', 'user:' . get_current_user_id(), wp_salt( 'auth' ) );
	}

	private function task_owner_key( $task_id ) { return 'ycait_tool_task_' . hash( 'sha256', (string) $task_id ); }

	private function store_task_owner( $task_id, $tool, $stage = 'final', $product_type = '' ) {
		$owner = array( 'requester' => $this->requester_key(), 'tool' => $tool, 'stage' => $stage );
		if ( $product_type ) { $owner['product_type'] = $product_type; }
		set_transient( $this->task_owner_key( $task_id ), $owner, DAY_IN_SECONDS );
	}

	private function validate_task_id( $task_id ) {
		return is_string( $task_id ) && 1 <= strlen( $task_id ) && 200 >= strlen( $task_id ) && (bool) preg_match( '/^[A-Za-z0-9_-]+$/', $task_id );
	}

	private function normalize_result_url( $url ) {
		if ( ! is_string( $url ) ) { return ''; }
		$url = html_entity_decode( trim( $url ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		return esc_url_raw( $url, array( 'https' ) );
	}

	/** Download a result through WordPress's safe HTTP transport, following validated CDN redirects. */
	private function download_result_image( $url, $tmp_file ) {
		$response = wp_safe_remote_get( $url, array(
			'timeout'             => 60,
			'redirection'         => 5,
			'stream'              => true,
			'filename'            => $tmp_file,
			'limit_response_size' => 10 * 1024 * 1024 + 1,
		) );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'ycait_result_download_failed', sprintf(
				/* translators: %s: WordPress HTTP transport error. */
				__( 'WordPress could not download the image: %s', 'youcam-ai-image-tools' ),
				$response->get_error_message()
			) );
		}

		$status_code = (int) wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			$status_message = wp_remote_retrieve_response_message( $response );
			return new WP_Error( 'ycait_result_download_http_error', sprintf(
				/* translators: 1: HTTP status code, 2: HTTP status message. */
				__( 'The image host returned HTTP %1$d%2$s while WordPress downloaded the image.', 'youcam-ai-image-tools' ),
				$status_code,
				$status_message ? ' ' . $status_message : ''
			) );
		}

		return true;
	}

	private function result_grant_key( $url ) { return 'ycait_tool_result_' . hash( 'sha256', $this->normalize_result_url( $url ) ); }

	private function task_result_key( $task_id ) { return 'ycait_tool_results_' . hash( 'sha256', (string) $task_id ); }

	private function task_authorizes_result( $task_id, $url ) {
		if ( ! $this->validate_task_id( $task_id ) ) { return false; }
		$results = get_transient( $this->task_result_key( $task_id ) );
		return is_array( $results ) && isset( $results['requester'], $results['urls'] ) && is_string( $results['requester'] ) && hash_equals( $this->requester_key(), $results['requester'] ) && is_array( $results['urls'] ) && in_array( $this->normalize_result_url( $url ), $results['urls'], true );
	}

	private function result_authorization_token( $task_id, $url, $expires ) {
		$payload = wp_json_encode( array( get_current_user_id(), (string) $task_id, $this->normalize_result_url( $url ), absint( $expires ) ) );
		return hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );
	}

	private function verify_result_authorization_token( $task_id, $url ) {
		$token = $this->post_value( 'result_token' );
		$expires = $this->post_uint( 'result_expires', 0 );
		if ( ! $this->validate_task_id( $task_id ) || ! $token || $expires < time() || $expires > time() + 15 * MINUTE_IN_SECONDS ) { return false; }
		return hash_equals( $this->result_authorization_token( $task_id, $url, $expires ), $token );
	}

	private function grant_result_url( $url ) {
		$url = $this->normalize_result_url( $url );
		if ( is_string( $url ) && 'https' === wp_parse_url( $url, PHP_URL_SCHEME ) && wp_http_validate_url( $url ) ) {
			set_transient( $this->result_grant_key( $url ), $this->requester_key(), 15 * MINUTE_IN_SECONDS );
		}
	}

	private function grant_result_urls( $value, &$urls = array(), $key = '' ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $child_key => $child ) { $this->grant_result_urls( $child, $urls, (string) $child_key ); }
		} elseif ( is_string( $value ) && 'https' === wp_parse_url( $value, PHP_URL_SCHEME ) ) {
			$urls[ $value ] = true;
		}
	}

	private function reserve_poll_slot( $task_id ) {
		global $wpdb;
		$key = 'ycait_tool_poll_' . hash( 'sha256', (string) $task_id );
		$now = time();
		$sql = $wpdb->prepare(
			"INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %d, 'no') ON DUPLICATE KEY UPDATE option_value = IF(CAST(option_value AS UNSIGNED) <= %d, %d, option_value)",
			$key, $now, $now - 2, $now
		);
		$affected = $wpdb->query( $sql );
		wp_cache_delete( $key, 'options' );
		wp_cache_delete( 'notoptions', 'options' );
		return false !== $affected && 0 !== $affected;
	}

	private function upload_input( $key, $max_dimension = 4096 ) {
		if ( empty( $_FILES[ $key ] ) || ! is_array( $_FILES[ $key ] ) || empty( $_FILES[ $key ]['tmp_name'] ) || ! is_string( $_FILES[ $key ]['tmp_name'] ) || ! isset( $_FILES[ $key ]['error'] ) || ! is_scalar( $_FILES[ $key ]['error'] ) || UPLOAD_ERR_OK !== (int) $_FILES[ $key ]['error'] || ! is_uploaded_file( $_FILES[ $key ]['tmp_name'] ) ) { return new WP_Error( 'missing_image', __( 'Please choose an image file.', 'youcam-ai-image-tools' ) ); }
		$file = $_FILES[ $key ];
		$size = filesize( $file['tmp_name'] );
		if ( false === $size || 0 === $size || $size >= 10 * 1024 * 1024 ) { return new WP_Error( 'large_image', __( 'Image files must be non-empty and smaller than 10 MB.', 'youcam-ai-image-tools' ) ); }
		$info = wp_getimagesize( $file['tmp_name'] );
		if ( ! $info || ! in_array( $info['mime'], array( 'image/jpeg', 'image/png' ), true ) || empty( $info[0] ) || empty( $info[1] ) || $info[0] > $max_dimension || $info[1] > $max_dimension ) { return new WP_Error( 'invalid_image', sprintf( /* translators: %d: maximum image side length in pixels. */ __( 'Use a JPG or PNG image with dimensions no larger than %d pixels per side.', 'youcam-ai-image-tools' ), absint( $max_dimension ) ) ); }
		$name = isset( $file['name'] ) && is_scalar( $file['name'] ) ? sanitize_file_name( (string) $file['name'] ) : 'youcam-image';
		return $this->api->upload_local_image( $file['tmp_name'], $name, $info['mime'] );
	}

	/** Accept either a newly uploaded file or an authorized Media Library image for a tool image field. */
	private function upload_image_field( $key, $max_dimension = 4096, $allowed_mimes = array( 'image/jpeg', 'image/png' ) ) {
		$attachment_id = $this->post_uint( $key . '_attachment_id', 0 );
		return $attachment_id ? $this->upload_attachment_input( $attachment_id, $max_dimension, $allowed_mimes ) : $this->upload_input( $key, $max_dimension );
	}

	/** Return the local path for whichever source was submitted, for tool-specific image checks. */
	private function image_field_path( $key ) {
		$attachment_id = $this->post_uint( $key . '_attachment_id', 0 );
		if ( $attachment_id ) { return get_attached_file( $attachment_id ); }
		return isset( $_FILES[ $key ]['tmp_name'] ) && is_string( $_FILES[ $key ]['tmp_name'] ) ? $_FILES[ $key ]['tmp_name'] : '';
	}

	/** Upload an authorized local attachment as the source image for an enhancement task. */
	private function upload_attachment_input( $attachment_id, $max_dimension = 4096, $allowed_mimes = array( 'image/jpeg', 'image/png' ) ) {
		if ( ! $attachment_id || ! wp_attachment_is_image( $attachment_id ) || ! current_user_can( 'edit_post', $attachment_id ) ) { return new WP_Error( 'invalid_attachment', __( 'You cannot use this attachment as an image source.', 'youcam-ai-image-tools' ) ); }
		$path = get_attached_file( $attachment_id );
		if ( ! $path || ! is_readable( $path ) ) { return new WP_Error( 'missing_attachment', __( 'The selected attachment file is unavailable.', 'youcam-ai-image-tools' ) ); }
		$size = filesize( $path );
		$info = wp_getimagesize( $path );
		if ( false === $size || 0 === $size || $size >= 10 * 1024 * 1024 || ! $info || ! in_array( $info['mime'], $allowed_mimes, true ) || empty( $info[0] ) || empty( $info[1] ) || $info[0] > $max_dimension || $info[1] > $max_dimension ) {
			$formats = in_array( 'image/webp', $allowed_mimes, true ) ? 'JPG, PNG, or WebP' : 'JPG or PNG';
			return new WP_Error( 'invalid_attachment_image', sprintf( /* translators: 1: allowed image formats, 2: maximum image side length. */ __( 'The selected attachment must be a %1$s image smaller than 10 MB and no larger than %2$d pixels per side.', 'youcam-ai-image-tools' ), $formats, absint( $max_dimension ) ) );
		}
		return $this->api->upload_local_image( $path, basename( $path ), $info['mime'] );
	}

	/** Upload one of the configured model images, whether stored as an attachment or HTTPS URL. */
	private function upload_configured_model( $gender ) {
		$value = $this->settings->get( 'default_model_' . $gender, '' );
		if ( is_numeric( $value ) ) { return $this->upload_attachment_input( absint( $value ), 4096, array( 'image/jpeg', 'image/png', 'image/webp' ) ); }
		if ( ! is_string( $value ) || 'https' !== wp_parse_url( $value, PHP_URL_SCHEME ) || ! wp_http_validate_url( $value ) ) { return new WP_Error( 'missing_model', __( 'The selected model is not configured.', 'youcam-ai-image-tools' ) ); }
		$tmp = wp_tempnam( 'ycait-model' );
		if ( ! $tmp ) { return new WP_Error( 'model_temp_file', __( 'Could not prepare the configured model image.', 'youcam-ai-image-tools' ) ); }
		$response = wp_safe_remote_get( $value, array( 'timeout' => 30, 'redirection' => 3, 'stream' => true, 'filename' => $tmp, 'limit_response_size' => 10 * 1024 * 1024 + 1 ) );
		$size = filesize( $tmp );
		$info = wp_getimagesize( $tmp );
		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) || false === $size || 0 === $size || $size >= 10 * 1024 * 1024 || ! $info || ! in_array( $info['mime'], array( 'image/jpeg', 'image/png', 'image/webp' ), true ) || $info[0] > 4096 || $info[1] > 4096 ) {
			@unlink( $tmp );
			return new WP_Error( 'invalid_model', __( 'The configured model image could not be prepared. Use a JPG or PNG smaller than 10 MB and no larger than 4096 pixels per side.', 'youcam-ai-image-tools' ) );
		}
		$file_id = $this->api->upload_local_image( $tmp, 'ycait-model-' . $gender, $info['mime'] );
		@unlink( $tmp );
		return $file_id;
	}

	/** Match selected product category terms to the same virtual try-on routes used on the storefront. */
	private function get_editor_product_type() {
		$raw_ids = $this->post_value( 'product_cat_ids' );
		$ids = array_filter( array_map( 'absint', preg_split( '/[^0-9]+/', $raw_ids ) ) );
		$terms = $ids ? get_terms( array( 'taxonomy' => 'product_cat', 'include' => $ids, 'hide_empty' => false ) ) : array();
		if ( ! is_array( $terms ) || is_wp_error( $terms ) ) { return 'clothes'; }
		$names = array();
		foreach ( $terms as $term ) { $names[] = strtolower( $term->name . ' ' . $term->slug ); }
		foreach ( array( 'hat' => '/\\b(hat|cap|headwear)\\b/', 'scarf' => '/\\b(scarf|scarves|scarfs)\\b/', 'bag' => '/\\b(bag|purse|handbag)\\b/' ) as $type => $pattern ) {
			foreach ( $names as $name ) { if ( preg_match( $pattern, $name ) ) { return $type; } }
		}
		return 'clothes';
	}

	public function run_task() {
		$this->check_request();
		$tool = $this->post_value( 'tool', '', 'sanitize_key' );
		$unit_costs = array( 'text-to-image' => 1, 'headshot' => 1, 'avatar' => 1, 'studio-generator' => 1, 'bg-replace' => 4, 'enhance' => 2, 'face-lift' => 1, 'smile' => 1, 'lighting' => 2, 'color-correction' => 2, 'bg-blur' => 1, 'face-swap' => 1, 'abs-filter' => 1, 'aging' => 1, 'body-reshape' => 1, 'breast-augmentation' => 1, 'face-reshape' => 1, 'image-extender' => 2, 'photo-colorize' => 2, 'watermark-removal' => 1, 'bangs-filter' => 1, 'beard-style' => 2, 'hair-color' => 1, 'hair-extension' => 1, 'hair-style' => 1, 'hair-volume' => 1, 'look-try-on' => 1, 'makeup-transfer' => 1, 'nail-transfer' => 1, 'teeth-whitening' => 1, 'wavy-hair' => 1, 'model-product' => 2 );
		if ( ! isset( $unit_costs[ $tool ] ) ) { wp_send_json_error( array( 'message' => __( 'Unknown tool.', 'youcam-ai-image-tools' ) ), 400 ); }
		if ( 'model-product' === $tool && ! current_user_can( 'upload_files' ) ) { wp_send_json_error( array( 'message' => __( 'You do not have permission to create product model images.', 'youcam-ai-image-tools' ) ), 403 ); }
		$output_count = 4;
		if ( in_array( $tool, array( 'avatar', 'studio-generator' ), true ) ) {
			$output_count = $this->post_uint( 'output_count', 4 );
			$allowed_counts = 'avatar' === $tool ? array( 4, 8, 12, 20 ) : array( 2, 4, 6, 8 );
			if ( ! in_array( $output_count, $allowed_counts, true ) ) { wp_send_json_error( array( 'message' => __( 'Choose a supported generated image count.', 'youcam-ai-image-tools' ) ), 400 ); }
			$unit_costs[ $tool ] = (int) ceil( $output_count / ( 'avatar' === $tool ? 4 : 2 ) );
		}
		$usage = $this->settings->reserve_tool_usage( $tool, $unit_costs[ $tool ] );
		if ( is_wp_error( $usage ) ) { wp_send_json_error( array( 'message' => $usage->get_error_message() ), 429 ); }
		$body = array();
		$endpoint = '';
		if ( 'text-to-image' === $tool ) {
			$prompt = $this->post_value( 'prompt', '', 'sanitize_textarea_field' );
			$prompt_length = function_exists( 'mb_strlen' ) ? mb_strlen( $prompt, 'UTF-8' ) : strlen( $prompt );
			if ( '' === $prompt || $prompt_length > 800 ) { wp_send_json_error( array( 'message' => __( 'Enter a prompt of up to 800 characters.', 'youcam-ai-image-tools' ) ), 400 ); }
			$endpoint = '/s2s/v2.0/task/text-to-image/youcam';
			$size = $this->post_value( 'size', '1664*928' );
			$sizes = array( '1664*928', '1472*1104', '1328*1328', '1104*1472', '928*1664' );
			$body = array( 'model' => 'youcam-image-v2', 'prompt' => $prompt, 'size' => in_array( $size, $sizes, true ) ? $size : '1664*928', 'prompt_extend' => true );
			$negative = $this->post_value( 'negative_prompt', '', 'sanitize_textarea_field' ); if ( $negative ) { $body['negative_prompt'] = function_exists( 'mb_substr' ) ? mb_substr( $negative, 0, 500, 'UTF-8' ) : substr( $negative, 0, 500 ); }
		} elseif ( 'headshot' === $tool ) {
			$file_id = $this->upload_image_field( 'photo' ); if ( is_wp_error( $file_id ) ) { wp_send_json_error( array( 'message' => $file_id->get_error_message() ), 400 ); }
			$template = $this->post_value( 'template_id' ); if ( '' === $template || 200 < strlen( $template ) ) { wp_send_json_error( array( 'message' => __( 'Enter a valid headshot template ID.', 'youcam-ai-image-tools' ) ), 400 ); }
			$endpoint = '/s2s/v2.0/task/headshot'; $body = array( 'src_file_id' => $file_id, 'template_id' => $template, 'output_count' => 2 );
		} elseif ( 'avatar' === $tool ) {
			$file_id = $this->upload_image_field( 'photo' ); if ( is_wp_error( $file_id ) ) { wp_send_json_error( array( 'message' => $file_id->get_error_message() ), 400 ); }
			$template = $this->post_value( 'template_id' ); if ( '' === $template || 200 < strlen( $template ) ) { wp_send_json_error( array( 'message' => __( 'Choose a valid avatar style.', 'youcam-ai-image-tools' ) ), 400 ); }
			$endpoint = '/s2s/v2.0/task/ai-avatar'; $body = array( 'src_file_id' => $file_id, 'template_id' => $template, 'output_count' => $output_count );
		} elseif ( 'studio-generator' === $tool ) {
			$file_id = $this->upload_image_field( 'photo', 1920 ); if ( is_wp_error( $file_id ) ) { wp_send_json_error( array( 'message' => $file_id->get_error_message() ), 400 ); }
			$template = $this->post_value( 'template_id' ); if ( '' === $template || 200 < strlen( $template ) ) { wp_send_json_error( array( 'message' => __( 'Choose a valid studio style.', 'youcam-ai-image-tools' ) ), 400 ); }
			$path = $this->image_field_path( 'photo' );
			$info = $path ? wp_getimagesize( $path ) : false;
			if ( ! $info || min( $info[0], $info[1] ) < 200 ) { wp_send_json_error( array( 'message' => __( 'Use a portrait photo with a short side of at least 200 pixels.', 'youcam-ai-image-tools' ) ), 400 ); }
			$endpoint = '/s2s/v2.0/task/ai-studio'; $body = array( 'src_file_id' => $file_id, 'template_id' => $template, 'output_count' => $output_count );
		} elseif ( 'model-product' === $tool ) {
			$image_id = $this->post_uint( 'product_image_id', 0 );
			if ( ! $image_id || ! wp_attachment_is_image( $image_id ) || ! current_user_can( 'edit_post', $image_id ) ) { wp_send_json_error( array( 'message' => __( 'Set a Product image before modeling this product.', 'youcam-ai-image-tools' ) ), 400 ); }
			$model_gender = $this->post_value( 'model_gender', '', 'sanitize_key' );
			if ( ! in_array( $model_gender, array( 'female', 'male' ), true ) || '' === (string) $this->settings->get( 'default_model_' . $model_gender, '' ) ) { wp_send_json_error( array( 'message' => __( 'Choose a configured model.', 'youcam-ai-image-tools' ) ), 400 ); }
			$product_file_id = $this->upload_attachment_input( $image_id, 4096, array( 'image/jpeg', 'image/png', 'image/webp' ) );
			if ( is_wp_error( $product_file_id ) ) { wp_send_json_error( array( 'message' => $product_file_id->get_error_message() ), 400 ); }
			$model_file_id = $this->upload_configured_model( $model_gender );
			if ( is_wp_error( $model_file_id ) ) { wp_send_json_error( array( 'message' => $model_file_id->get_error_message() ), 400 ); }
			$product_type = $this->get_editor_product_type();
			$result = $this->api->create_tryon_task( $model_file_id, $product_file_id, 'auto', $product_type, $model_gender );
			if ( is_wp_error( $result ) ) { wp_send_json_error( array( 'message' => $result->get_error_message() ), 500 ); }
			$task_id = $result['data']['task_id'] ?? $result['task_id'] ?? '';
			$task_id = is_scalar( $task_id ) ? sanitize_text_field( (string) $task_id ) : '';
			if ( ! $this->validate_task_id( $task_id ) ) { wp_send_json_error( array( 'message' => __( 'The API did not return a valid try-on task ID.', 'youcam-ai-image-tools' ) ), 500 ); }
			$this->store_task_owner( $task_id, $tool, 'final', $product_type );
			wp_send_json_success( array( 'task_id' => $task_id, 'tool' => $tool, 'stage' => 'final' ) );
		} elseif ( in_array( $tool, array( 'bangs-filter', 'beard-style', 'hair-color', 'hair-extension', 'hair-style', 'hair-volume', 'look-try-on', 'makeup-transfer', 'nail-transfer', 'teeth-whitening', 'wavy-hair' ), true ) ) {
			$attachment_source = $this->post_uint( 'attachment_id', 0 );
			$image_attachment_source = $this->post_uint( 'photo_attachment_id', 0 );
			$source_limit = in_array( $tool, array( 'beard-style', 'makeup-transfer' ), true ) ? 1023 : ( in_array( $tool, array( 'hair-color', 'teeth-whitening' ), true ) ? 1920 : 4096 );
			$source_id = $attachment_source ? $this->upload_attachment_input( $attachment_source, $source_limit ) : $this->upload_image_field( 'photo', $source_limit );
			if ( is_wp_error( $source_id ) ) { wp_send_json_error( array( 'message' => $source_id->get_error_message() ), 400 ); }
			$source_info_path = $attachment_source ? get_attached_file( $attachment_source ) : ( $image_attachment_source ? get_attached_file( $image_attachment_source ) : $this->image_field_path( 'photo' ) );
			$source_info = $source_info_path ? wp_getimagesize( $source_info_path ) : false;
			if ( 'beard-style' === $tool && ( ! $source_info || 'image/jpeg' !== $source_info['mime'] ) ) { wp_send_json_error( array( 'message' => __( 'Beard Style Generator accepts JPG images only.', 'youcam-ai-image-tools' ) ), 400 ); }
			$template_tools = array( 'bangs-filter' => 'hair-bang', 'beard-style' => 'beard-style', 'hair-extension' => 'hair-ext', 'hair-style' => 'hair-style', 'hair-volume' => 'hair-vol', 'look-try-on' => 'look-vto', 'wavy-hair' => 'hair-curl' );
			if ( isset( $template_tools[ $tool ] ) ) {
				$template = $this->post_value( 'template_id' );
				if ( '' === $template || 200 < strlen( $template ) ) { wp_send_json_error( array( 'message' => __( 'Choose a valid style.', 'youcam-ai-image-tools' ) ), 400 ); }
				$endpoint = '/s2s/v2.0/task/' . $template_tools[ $tool ];
				$body = array( 'src_file_id' => $source_id, 'template_id' => $template );
			} elseif ( 'hair-color' === $tool ) {
				if ( ! $source_info || max( $source_info[0], $source_info[1] ) >= 1920 || $source_info[1] >= 1080 || min( $source_info[0], $source_info[1] ) < 320 ) { wp_send_json_error( array( 'message' => __( 'Hair Color requires an image at least 320 pixels on each side and less than 1920 by 1080 pixels.', 'youcam-ai-image-tools' ) ), 400 ); }
				$presets = array( 'Jet Black', 'Chocolate Brown', 'Honey Blonde', 'Platinum Blonde', 'Ash Gray', 'Rose Gold', 'Burgundy', 'Copper Red', 'Lavender', 'Teal Blue', 'Dark Brown/Caramel Blonde', 'Jet Black/Silver Gray', 'Ash Brown/Lavender', 'Rose Gold/Peach Blonde', 'Burgundy/Magenta Pink', 'Deep Blue/Teal Green', 'Plum Purple/Pastel Lilac', 'Copper Red/Golden Blonde', 'Dark Gray/Ice Blonde', 'Midnight Blue/Denim Blue' );
				$preset = $this->post_value( 'preset' );
				if ( ! in_array( $preset, $presets, true ) ) { wp_send_json_error( array( 'message' => __( 'Choose a supported hair color preset.', 'youcam-ai-image-tools' ) ), 400 ); }
				$endpoint = '/s2s/v2.0/task/hair-color'; $body = array( 'src_file_id' => $source_id, 'preset' => $preset );
			} elseif ( in_array( $tool, array( 'makeup-transfer', 'nail-transfer' ), true ) ) {
				$reference_id = $this->upload_image_field( 'reference', 'nail-transfer' === $tool ? 4096 : 1024 );
				if ( is_wp_error( $reference_id ) ) { wp_send_json_error( array( 'message' => $reference_id->get_error_message() ), 400 ); }
				$endpoint = 'nail-transfer' === $tool ? '/s2s/v2.0/task/ai-nail' : '/s2s/v2.0/task/mu-transfer';
				$body = array( 'src_file_id' => $source_id, 'ref_file_id' => $reference_id );
			} else {
				if ( ! $source_info || min( $source_info[0], $source_info[1] ) < 320 ) { wp_send_json_error( array( 'message' => __( 'Teeth Whitening requires an image with both sides at least 320 pixels.', 'youcam-ai-image-tools' ) ), 400 ); }
				$endpoint = '/s2s/v2.0/task/teeth-whiten'; $body = array( 'src_file_id' => $source_id );
			}
		} elseif ( in_array( $tool, array( 'bg-replace', 'enhance', 'face-lift', 'face-swap', 'smile', 'lighting', 'color-correction', 'bg-blur', 'abs-filter', 'aging', 'body-reshape', 'breast-augmentation', 'face-reshape', 'image-extender', 'photo-colorize', 'watermark-removal' ), true ) ) {
			$attachment_source = $this->post_uint( 'attachment_id', 0 );
			$image_attachment_source = $this->post_uint( 'photo_attachment_id', 0 );
			$source_max_dimension = in_array( $tool, array( 'face-lift', 'image-extender' ), true ) ? ( 'face-lift' === $tool ? 1920 : 4096 ) : 4096;
			$source_id = $attachment_source ? $this->upload_attachment_input( $attachment_source, $source_max_dimension ) : $this->upload_image_field( 'photo', $source_max_dimension ); if ( is_wp_error( $source_id ) ) { wp_send_json_error( array( 'message' => $source_id->get_error_message() ), 400 ); }
			$source_path = $attachment_source ? get_attached_file( $attachment_source ) : ( $image_attachment_source ? get_attached_file( $image_attachment_source ) : $this->image_field_path( 'photo' ) );
			$source_info = $source_path ? wp_getimagesize( $source_path ) : false;
			if ( 'image-extender' === $tool && ( ! $source_info || 'image/jpeg' !== $source_info['mime'] ) ) { wp_send_json_error( array( 'message' => __( 'Image Extender accepts JPG images only.', 'youcam-ai-image-tools' ) ), 400 ); }
			if ( 'bg-replace' === $tool ) { $prompt = $this->post_value( 'prompt', '', 'sanitize_textarea_field' ); if ( '' === $prompt ) { wp_send_json_error( array( 'message' => __( 'Enter a background description.', 'youcam-ai-image-tools' ) ), 400 ); } $endpoint = '/s2s/v2.0/task/bg-replace'; $body = array( 'src_file_id' => $source_id, 'type' => 'prompt', 'prompt' => function_exists( 'mb_substr' ) ? mb_substr( $prompt, 0, 500, 'UTF-8' ) : substr( $prompt, 0, 500 ) ); }
			if ( 'enhance' === $tool ) { $endpoint = '/s2s/v2.0/task/enhance'; $scale = $this->post_uint( 'scale', 1 ); $body = array( 'src_file_id' => $source_id, 'scale' => in_array( $scale, array( 1, 2, 4 ), true ) ? $scale : 1 ); }
			if ( 'face-lift' === $tool ) {
				$features = array();
				foreach ( array( 'eye_bag', 'cheek', 'forehead', 'shape', 'mouth' ) as $feature ) { $raw_value = $this->post_value( 'feature_' . $feature, '0' ); if ( ! preg_match( '/^\d{1,3}$/', $raw_value ) || 100 < (int) $raw_value ) { wp_send_json_error( array( 'message' => __( 'Face Lift values must be whole numbers between 0 and 100.', 'youcam-ai-image-tools' ) ), 400 ); } $features[ $feature ] = (int) $raw_value; }
				if ( 0 === array_sum( $features ) ) { wp_send_json_error( array( 'message' => __( 'Set at least one Face Lift enhancement above 0.', 'youcam-ai-image-tools' ) ), 400 ); }
				$endpoint = '/s2s/v2.0/task/face-lift'; $body = array( 'src_file_id' => $source_id, 'type' => 'custom', 'features' => $features );
			}
			if ( 'smile' === $tool ) { $expression = $this->post_value( 'expression_type', '', 'sanitize_key' ); if ( ! in_array( $expression, array( 'smile_with_teeth_visible', 'closed_mouth_smile' ), true ) ) { $expression = 'smile_with_teeth_visible'; } $endpoint = '/s2s/v2.0/task/ai-smile'; $body = array( 'src_file_id' => $source_id, 'expression_type' => $expression ); }
			if ( 'lighting' === $tool ) { $endpoint = '/s2s/v2.0/task/lighting'; $body = array( 'src_file_id' => $source_id ); }
			if ( 'color-correction' === $tool ) { $endpoint = '/s2s/v2.0/task/colorize/color-correct'; $body = array( 'src_file_id' => $source_id ); }
			if ( 'bg-blur' === $tool ) { $intensity = $this->post_uint( 'intensity', 50 ); $endpoint = '/s2s/v2.0/task/bg-blur'; $body = array( 'src_file_id' => $source_id, 'intensity' => min( 100, $intensity ) ); }
			if ( 'photo-colorize' === $tool ) { $endpoint = '/s2s/v2.0/task/colorize'; $body = array( 'src_file_id' => $source_id ); }
			if ( 'watermark-removal' === $tool ) { $endpoint = '/s2s/v2.0/task/wmk-removal'; $body = array( 'src_file_id' => $source_id ); }
			if ( 'image-extender' === $tool ) {
				$output_width = $this->post_uint( 'output_width', 0 ); $output_height = $this->post_uint( 'output_height', 0 );
				$input_width = (int) $source_info[0]; $input_height = (int) $source_info[1];
				if ( ! $output_width || ! $output_height || $output_width > 4096 || $output_height > 4096 || $output_width < $input_width || $output_height < $input_height || ( $output_width === $input_width && $output_height === $input_height ) ) { wp_send_json_error( array( 'message' => __( 'Output dimensions must fit the source image and expand at least one side, up to 4096 pixels.', 'youcam-ai-image-tools' ) ), 400 ); }
				$input_x = (int) floor( ( $output_width - $input_width ) / 2 ); $input_y = (int) floor( ( $output_height - $input_height ) / 2 );
				$endpoint = '/s2s/v2.0/task/out-paint';
				$body = array( 'src_file_id' => $source_id, 'output_width' => $output_width, 'output_height' => $output_height, 'input_x' => $input_x, 'input_y' => $input_y, 'input_width' => $input_width, 'input_height' => $input_height, 'crop_input_x' => 0, 'crop_input_y' => 0, 'crop_input_width' => $input_width, 'crop_input_height' => $input_height );
			}
			if ( 'face-swap' === $tool ) {
				$reference_id = $this->upload_image_field( 'reference' );
				if ( is_wp_error( $reference_id ) ) { wp_send_json_error( array( 'message' => $reference_id->get_error_message() ), 400 ); }
				$endpoint = '/s2s/v2.0/task/face-swap/pre-process';
				$body = array( 'src_file_id' => $source_id );
				$result = $this->api->create_tool_task( $endpoint, $body );
				if ( is_wp_error( $result ) ) { wp_send_json_error( array( 'message' => $result->get_error_message() ), 500 ); }
				$task_id = $result['data']['task_id'] ?? $result['task_id'] ?? '';
				$task_id = is_scalar( $task_id ) ? sanitize_text_field( (string) $task_id ) : '';
				if ( ! $this->validate_task_id( $task_id ) ) { wp_send_json_error( array( 'message' => __( 'The API did not return a valid face detection task ID.', 'youcam-ai-image-tools' ) ), 500 ); }
				$this->store_task_owner( $task_id, $tool, 'preprocess' );
				set_transient( 'ycait_face_swap_' . md5( $task_id ), array( 'source_id' => $source_id, 'reference_id' => $reference_id ), 15 * MINUTE_IN_SECONDS );
				wp_send_json_success( array( 'task_id' => sanitize_text_field( $task_id ), 'tool' => $tool, 'stage' => 'preprocess' ) );
			}
			if ( 'abs-filter' === $tool ) { $mode = $this->post_value( 'mode', 'Six-pack' ); if ( ! in_array( $mode, array( 'Six-pack', 'Vest-line' ), true ) ) { $mode = 'Six-pack'; } $intensity = $this->post_uint( 'intensity', 1 ); $endpoint = '/s2s/v2.0/task/abs-shape'; $body = array( 'src_file_id' => $source_id, 'mode' => $mode, 'intensity' => in_array( $intensity, array( 1, 2 ), true ) ? $intensity : 1 ); }
			if ( 'aging' === $tool ) { $endpoint = '/s2s/v2.0/task/aging'; $body = array( 'src_file_id' => $source_id ); }
			if ( 'breast-augmentation' === $tool ) { $intensity = $this->post_uint( 'intensity', 2 ); $endpoint = '/s2s/v2.0/task/breast-shape'; $body = array( 'src_file_id' => $source_id, 'intensity' => in_array( $intensity, array( 1, 2, 3 ), true ) ? $intensity : 2 ); }
			if ( in_array( $tool, array( 'body-reshape', 'face-reshape' ), true ) ) {
				$feature = $this->post_value( 'feature', '', 'sanitize_key' );
				$amount_raw = $this->post_value( 'amount', '' );
				$allowed = 'body-reshape' === $tool ? array( 'arm', 'breast', 'hip', 'hip_lift', 'leg', 'neck', 'shoulder', 'slim', 'taller', 'waist', 'belly' ) : array( 'cheekbones', 'jaw', 'face_reshape', 'face_width', 'chin_reshape', 'chin_length', 'eye_size', 'eye_distance', 'eye_angle', 'eye_height', 'eye_width', 'nose_bridge_width', 'nose_lift', 'nose_size', 'nose_tip', 'nose_tip_width', 'nose_wing', 'lip_size', 'lip_width', 'lip_peak', 'lip_height_top', 'lip_height_bottom' );
				$minimum = 'face-reshape' === $tool && in_array( $feature, array( 'cheekbones', 'jaw' ), true ) ? 0 : ( 'body-reshape' === $tool && in_array( $feature, array( 'neck', 'taller' ), true ) ? 0 : -100 );
				if ( ! in_array( $feature, $allowed, true ) || ! preg_match( '/^-?\d{1,3}$/', $amount_raw ) || (int) $amount_raw < $minimum || (int) $amount_raw > 100 || 0 === (int) $amount_raw ) { wp_send_json_error( array( 'message' => __( 'Choose a valid non-zero adjustment within the supported range.', 'youcam-ai-image-tools' ) ), 400 ); }
				$endpoint = 'body-reshape' === $tool ? '/s2s/v2.0/task/body-reshape/pre-process' : '/s2s/v2.0/task/face-reshape/pre-process';
				$body = array( 'src_file_id' => $source_id );
				$chain_id = wp_generate_password( 32, false, false );
				set_transient( 'ycait_effect_chain_' . hash( 'sha256', $chain_id ), array( 'tool' => $tool, 'source_id' => $source_id, 'feature' => $feature, 'amount' => (int) $amount_raw ), 15 * MINUTE_IN_SECONDS );
			}
		} else { wp_send_json_error( array( 'message' => __( 'Unknown tool.', 'youcam-ai-image-tools' ) ), 400 ); }
		$result = $this->api->create_tool_task( $endpoint, $body );
		if ( is_wp_error( $result ) ) { wp_send_json_error( array( 'message' => $result->get_error_message() ), 500 ); }
		$task_id = $result['data']['task_id'] ?? $result['task_id'] ?? '';
		$task_id = is_scalar( $task_id ) ? sanitize_text_field( (string) $task_id ) : '';
		if ( ! $this->validate_task_id( $task_id ) ) { wp_send_json_error( array( 'message' => __( 'The API did not return a valid task ID.', 'youcam-ai-image-tools' ) ), 500 ); }
		$stage = in_array( $tool, array( 'body-reshape', 'face-reshape' ), true ) ? 'preprocess' : 'final';
		$this->store_task_owner( $task_id, $tool, $stage );
		if ( 'preprocess' === $stage ) { set_transient( 'ycait_effect_task_' . hash( 'sha256', $task_id ), get_transient( 'ycait_effect_chain_' . hash( 'sha256', $chain_id ) ), 15 * MINUTE_IN_SECONDS ); delete_transient( 'ycait_effect_chain_' . hash( 'sha256', $chain_id ) ); }
		wp_send_json_success( array( 'task_id' => sanitize_text_field( $task_id ), 'tool' => $tool, 'stage' => $stage ) );
	}

	public function poll_task() {
		$this->check_request();
		$tool = $this->post_value( 'tool', '', 'sanitize_key' );
		$id = $this->post_value( 'task_id' );
		$paths = array( 'text-to-image' => 'text-to-image/youcam', 'headshot' => 'headshot', 'avatar' => 'ai-avatar', 'studio-generator' => 'ai-studio', 'bg-replace' => 'bg-replace', 'enhance' => 'enhance', 'face-lift' => 'face-lift', 'face-swap' => 'face-swap', 'smile' => 'ai-smile', 'lighting' => 'lighting', 'color-correction' => 'colorize/color-correct', 'bg-blur' => 'bg-blur', 'abs-filter' => 'abs-shape', 'aging' => 'aging', 'body-reshape' => 'body-reshape', 'breast-augmentation' => 'breast-shape', 'face-reshape' => 'face-reshape', 'image-extender' => 'out-paint', 'photo-colorize' => 'colorize', 'watermark-removal' => 'wmk-removal', 'bangs-filter' => 'hair-bang', 'beard-style' => 'beard-style', 'hair-color' => 'hair-color', 'hair-extension' => 'hair-ext', 'hair-style' => 'hair-style', 'hair-volume' => 'hair-vol', 'look-try-on' => 'look-vto', 'makeup-transfer' => 'mu-transfer', 'nail-transfer' => 'ai-nail', 'teeth-whitening' => 'teeth-whiten', 'wavy-hair' => 'hair-curl', 'model-product' => 'cloth-v4' );
		if ( ! isset( $paths[ $tool ] ) || ! $this->validate_task_id( $id ) ) { wp_send_json_error( array( 'message' => __( 'Invalid task request.', 'youcam-ai-image-tools' ) ), 400 ); }
		$stage = $this->post_value( 'stage', '', 'sanitize_key' );
		$owner_key = $this->task_owner_key( $id );
		$owner = get_transient( $owner_key );
		if ( ! is_array( $owner ) || empty( $owner['requester'] ) || ! is_string( $owner['requester'] ) || ! hash_equals( $this->requester_key(), $owner['requester'] ) || ( $owner['tool'] ?? '' ) !== $tool || ( $owner['stage'] ?? '' ) !== ( 'preprocess' === $stage ? 'preprocess' : 'final' ) ) {
			wp_send_json_error( array( 'message' => __( 'This task is unavailable. Start a new task and try again.', 'youcam-ai-image-tools' ) ), 403 );
		}
		if ( ! $this->reserve_poll_slot( $id ) ) { wp_send_json_error( array( 'message' => __( 'Please wait before checking this task again.', 'youcam-ai-image-tools' ) ), 429 ); }
		$is_preprocess = 'preprocess' === $stage;
		$preprocess_tools = array( 'face-swap' => 'face-swap/pre-process', 'body-reshape' => 'body-reshape/pre-process', 'face-reshape' => 'face-reshape/pre-process' );
		$endpoint_path = $is_preprocess && isset( $preprocess_tools[ $tool ] ) ? $preprocess_tools[ $tool ] : $paths[ $tool ];
		if ( 'model-product' === $tool ) {
			$model_paths = array( 'clothes' => 'cloth-v4', 'hat' => 'hat', 'scarf' => 'scarf', 'bag' => 'bag' );
			$endpoint_path = $model_paths[ $owner['product_type'] ?? 'clothes' ] ?? 'cloth-v4';
		}
		$endpoint = '/s2s/v2.0/task/' . $endpoint_path . '/' . rawurlencode( $id );
		$result = $this->api->get_tool_task( $endpoint );
		if ( is_wp_error( $result ) ) { wp_send_json_error( array( 'message' => $result->get_error_message() ), 500 ); }
		if ( isset( $result['data'] ) && ! is_array( $result['data'] ) ) { wp_send_json_error( array( 'message' => __( 'The API returned an invalid task response.', 'youcam-ai-image-tools' ) ), 500 ); }
		if ( 'face-swap' === $tool && 'preprocess' === $stage ) {
			$task = $result['data'] ?? $result;
			if ( ! is_array( $task ) ) { wp_send_json_error( array( 'message' => __( 'The API returned an invalid task response.', 'youcam-ai-image-tools' ) ), 500 ); }
			if ( 'success' === strtolower( $task['status'] ?? '' ) ) {
				$files = get_transient( 'ycait_face_swap_' . md5( $id ) );
				if ( empty( $files['source_id'] ) || empty( $files['reference_id'] ) ) { wp_send_json_error( array( 'message' => __( 'Face swap session expired. Please submit the images again.', 'youcam-ai-image-tools' ) ), 400 ); }
				$swap = $this->api->create_tool_task( '/s2s/v2.0/task/face-swap', array( 'src_file_id' => $files['source_id'], 'ref_file_ids' => array( $files['reference_id'] ), 'face_mapping' => array( array( 'index' => 0, 'position' => 0 ) ) ) );
				delete_transient( 'ycait_face_swap_' . md5( $id ) );
				if ( is_wp_error( $swap ) ) { wp_send_json_error( array( 'message' => $swap->get_error_message() ), 500 ); }
				$swap_id = $swap['data']['task_id'] ?? $swap['task_id'] ?? '';
				$swap_id = is_scalar( $swap_id ) ? sanitize_text_field( (string) $swap_id ) : '';
				if ( ! $this->validate_task_id( $swap_id ) ) { wp_send_json_error( array( 'message' => __( 'The API did not return a valid face swap task ID.', 'youcam-ai-image-tools' ) ), 500 ); }
				$this->store_task_owner( $swap_id, $tool, 'final' );
				delete_transient( $owner_key );
				wp_send_json_success( array( 'result' => array( 'status' => 'running' ), 'next_task_id' => sanitize_text_field( $swap_id ), 'stage' => 'final' ) );
			}
		}
		$task = $result['data'] ?? $result;
		$status = strtolower( $task['task_status'] ?? $task['status'] ?? '' );
		if ( $is_preprocess && in_array( $tool, array( 'body-reshape', 'face-reshape' ), true ) && 'success' === $status ) {
			$chain_key = 'ycait_effect_task_' . hash( 'sha256', $id );
			$chain = get_transient( $chain_key );
			if ( ! is_array( $chain ) || ( $chain['tool'] ?? '' ) !== $tool ) { wp_send_json_error( array( 'message' => __( 'The effect session expired. Please submit the image again.', 'youcam-ai-image-tools' ) ), 400 ); }
			$features = array( $chain['feature'] => (int) $chain['amount'] );
			$final_body = array( 'src_file_id' => $chain['source_id'], 'version' => '1.0', 'index' => 0, 'features' => $features );
			if ( 'face-reshape' === $tool ) { $final_body['global'] = array( 'skin_smooth_strength' => 0, 'skin_smooth_color_intensity' => 0 ); }
			$final_path = 'body-reshape' === $tool ? 'body-reshape' : 'face-reshape';
			$final = $this->api->create_tool_task( '/s2s/v2.0/task/' . $final_path, $final_body );
			delete_transient( $chain_key );
			if ( is_wp_error( $final ) ) { wp_send_json_error( array( 'message' => $final->get_error_message() ), 500 ); }
			$final_id = $final['data']['task_id'] ?? $final['task_id'] ?? '';
			$final_id = is_scalar( $final_id ) ? sanitize_text_field( (string) $final_id ) : '';
			if ( ! $this->validate_task_id( $final_id ) ) { wp_send_json_error( array( 'message' => __( 'The API did not return a valid effect task ID.', 'youcam-ai-image-tools' ) ), 500 ); }
			$this->store_task_owner( $final_id, $tool );
			delete_transient( $owner_key );
			wp_send_json_success( array( 'next_task_id' => $final_id, 'stage' => 'final' ) );
		}
		$result_authorizations = array();
		if ( 'success' === $status ) {
			$urls = array();
			$this->grant_result_urls( $task['results'] ?? $task, $urls );
			foreach ( array_keys( $urls ) as $result_url ) { $this->grant_result_url( $result_url ); }
			$approved_urls = array_values( array_filter( array_map( array( $this, 'normalize_result_url' ), array_keys( $urls ) ) ) );
			if ( $approved_urls ) {
				set_transient( $this->task_result_key( $id ), array( 'requester' => $this->requester_key(), 'urls' => $approved_urls ), 15 * MINUTE_IN_SECONDS );
			}
			$expires = time() + 15 * MINUTE_IN_SECONDS;
			foreach ( array_keys( $urls ) as $result_url ) {
				$normalized_url = $this->normalize_result_url( $result_url );
				if ( $normalized_url ) {
					$result_authorizations[] = array( 'url' => $result_url, 'token' => $this->result_authorization_token( $id, $normalized_url, $expires ), 'expires' => $expires );
				}
			}
		}
		if ( in_array( $status, array( 'success', 'error', 'failed' ), true ) ) { delete_transient( $owner_key ); }
		wp_send_json_success( array( 'result' => $result, 'task_id' => $id, 'result_authorizations' => $result_authorizations ) );
	}

	public function save_result() {
		$this->check_request();
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to add images to the Media Library.', 'youcam-ai-image-tools' ) ), 403 );
		}
		$url = $this->normalize_result_url( $this->post_url_value( 'url' ) );
		if ( ! $url || 'https' !== wp_parse_url( $url, PHP_URL_SCHEME ) || ! wp_http_validate_url( $url ) ) {
			wp_send_json_error( array( 'message' => __( 'The result image URL is invalid.', 'youcam-ai-image-tools' ) ), 400 );
		}
		$task_id = $this->post_value( 'task_id' );

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$tmp_file = wp_tempnam( 'ycait-result' );
		if ( ! $tmp_file ) {
			wp_send_json_error( array( 'message' => __( 'WordPress could not prepare a temporary image file.', 'youcam-ai-image-tools' ) ), 500 );
		}
		$download = $this->download_result_image( $url, $tmp_file );
		if ( is_wp_error( $download ) ) {
			@unlink( $tmp_file );
			wp_send_json_error( array( 'message' => $download->get_error_message() ), 500 );
		}

		$file_size = filesize( $tmp_file );
		$image_info = wp_getimagesize( $tmp_file );
		if ( false === $file_size || $file_size > 10 * 1024 * 1024 || ! $image_info || ! in_array( $image_info['mime'], array( 'image/jpeg', 'image/png', 'image/webp' ), true ) || $image_info[0] > 4096 || $image_info[1] > 4096 ) {
			@unlink( $tmp_file );
			wp_send_json_error( array( 'message' => __( 'The result must be a JPG, PNG, or WebP image smaller than 10 MB.', 'youcam-ai-image-tools' ) ), 400 );
		}

		$extension = 'image/png' === $image_info['mime'] ? 'png' : ( 'image/webp' === $image_info['mime'] ? 'webp' : 'jpg' );
		$file_array = array( 'name' => 'youcam-result-' . gmdate( 'Ymd-His' ) . '.' . $extension, 'tmp_name' => $tmp_file );
		$attachment_id = media_handle_sideload( $file_array, 0, __( 'Generated or enhanced with YouCam AI Image Tools', 'youcam-ai-image-tools' ) );
		if ( is_wp_error( $attachment_id ) ) {
			@unlink( $tmp_file );
			wp_send_json_error( array( 'message' => $attachment_id->get_error_message() ), 500 );
		}

		wp_send_json_success( array(
			'attachment_id' => $attachment_id,
			'edit_url'      => get_edit_post_link( $attachment_id, 'raw' ),
		) );
	}

	/** Render the generation controls inside the featured/product image media modal iframe. */
	public function render_media_generator() {
		check_ajax_referer( 'ycait_media_generator', 'nonce' );
		if ( ! current_user_can( 'upload_files' ) || ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to generate images here.', 'youcam-ai-image-tools' ), '', array( 'response' => 403 ) );
		}
		$config = array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'ycait_tools' ), 'mediaEmbed' => true,
			'fieldDescriptions' => $this->get_field_descriptions(),
			'polling' => __( 'Working…', 'youcam-ai-image-tools' ), 'saving' => __( 'Saving…', 'youcam-ai-image-tools' ),
			'saved' => __( 'Saved to Media Library', 'youcam-ai-image-tools' ), 'save' => __( 'Save to Media Library', 'youcam-ai-image-tools' ),
			'error' => __( 'The request could not be completed.', 'youcam-ai-image-tools' ), 'close' => __( 'Close', 'youcam-ai-image-tools' ),
			'resultTitle' => __( 'Your result', 'youcam-ai-image-tools' ), 'resultReady' => __( 'Your image is ready.', 'youcam-ai-image-tools' ),
			'resultMissing' => __( 'The task finished, but the API response did not include a result image URL.', 'youcam-ai-image-tools' ),
			'openImage' => __( 'Open full-size image', 'youcam-ai-image-tools' ), 'viewAttachment' => __( 'View attachment', 'youcam-ai-image-tools' ),
			'timeout' => __( 'Processing is taking longer than expected. Check again later.', 'youcam-ai-image-tools' ),
			'dragDrop' => __( 'Drag & drop your photo here, or click to browse', 'youcam-ai-image-tools' ), 'fileHelp' => __( 'JPG or PNG, smaller than 10 MB', 'youcam-ai-image-tools' ),
			'invalidFile' => __( 'Use a JPG or PNG image.', 'youcam-ai-image-tools' ), 'largeFile' => __( 'Image files must be smaller than 10 MB.', 'youcam-ai-image-tools' ),
			'previewAlt' => __( 'Selected image preview', 'youcam-ai-image-tools' ), 'mediaSelectMessage' => __( 'Image selected in the media library.', 'youcam-ai-image-tools' ),
		);
		header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
	?>
	<!doctype html><html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="stylesheet" href="<?php echo esc_url( YOUCAM_AI_IMAGE_TOOLS_URL . 'admin/css/youcam-ai-image-tools-admin.css?ver=' . YOUCAM_AI_IMAGE_TOOLS_VERSION ); ?>"><link rel="stylesheet" href="<?php echo esc_url( admin_url( 'css/common.min.css' ) ); ?>"><style>html,body{width:100%;height:100%;min-height:100%;margin:0;background:#fff}body{box-sizing:border-box;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;text-align:center}.ycait-media-intro{max-width:460px;margin:0 0 18px;text-align:center}.ycait-media-actions{display:flex;justify-content:center;width:100%}.ycait-media-actions .ycait-tool-launch{min-height:46px;padding:0 36px;border:1px solid #2271b1;border-radius:3px;background:#fff;color:#2271b1;font-size:14px;line-height:2.8;box-shadow:none;cursor:pointer}.ycait-media-actions .ycait-tool-launch:hover,.ycait-media-actions .ycait-tool-launch:focus{border-color:#2271b1;background:#f0f0f1;color:#135e96;box-shadow:none}.ycait-tools-modal{position:fixed;z-index:100000;inset:0}.ycait-tools-modal-backdrop{background:#fff}.ycait-tools-dialog{width:min(620px,100%)}</style></head><body>
	<p class="ycait-media-intro"><?php esc_html_e( 'Create an image and save it directly to the Media Library.', 'youcam-ai-image-tools' ); ?></p>
	<div class="ycait-media-actions"><button type="button" class="button ycait-tool-launch" data-tool="text-to-image" data-title="<?php esc_attr_e( 'Generate Image', 'youcam-ai-image-tools' ); ?>"><?php esc_html_e( 'Generate Image', 'youcam-ai-image-tools' ); ?></button></div>
	<div class="ycait-tool-templates" hidden><template data-tool-template="text-to-image"><form class="ycait-tool-form" data-tool="text-to-image"><label><?php esc_html_e( 'Prompt', 'youcam-ai-image-tools' ); ?><textarea name="prompt" maxlength="800" required></textarea></label><label><?php esc_html_e( 'Negative prompt (optional)', 'youcam-ai-image-tools' ); ?><textarea name="negative_prompt" maxlength="500"></textarea></label><label><?php esc_html_e( 'Image size', 'youcam-ai-image-tools' ); ?><select name="size"><option value="1664*928">Landscape</option><option value="1472*1104">Wide landscape</option><option value="1328*1328">Square</option><option value="1104*1472">Portrait</option><option value="928*1664">Tall portrait</option></select></label><button type="submit" class="button button-primary"><?php esc_html_e( 'Generate image', 'youcam-ai-image-tools' ); ?></button></form></template></div><div class="ycait-tools-modal" hidden><div class="ycait-tools-modal-backdrop"><section class="ycait-tools-dialog" role="dialog" aria-modal="true" aria-labelledby="ycait-tools-dialog-title" tabindex="-1"><header class="ycait-tools-dialog-header"><h2 id="ycait-tools-dialog-title"></h2><button type="button" class="ycait-tools-dialog-close" aria-label="<?php esc_attr_e( 'Close', 'youcam-ai-image-tools' ); ?>">&times;</button></header><div class="ycait-tools-dialog-content"></div></section></div></div>
	<script>window.ycaitTools=<?php echo wp_json_encode( $config ); ?>;</script><script src="<?php echo esc_url( YOUCAM_AI_IMAGE_TOOLS_URL . 'admin/js/youcam-ai-image-tools-tools.js?ver=' . YOUCAM_AI_IMAGE_TOOLS_VERSION ); ?>"></script></body></html>
	<?php
		wp_die();
	}

	/** Render the model try-on controls inside the product gallery media picker. */
	public function render_media_model_product() {
		check_ajax_referer( 'ycait_media_model_product', 'nonce' );
		if ( ! current_user_can( 'edit_posts' ) || ! current_user_can( 'upload_files' ) ) {
			wp_die( esc_html__( 'You do not have permission to model product images.', 'youcam-ai-image-tools' ), '', array( 'response' => 403 ) );
		}
		$models = array();
		foreach ( array( 'female', 'male' ) as $gender ) {
			$value = $this->settings->get( 'default_model_' . $gender, '' );
			$url = $this->settings->get_model_url( 'default_model_' . $gender );
			if ( $value && $url ) {
				$models[] = array( 'gender' => $gender, 'name' => $this->settings->get( 'model_name_' . $gender, ucfirst( $gender ) . ' Model' ), 'url' => $url );
			}
		}
		$config = array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'ycait_tools' ),
			'models' => $models, 'missingImage' => __( 'Set a Product image before modeling this product.', 'youcam-ai-image-tools' ),
			'missingModel' => __( 'Configure at least one model under WooCommerce → Settings → Virtual Try On.', 'youcam-ai-image-tools' ),
			'generate' => __( 'Generate Image', 'youcam-ai-image-tools' ),
			'save' => __( 'Save to Media Library', 'youcam-ai-image-tools' ), 'saving' => __( 'Saving…', 'youcam-ai-image-tools' ),
			'saved' => __( 'Saved to Media Library', 'youcam-ai-image-tools' ), 'addGallery' => __( 'The saved image is selected. Use Add to gallery to add it.', 'youcam-ai-image-tools' ),
			'polling' => __( 'Creating the model image…', 'youcam-ai-image-tools' ), 'error' => __( 'The request could not be completed.', 'youcam-ai-image-tools' ),
			'mediaSelectMessage' => __( 'The saved image is selected for your product gallery.', 'youcam-ai-image-tools' ),
		);
		header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		?>
		<!doctype html><html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="stylesheet" href="<?php echo esc_url( YOUCAM_AI_IMAGE_TOOLS_URL . 'admin/css/youcam-ai-image-tools-admin.css?ver=' . YOUCAM_AI_IMAGE_TOOLS_VERSION ); ?>"><link rel="stylesheet" href="<?php echo esc_url( admin_url( 'css/common.min.css' ) ); ?>"><style>
			html,body{margin:0;min-height:100%;background:#fff}body{box-sizing:border-box;padding:22px;color:#1d2327;font:13px/1.5 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.ycait-model-product{max-width:760px;margin:0 auto;text-align:center}.ycait-model-product h2{margin:0 0 8px;font-size:28px;font-weight:500;line-height:1.5;text-align:center}.ycait-model-product p{margin:0 0 16px}.ycait-model-choices{display:flex;justify-content:center;gap:18px;flex-wrap:wrap;margin:0 0 16px}.ycait-model-choice{box-sizing:border-box;width:260px;min-height:260px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;padding:18px;border:1px solid #c3c4c7;border-radius:4px;cursor:pointer}.ycait-model-choice img{display:block;width:204px;height:204px;max-width:100%;object-fit:cover}.ycait-model-choice input{margin:0}.ycait-model-reference{margin:0 0 10px!important}.ycait-model-message{margin:8px 0;color:#646970}.ycait-model-status{min-height:20px;margin:8px 0;color:#646970}.ycait-model-actions{display:flex;justify-content:center}.ycait-model-button{min-height:46px;padding:0 36px;border:1px solid #2271b1!important;border-radius:3px!important;background:#fff!important;color:#2271b1!important;font-size:14px!important;line-height:2.8!important;box-shadow:none!important;cursor:pointer}.ycait-model-button:hover,.ycait-model-button:focus{border-color:#2271b1!important;background:#f0f0f1!important;color:#135e96!important;box-shadow:none!important}.ycait-model-result{margin-top:18px}.ycait-model-result img{display:block;max-width:100%;max-height:55vh;margin:0 auto 12px}.ycait-model-result-actions{display:flex;flex-direction:column;align-items:center;gap:8px}.ycait-model-product .button:disabled{opacity:.55;cursor:not-allowed}
		</style></head><body><main class="ycait-model-product"><h2><?php esc_html_e( 'Create Image with Model', 'youcam-ai-image-tools' ); ?></h2><p><?php esc_html_e( 'Choose a configured model.', 'youcam-ai-image-tools' ); ?></p><div id="ycait-model-choices" class="ycait-model-choices"></div><p class="ycait-model-reference"><?php esc_html_e( 'The current Product image will be used as the product reference.', 'youcam-ai-image-tools' ); ?></p><div id="ycait-model-notice" class="ycait-model-message" aria-live="polite"></div><div id="ycait-model-status" class="ycait-model-status" aria-live="polite"></div><div class="ycait-model-actions"><button type="button" class="button ycait-model-button" id="ycait-model-generate"><?php esc_html_e( 'Generate Image', 'youcam-ai-image-tools' ); ?></button></div><div id="ycait-model-result" class="ycait-model-result" aria-live="polite"></div></main><script>window.ycaitModelProduct=<?php echo wp_json_encode( $config ); ?>;</script><script src="<?php echo esc_url( YOUCAM_AI_IMAGE_TOOLS_URL . 'admin/js/youcam-ai-image-tools-model-product.js?ver=' . YOUCAM_AI_IMAGE_TOOLS_VERSION ); ?>"></script></body></html>
		<?php
		wp_die();
	}

	/** Serve the selected attachment's enhancement modal in a same-origin iframe. */
	public function render_attachment_enhancement() {
		$attachment_id = isset( $_GET['attachment_id'] ) ? absint( $_GET['attachment_id'] ) : 0;
		$tool = isset( $_GET['tool'] ) ? sanitize_key( wp_unslash( $_GET['tool'] ) ) : '';
		check_ajax_referer( 'ycait_attachment_enhancement', 'nonce' );
		$tools = array( 'bg-replace', 'enhance', 'smile', 'lighting', 'color-correction', 'bg-blur', 'face-swap', 'abs-filter', 'aging', 'body-reshape', 'breast-augmentation', 'face-reshape', 'face-lift', 'image-extender', 'photo-colorize', 'watermark-removal', 'bangs-filter', 'beard-style', 'hair-color', 'hair-extension', 'hair-style', 'hair-volume', 'look-try-on', 'makeup-transfer', 'nail-transfer', 'teeth-whitening', 'wavy-hair' );
		if ( ! current_user_can( 'edit_posts' ) || ! current_user_can( 'upload_files' ) || ! wp_attachment_is_image( $attachment_id ) || ! current_user_can( 'edit_post', $attachment_id ) || ! in_array( $tool, $tools, true ) ) {
			wp_die( esc_html__( 'You do not have permission to enhance this image.', 'youcam-ai-image-tools' ), '', array( 'response' => 403 ) );
		}
		$attachment_path = get_attached_file( $attachment_id );
		$attachment_dimensions = $attachment_path ? wp_getimagesize( $attachment_path ) : false;
		$config = array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'ycait_tools' ),
			'attachmentEnhance' => true, 'attachmentId' => $attachment_id, 'initialTool' => $tool,
			'sourceDimensions' => $attachment_dimensions ? array( (int) $attachment_dimensions[0], (int) $attachment_dimensions[1] ) : array(),
			'polling' => __( 'Working…', 'youcam-ai-image-tools' ), 'saving' => __( 'Saving Enhancement…', 'youcam-ai-image-tools' ),
			'saveEnhancement' => __( 'Save Enhancement', 'youcam-ai-image-tools' ), 'discardEnhancement' => __( 'Discard Enhancement', 'youcam-ai-image-tools' ),
			'saveError' => __( 'Could not save the enhancement. The attachment was left unchanged.', 'youcam-ai-image-tools' ),
			'error' => __( 'The request could not be completed.', 'youcam-ai-image-tools' ), 'close' => __( 'Close', 'youcam-ai-image-tools' ),
			'resultTitle' => __( 'Enhancement preview', 'youcam-ai-image-tools' ), 'resultReady' => __( 'Review the result, then save it or discard it.', 'youcam-ai-image-tools' ),
			'resultMissing' => __( 'The task finished, but the API response did not include a result image URL.', 'youcam-ai-image-tools' ),
			'openImage' => __( 'Open full-size image', 'youcam-ai-image-tools' ), 'timeout' => __( 'Processing is taking longer than expected. Check again later.', 'youcam-ai-image-tools' ),
			'dragDrop' => __( 'Drag & drop your photo here, or click to browse', 'youcam-ai-image-tools' ), 'fileHelp' => __( 'JPG or PNG, smaller than 10 MB', 'youcam-ai-image-tools' ),
			'invalidFile' => __( 'Use a JPG or PNG image.', 'youcam-ai-image-tools' ), 'largeFile' => __( 'Image files must be smaller than 10 MB.', 'youcam-ai-image-tools' ),
			'previewAlt' => __( 'Selected image preview', 'youcam-ai-image-tools' ), 'fieldDescriptions' => $this->get_field_descriptions(),
		);
		header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
	?>
	<!doctype html><html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="stylesheet" href="<?php echo esc_url( YOUCAM_AI_IMAGE_TOOLS_URL . 'admin/css/youcam-ai-image-tools-admin.css?ver=' . YOUCAM_AI_IMAGE_TOOLS_VERSION ); ?>"><link rel="stylesheet" href="<?php echo esc_url( admin_url( 'css/common.min.css' ) ); ?>"><style>body{margin:0;padding:0;background:#fff}.wrap{margin:0;padding:12px}.wrap>h1,.wrap>p,.ycait-tool-grid{display:none!important}.ycait-tools-modal{position:fixed;z-index:100000;inset:0}.ycait-tools-modal-backdrop{padding:10px}.ycait-tools-dialog{width:min(700px,100%);max-height:98%}.ycait-tool-result-actions{flex-wrap:wrap}.ycait-enhancement-discard{background:#fff!important;color:#b32d2e!important;border:1px solid #b32d2e!important}</style></head><body>
	<?php if ( in_array( $tool, array( 'abs-filter', 'aging', 'body-reshape', 'breast-augmentation', 'face-reshape', 'face-lift', 'smile' ), true ) ) { $this->render_face_body(); } elseif ( in_array( $tool, array( 'bangs-filter', 'beard-style', 'hair-color', 'hair-extension', 'hair-style', 'hair-volume', 'look-try-on', 'makeup-transfer', 'nail-transfer', 'teeth-whitening', 'wavy-hair' ), true ) ) { $this->render_beauty(); } else { $this->render_enhancement(); } ?>
	<script>window.ycaitTools=<?php echo wp_json_encode( $config ); ?>;</script><script src="<?php echo esc_url( YOUCAM_AI_IMAGE_TOOLS_URL . 'admin/js/youcam-ai-image-tools-tools.js?ver=' . YOUCAM_AI_IMAGE_TOOLS_VERSION ); ?>"></script></body></html>
	<?php
		wp_die();
	}

	/** Replace the attachment's current image with a generated result, preserving a restorable original. */
	public function save_enhancement() {
		$this->check_request();
		if ( ! current_user_can( 'upload_files' ) ) { wp_send_json_error( array( 'message' => __( 'You do not have permission to edit Media Library images.', 'youcam-ai-image-tools' ) ), 403 ); }
		$attachment_id = $this->post_uint( 'attachment_id', 0 );
		if ( ! wp_attachment_is_image( $attachment_id ) || ! current_user_can( 'edit_post', $attachment_id ) ) { wp_send_json_error( array( 'message' => __( 'You cannot edit this image attachment.', 'youcam-ai-image-tools' ) ), 403 ); }
		$url = $this->normalize_result_url( $this->post_url_value( 'url' ) );
		if ( ! $url || 'https' !== wp_parse_url( $url, PHP_URL_SCHEME ) || ! wp_http_validate_url( $url ) ) { wp_send_json_error( array( 'message' => __( 'The result image URL is invalid.', 'youcam-ai-image-tools' ) ), 400 ); }
		$task_id = $this->post_value( 'task_id' );

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$tmp_file = wp_tempnam( 'ycait-enhancement' );
		if ( ! $tmp_file ) { wp_send_json_error( array( 'message' => __( 'WordPress could not prepare a temporary image file.', 'youcam-ai-image-tools' ) ), 500 ); }
		$download = $this->download_result_image( $url, $tmp_file );
		if ( is_wp_error( $download ) ) { @unlink( $tmp_file ); wp_send_json_error( array( 'message' => $download->get_error_message() ), 500 ); }
		$file_size = filesize( $tmp_file );
		$image_info = wp_getimagesize( $tmp_file );
		if ( false === $file_size || 0 === $file_size || $file_size > 10 * 1024 * 1024 || ! $image_info || ! in_array( $image_info['mime'], array( 'image/jpeg', 'image/png', 'image/webp' ), true ) || $image_info[0] > 4096 || $image_info[1] > 4096 ) {
			@unlink( $tmp_file );
			wp_send_json_error( array( 'message' => __( 'The enhancement must be a JPG, PNG, or WebP image smaller than 10 MB.', 'youcam-ai-image-tools' ) ), 400 );
		}
		$old_path = get_attached_file( $attachment_id );
		$old_meta = wp_get_attachment_metadata( $attachment_id );
		$old_mime = get_post_mime_type( $attachment_id );
		$old_backup = get_post_meta( $attachment_id, '_wp_attachment_backup_sizes', true );
		if ( ! $old_path || ! is_file( $old_path ) || ! is_array( $old_meta ) ) { @unlink( $tmp_file ); wp_send_json_error( array( 'message' => __( 'The original attachment file or metadata is unavailable.', 'youcam-ai-image-tools' ) ), 500 ); }
		$directory = dirname( $old_path );
		$extension = 'image/png' === $image_info['mime'] ? 'png' : ( 'image/webp' === $image_info['mime'] ? 'webp' : 'jpg' );
		$base_name = pathinfo( $old_path, PATHINFO_FILENAME ) . '-ai-' . gmdate( 'Ymd-His' ) . '.' . $extension;
		$new_path = trailingslashit( $directory ) . wp_unique_filename( $directory, $base_name );
		if ( ! copy( $tmp_file, $new_path ) ) { @unlink( $tmp_file ); wp_send_json_error( array( 'message' => __( 'WordPress could not store the enhancement beside the attachment.', 'youcam-ai-image-tools' ) ), 500 ); }
		@unlink( $tmp_file );

		$backup_sizes = is_array( $old_backup ) ? $old_backup : array();
		if ( empty( $backup_sizes['full-orig'] ) ) {
			$backup_sizes['full-orig'] = array( 'file' => basename( $old_path ), 'width' => absint( $old_meta['width'] ?? 0 ), 'height' => absint( $old_meta['height'] ?? 0 ), 'filesize' => absint( $old_meta['filesize'] ?? filesize( $old_path ) ) );
		}
		foreach ( ( $old_meta['sizes'] ?? array() ) as $size_name => $size_data ) {
			$backup_key = $size_name . '-orig';
			if ( ! isset( $backup_sizes[ $backup_key ] ) && is_array( $size_data ) ) { $backup_sizes[ $backup_key ] = $size_data; }
		}
		$new_meta = wp_generate_attachment_metadata( $attachment_id, $new_path );
		if ( ! is_array( $new_meta ) || empty( $new_meta['width'] ) || empty( $new_meta['height'] ) ) { wp_delete_file( $new_path ); wp_send_json_error( array( 'message' => __( 'WordPress could not create image sizes for the enhancement.', 'youcam-ai-image-tools' ) ), 500 ); }
		$new_meta['filesize'] = filesize( $new_path );
		if ( ! update_attached_file( $attachment_id, $new_path ) ) { wp_delete_file( $new_path ); wp_send_json_error( array( 'message' => __( 'WordPress could not update the attachment file.', 'youcam-ai-image-tools' ) ), 500 ); }
		$mime_update = wp_update_post( array( 'ID' => $attachment_id, 'post_mime_type' => $image_info['mime' ] ), true );
		$meta_saved = wp_update_attachment_metadata( $attachment_id, $new_meta );
		update_post_meta( $attachment_id, '_wp_attachment_backup_sizes', $backup_sizes );
		$backup_saved = get_post_meta( $attachment_id, '_wp_attachment_backup_sizes', true ) === $backup_sizes;
		if ( is_wp_error( $mime_update ) || ! $meta_saved || ! $backup_saved ) {
			update_attached_file( $attachment_id, $old_path );
			wp_update_post( array( 'ID' => $attachment_id, 'post_mime_type' => $old_mime ) );
			wp_update_attachment_metadata( $attachment_id, $old_meta );
			if ( is_array( $old_backup ) ) { update_post_meta( $attachment_id, '_wp_attachment_backup_sizes', $old_backup ); } else { delete_post_meta( $attachment_id, '_wp_attachment_backup_sizes' ); }
			wp_delete_file( $new_path );
			foreach ( ( $new_meta['sizes'] ?? array() ) as $generated_size ) { if ( ! empty( $generated_size['file'] ) ) { wp_delete_file( trailingslashit( $directory ) . wp_basename( $generated_size['file'] ) ); } }
			wp_send_json_error( array( 'message' => __( 'WordPress could not update the attachment metadata. The original image has been restored.', 'youcam-ai-image-tools' ) ), 500 );
		}
		wp_send_json_success( array( 'attachment_id' => $attachment_id, 'url' => wp_get_attachment_url( $attachment_id ) ) );
	}
}
