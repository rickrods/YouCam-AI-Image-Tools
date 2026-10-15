<?php
/**
 * AJAX request handlers.
 *
 * Handles three AJAX actions:
 *  - ycait_upload : Upload a user photo via the File API
 *  - ycait_tryon  : Create a try-on task and return a task_id
 *  - ycait_poll   : Poll the task status and return result URL when done
 *
 * @package Youcam_Ai_Image_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and handles AJAX endpoints for the try-on flow.
 */
class YCAIT_Ajax {

	/**
	 * API client instance.
	 *
	 * @var YCAIT_API_Client
	 */
	protected $api;

	/**
	 * Settings instance.
	 *
	 * @var YCAIT_Settings
	 */
	protected $settings;

	/**
	 * Constructor.
	 *
	 * @param YCAIT_API_Client $api      API client.
	 * @param YCAIT_Settings   $settings Settings.
	 */
	public function __construct( YCAIT_API_Client $api, YCAIT_Settings $settings ) {
		$this->api      = $api;
		$this->settings = $settings;

		add_action( 'wp_ajax_ycait_upload', array( $this, 'handle_upload' ) );
		add_action( 'wp_ajax_nopriv_ycait_upload', array( $this, 'handle_upload' ) );

		add_action( 'wp_ajax_ycait_tryon', array( $this, 'handle_tryon' ) );
		add_action( 'wp_ajax_nopriv_ycait_tryon', array( $this, 'handle_tryon' ) );

		add_action( 'wp_ajax_ycait_poll', array( $this, 'handle_poll' ) );
		add_action( 'wp_ajax_nopriv_ycait_poll', array( $this, 'handle_poll' ) );
	}

	/**
	 * Verify the AJAX nonce.
	 */
	protected function verify_nonce() {
		if ( ! isset( $_POST['nonce'] ) || ! is_string( $_POST['nonce'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh the page and try again.', 'youcam-ai-image-tools' ) ), 403 );
		}
		if ( ! check_ajax_referer( 'ycait_nonce', 'nonce', false ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Security check failed. Please refresh the page and try again.', 'youcam-ai-image-tools' ),
				),
				403
			);
		}
	}

	/** Read a scalar AJAX field safely before applying the field sanitizer. */
	protected function post_value( $key, $default = '', $sanitizer = 'sanitize_text_field' ) {
		if ( ! isset( $_POST[ $key ] ) || ! is_scalar( $_POST[ $key ] ) ) { return $default; }
		return call_user_func( $sanitizer, wp_unslash( (string) $_POST[ $key ] ) );
	}

	protected function post_uint( $key, $default ) {
		$value = $this->post_value( $key, (string) $default );
		return preg_match( '/^\d+$/', $value ) ? absint( $value ) : $default;
	}

	/** Atomically allow at most ten guest image uploads per requester and hour. */
	protected function reserve_upload_slot() {
		global $wpdb;
		$key = 'ycait_upload_rate_' . current_time( 'YmdH' ) . '_' . substr( $this->requester_key(), 0, 32 );
		$sql = $wpdb->prepare(
			"INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, '1', 'no') ON DUPLICATE KEY UPDATE option_value = IF(CAST(option_value AS UNSIGNED) < 10, CAST(option_value AS UNSIGNED) + 1, option_value)",
			$key
		);
		$affected = $wpdb->query( $sql );
		wp_cache_delete( $key, 'options' );
		wp_cache_delete( 'notoptions', 'options' );
		return false === $affected ? new WP_Error( 'ycait_upload_rate_store', __( 'Upload limits could not be checked. Please try again later.', 'youcam-ai-image-tools' ) ) : ( 0 === $affected ? new WP_Error( 'ycait_upload_rate_limit', __( 'Too many image uploads. Please try again later.', 'youcam-ai-image-tools' ) ) : true );
	}

	/**
	 * Upload a local file to the File API.
	 *
	 * @param string $file_path    Local filesystem path.
	 * @param string $file_name    Original file name.
	 * @param string $mime_type    MIME type.
	 *
	 * @return array|WP_Error
	 */
	protected function upload_local_file_to_api( $file_path, $file_name, $mime_type ) {
		if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
			return new WP_Error(
				'file_not_readable',
				__( 'The image file could not be read.', 'youcam-ai-image-tools' )
			);
		}

		$file_size = filesize( $file_path );

		if ( false === $file_size ) {
			return new WP_Error(
				'file_size_unavailable',
				__( 'Could not determine the image file size.', 'youcam-ai-image-tools' )
			);
		}

		$upload_info = $this->api->get_upload_url(
			$mime_type,
			sanitize_file_name( $file_name ),
			$file_size
		);

		if ( is_wp_error( $upload_info ) ) {
			return $upload_info;
		}

		$upload_result = $this->api->upload_file(
			$upload_info['upload_url'],
			$file_path,
			$mime_type,
			$upload_info['headers']
		);

		if ( is_wp_error( $upload_result ) ) {
			return $upload_result;
		}

		return array(
			'file_id'  => $upload_info['file_id'],
			'file_url' => $this->api->get_file_url( $upload_info['file_id'] ),
		);
	}

	/** Validate an image file's actual format, size, and dimensions. */
	protected function get_valid_image_info( $file_path ) {
		if ( ! is_file( $file_path ) || ! is_readable( $file_path ) ) {
			return new WP_Error( 'invalid_image', __( 'The image file could not be read.', 'youcam-ai-image-tools' ) );
		}
		$size = filesize( $file_path );
		$image = wp_getimagesize( $file_path );
		$allowed = array( 'image/jpeg', 'image/png', 'image/webp' );
		if ( false === $size || 0 === $size || $size > 10 * 1024 * 1024 || ! $image || empty( $image['mime'] ) || ! in_array( $image['mime'], $allowed, true ) || empty( $image[0] ) || empty( $image[1] ) || $image[0] > 4096 || $image[1] > 4096 ) {
			return new WP_Error( 'invalid_image', __( 'Use a JPG, PNG, or WebP image smaller than 10 MB with dimensions no larger than 4096 pixels per side.', 'youcam-ai-image-tools' ) );
		}
		$extensions = array( 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp' );
		return array( 'size' => $size, 'mime_type' => $image['mime'], 'extension' => $extensions[ $image['mime'] ], 'width' => (int) $image[0], 'height' => (int) $image[1] );
	}

	/** Return an identity key for binding temporary upload tickets to their requester. */
	protected function requester_key() {
		$identity = is_user_logged_in() ? 'user:' . get_current_user_id() : 'ip:' . sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
		return hash_hmac( 'sha256', $identity, wp_salt( 'auth' ) );
	}

	/**
	 * Download a remote image to a temporary local file.
	 *
	 * @param string $url Remote image URL.
	 *
	 * @return array|WP_Error
	 */
	protected function download_remote_image( $url ) {
		if ( empty( $url ) ) {
			return new WP_Error( 'missing_image_url', __( 'Missing image URL.', 'youcam-ai-image-tools' ) );
		}
		$local_path = $this->url_to_local_path( $url );
		if ( $local_path ) {
			$source_path = $local_path;
			$temporary = false;
		} else {
			if ( ! wp_http_validate_url( $url ) || 'https' !== wp_parse_url( $url, PHP_URL_SCHEME ) ) {
				return new WP_Error( 'invalid_image_url', __( 'The configured model image URL is invalid.', 'youcam-ai-image-tools' ) );
			}
			$source_path = wp_tempnam( 'ycait-image' );
			if ( ! $source_path ) {
				return new WP_Error( 'temp_file_failed', __( 'Could not create a temporary image file.', 'youcam-ai-image-tools' ) );
			}
			$temporary = true;
			$response = wp_safe_remote_get( $url, array(
				'timeout'             => 30,
				'redirection'         => 3,
				'stream'              => true,
				'filename'            => $source_path,
				'limit_response_size' => 10 * 1024 * 1024 + 1,
			) );
			if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
				@unlink( $source_path );
				return new WP_Error( 'image_download_failed', __( 'The configured model image could not be downloaded.', 'youcam-ai-image-tools' ) );
			}
		}

		$image_info = $this->get_valid_image_info( $source_path );
		if ( is_wp_error( $image_info ) ) {
			if ( $temporary ) { @unlink( $source_path ); }
			return $image_info;
		}
		if ( $temporary ) {
			$extension = 'image/png' === $image_info['mime_type'] ? 'png' : ( 'image/webp' === $image_info['mime_type'] ? 'webp' : 'jpg' );
			$renamed_path = $source_path . '.' . $extension;
			if ( ! rename( $source_path, $renamed_path ) ) {
				@unlink( $source_path );
				return new WP_Error( 'temp_file_failed', __( 'Could not prepare the downloaded image.', 'youcam-ai-image-tools' ) );
			}
			$source_path = $renamed_path;
		}
		return array( 'path' => $source_path, 'mime_type' => $image_info['mime_type'], 'extension' => 'image/png' === $image_info['mime_type'] ? 'png' : ( 'image/webp' === $image_info['mime_type'] ? 'webp' : 'jpg' ), 'temporary' => $temporary );
	}

	/**
	 * Convert a local site URL to a filesystem path.
	 *
	 * @param string $url URL to convert.
	 * @return string|false Local path or false if not local.
	 */
	protected function url_to_local_path( $url ) {
		$requested = wp_parse_url( $url );
		if ( ! is_array( $requested ) || empty( $requested['host'] ) || empty( $requested['path'] ) || isset( $requested['user'] ) || isset( $requested['pass'] ) ) { return false; }
		$upload_dir = wp_upload_dir();
		$roots = array(
			array( 'url' => site_url(), 'path' => ABSPATH ),
			array( 'url' => $upload_dir['baseurl'], 'path' => $upload_dir['basedir'] ),
		);
		foreach ( $roots as $root ) {
			$base = wp_parse_url( $root['url'] );
			if ( ! is_array( $base ) || empty( $base['host'] ) ) { continue; }
			$base_scheme = strtolower( $base['scheme'] ?? 'https' );
			$request_scheme = strtolower( $requested['scheme'] ?? 'https' );
			$base_origin = $base_scheme . '://' . strtolower( $base['host'] ) . ':' . (int) ( $base['port'] ?? ( 'https' === $base_scheme ? 443 : 80 ) );
			$request_origin = $request_scheme . '://' . strtolower( $requested['host'] ) . ':' . (int) ( $requested['port'] ?? ( 'https' === $request_scheme ? 443 : 80 ) );
			if ( $base_origin !== $request_origin ) { continue; }
			$base_path = rtrim( rawurldecode( $base['path'] ?? '' ), '/' );
			$url_path = rawurldecode( $requested['path'] );
			if ( '' !== $base_path && $url_path !== $base_path && 0 !== strpos( $url_path, $base_path . '/' ) ) { continue; }
			$relative = '' === $base_path ? ltrim( $url_path, '/' ) : ltrim( substr( $url_path, strlen( $base_path ) ), '/' );
			$segments = explode( '/', $relative );
			$unsafe = false;
			foreach ( $segments as $segment ) {
				if ( '' === $segment || '.' === $segment || '..' === $segment || false !== strpos( $segment, '\\' ) || false !== strpos( $segment, "\0" ) || false !== strpos( $segment, '%' ) ) { $unsafe = true; break; }
			}
			if ( $unsafe ) { continue; }
			$root_path = realpath( $root['path'] );
			$candidate = realpath( trailingslashit( $root['path'] ) . implode( '/', $segments ) );
			if ( ! $root_path || ! $candidate || ! is_file( $candidate ) || 0 !== strpos( wp_normalize_path( $candidate ), trailingslashit( wp_normalize_path( $root_path ) ) ) ) { continue; }
			return $candidate;
		}
		return false;
	}

	/**
	 * Download a remote image and upload it to the File API.
	 *
	 * @param string $url       Remote image URL.
	 * @param string $file_name File name.
	 *
	 * @return array|WP_Error
	 */
	protected function import_remote_image_to_api( $url, $file_name ) {

		$download = $this->download_remote_image( $url );

		if ( is_wp_error( $download ) ) {
			return $download;
		}

		$result = $this->upload_local_file_to_api(
			$download['path'],
			$file_name . '.' . $download['extension'],
			$download['mime_type']
		);

		if ( ! empty( $download['temporary'] ) ) { @unlink( $download['path'] ); }

		return $result;
	}

	/**
	 * AJAX: Upload a user photo to the File API.
	 *
	 * Expects:
	 * $_FILES['photo']
	 *
	 * Returns:
	 * {
	 *     upload_token
	 * }
	 */
	public function handle_upload() {
		$this->verify_nonce();
		$access_error = $this->settings->access_error();
		if ( $access_error ) {
			wp_send_json_error( array( 'message' => $access_error ), 403 );
		}

		if ( empty( $_FILES['photo'] ) || ! is_array( $_FILES['photo'] ) || empty( $_FILES['photo']['tmp_name'] ) || ! is_string( $_FILES['photo']['tmp_name'] ) || ! isset( $_FILES['photo']['error'] ) || ! is_scalar( $_FILES['photo']['error'] ) || UPLOAD_ERR_OK !== (int) $_FILES['photo']['error'] || ! is_uploaded_file( $_FILES['photo']['tmp_name'] ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'No photo was uploaded.', 'youcam-ai-image-tools' ),
				),
				400
			);
		}

		$file = $_FILES['photo'];

		if ( ! is_scalar( $file['name'] ?? null ) || false === filesize( $file['tmp_name'] ) || filesize( $file['tmp_name'] ) > 10 * 1024 * 1024 ) {
			wp_send_json_error(
				array(
					'message' => __( 'The image is too large. Maximum size is 10 MB.', 'youcam-ai-image-tools' ),
				),
				400
			);
		}
		$image_info = $this->get_valid_image_info( $file['tmp_name'] );
		if ( is_wp_error( $image_info ) ) {
			wp_send_json_error(
				array( 'message' => $image_info->get_error_message() ),
				400
			);
		}
		$mime_type = $image_info['mime_type'];
		$upload_slot = $this->reserve_upload_slot();
		if ( is_wp_error( $upload_slot ) ) {
			wp_send_json_error( array( 'message' => $upload_slot->get_error_message() ), 'ycait_upload_rate_limit' === $upload_slot->get_error_code() ? 429 : 500 );
		}

		$upload_result = $this->upload_local_file_to_api(
			$file['tmp_name'],
			'ycait-person.' . $image_info['extension'],
			$mime_type
		);

		if ( is_wp_error( $upload_result ) ) {
			wp_send_json_error(
				array(
					'message' => $upload_result->get_error_message(),
				),
				500
			);
		}

		$upload_token = wp_generate_password( 64, false, false );
		set_transient( 'ycait_upload_' . hash( 'sha256', $upload_token ), array( 'file_id' => $upload_result['file_id'], 'requester' => $this->requester_key() ), 15 * MINUTE_IN_SECONDS );
		wp_send_json_success( array( 'upload_token' => $upload_token ) );
	}

	/** Resolve the API from the product's WooCommerce categories. */
	protected function get_product_api_type( $product_id ) {
		if ( ! $product_id || ! function_exists( 'wc_get_product' ) || ! wc_get_product( $product_id ) ) {
			return 'clothes';
		}
		$terms = get_the_terms( $product_id, 'product_cat' );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return 'clothes';
		}
		$labels = '';
		foreach ( $terms as $term ) {
			$labels .= ' ' . strtolower( $term->slug . ' ' . $term->name );
		}
		if ( preg_match( '/(^|[^a-z])(hat|hats|cap|caps|headwear)([^a-z]|$)/', $labels ) ) {
			return 'hat';
		}
		if ( preg_match( '/(^|[^a-z])(scarf|scarves|scarfs)([^a-z]|$)/', $labels ) ) {
			return 'scarf';
		}
		if ( preg_match( '/(^|[^a-z])(bag|bags|purse|purses|handbag|handbags)([^a-z]|$)/', $labels ) ) {
			return 'bag';
		}
		return 'clothes';
	}

	/** Upload the featured image from a published WooCommerce product. */
	protected function get_product_image_file_id( $product_id ) {
		if ( ! function_exists( 'wc_get_product' ) ) { return new WP_Error( 'missing_product', __( 'WooCommerce product was not found.', 'youcam-ai-image-tools' ) ); }
		$product = wc_get_product( $product_id );
		if ( ! $product || 'publish' !== $product->get_status() ) { return new WP_Error( 'invalid_product', __( 'The selected product is unavailable.', 'youcam-ai-image-tools' ) ); }
		$image_id = absint( $product->get_image_id() );
		$path = $image_id && wp_attachment_is_image( $image_id ) ? get_attached_file( $image_id ) : '';
		$image_info = $path ? $this->get_valid_image_info( $path ) : new WP_Error( 'missing_product_image', __( 'The product does not have a usable image.', 'youcam-ai-image-tools' ) );
		if ( is_wp_error( $image_info ) ) { return $image_info; }
		return $this->upload_local_file_to_api( $path, 'ycait-product.' . $image_info['extension'], $image_info['mime_type'] );
	}

	/** Resolve a model image from the administrator-configured source. */
	protected function get_configured_model_file_id( $gender ) {
		$key = 'default_model_' . $gender;
		$value = $this->settings->get( $key, '' );
		if ( is_numeric( $value ) ) {
			$attachment_id = absint( $value );
			$path = wp_attachment_is_image( $attachment_id ) ? get_attached_file( $attachment_id ) : '';
			$image_info = $path ? $this->get_valid_image_info( $path ) : new WP_Error( 'invalid_model', __( 'The configured model image is unavailable.', 'youcam-ai-image-tools' ) );
			if ( is_wp_error( $image_info ) ) { return $image_info; }
			return $this->upload_local_file_to_api( $path, 'ycait-model-' . $gender . '.' . $image_info['extension'], $image_info['mime_type'] );
		}
		if ( is_string( $value ) && '' !== $value ) {
			$model = $this->import_remote_image_to_api( $value, 'ycait-model-' . $gender );
			return is_wp_error( $model ) ? $model : $model['file_id'];
		}
		return new WP_Error( 'missing_model', __( 'The selected default model is not configured.', 'youcam-ai-image-tools' ) );
	}

	/**
	 * AJAX: Create a try-on task.
	 *
	 * The handler resolves product and model images from trusted server-side
	 * sources and accepts user images only through a short-lived upload ticket.
	 */
	public function handle_tryon() {
		$this->verify_nonce();

		$access_error = $this->settings->access_error();
		if ( $access_error ) {
			wp_send_json_error( array( 'message' => $access_error ), 403 );
		}

		$upload_token = $this->post_value( 'upload_token' );
		$source = $this->post_value( 'source', '', 'sanitize_key' );
		$model_gender = $this->post_value( 'model_gender', '', 'sanitize_key' );

		$garment = $this->post_value( 'garment_category', 'auto' );
		$product_id = $this->post_uint( 'product_id', 0 );
		$product_type = $this->get_product_api_type( $product_id );
		$gender = $this->post_value( 'gender', 'female', 'sanitize_key' );

		$valid_categories = array(
			'auto',
			'full_body',
			'upper_body',
			'lower_body',
			'shoes',
			'outer',
		);

		if ( ! in_array( $garment, $valid_categories, true ) ) {
			$garment = 'auto';
		}

		if ( ! in_array( $source, array( 'upload', 'model' ), true ) ) { wp_send_json_error( array( 'message' => __( 'Choose a valid person image source.', 'youcam-ai-image-tools' ) ), 400 ); }
		if ( 'model' === $source && ! in_array( $model_gender, array( 'female', 'male' ), true ) ) { wp_send_json_error( array( 'message' => __( 'Choose a configured model.', 'youcam-ai-image-tools' ) ), 400 ); }
		if ( ! in_array( $gender, array( 'female', 'male' ), true ) ) { $gender = 'female'; }

		$upload_key = '';
		if ( 'upload' === $source ) {
			if ( ! preg_match( '/^[a-zA-Z0-9]{64}$/', $upload_token ) ) { wp_send_json_error( array( 'message' => __( 'Please upload your photo again.', 'youcam-ai-image-tools' ) ), 400 ); }
			$upload_key = 'ycait_upload_' . hash( 'sha256', $upload_token );
			$uploaded = get_transient( $upload_key );
			if ( empty( $uploaded['file_id'] ) || empty( $uploaded['requester'] ) || ! is_string( $uploaded['requester'] ) || ! hash_equals( $this->requester_key(), $uploaded['requester'] ) ) { wp_send_json_error( array( 'message' => __( 'Your uploaded photo expired. Please upload it again.', 'youcam-ai-image-tools' ) ), 403 ); }
			$src_file_id = sanitize_text_field( $uploaded['file_id'] );
		}
		if ( ! function_exists( 'wc_get_product' ) ) { wp_send_json_error( array( 'message' => __( 'WooCommerce product was not found.', 'youcam-ai-image-tools' ) ), 400 ); }
		$product = wc_get_product( $product_id );
		if ( ! $product || 'publish' !== $product->get_status() || ! wp_attachment_is_image( absint( $product->get_image_id() ) ) ) { wp_send_json_error( array( 'message' => __( 'The selected product image is unavailable.', 'youcam-ai-image-tools' ) ), 400 ); }
		$product_image_path = get_attached_file( absint( $product->get_image_id() ) );
		$product_image_info = $product_image_path ? $this->get_valid_image_info( $product_image_path ) : new WP_Error( 'invalid_product_image', __( 'The selected product image is unavailable.', 'youcam-ai-image-tools' ) );
		if ( is_wp_error( $product_image_info ) ) { wp_send_json_error( array( 'message' => $product_image_info->get_error_message() ), 400 ); }
		if ( 'model' === $source && '' === (string) $this->settings->get( 'default_model_' . $model_gender, '' ) ) { wp_send_json_error( array( 'message' => __( 'The selected default model is not configured.', 'youcam-ai-image-tools' ) ), 400 ); }

		// Reserve usage atomically before external File API/task requests.
		$usage = $this->settings->reserve_usage();
		if ( is_wp_error( $usage ) ) { wp_send_json_error( array( 'message' => $usage->get_error_message() ), 403 ); }
		if ( $upload_key ) { delete_transient( $upload_key ); }
		if ( 'model' === $source ) {
			$src_file_id = $this->get_configured_model_file_id( $model_gender );
			if ( is_wp_error( $src_file_id ) ) { wp_send_json_error( array( 'message' => __( 'The selected model could not be prepared.', 'youcam-ai-image-tools' ) ), 400 ); }
		}
		$ref_file_id = $this->get_product_image_file_id( $product_id );
		if ( is_wp_error( $ref_file_id ) ) { wp_send_json_error( array( 'message' => $ref_file_id->get_error_message() ), 400 ); }

		/*
		 * At this point BOTH images are File API IDs.
		 *
		 * The API client sends only these validated IDs:
		 *
		 * src_file_id
		 * ref_file_id
		 */
		$result = $this->api->create_tryon_task(
			$src_file_id,
			$ref_file_id,
			$garment,
			$product_type,
			$gender
		);

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'message' => $result->get_error_message(),
				),
				500
			);
		}

		$task_id = $result['data']['task_id'] ?? '';
		$task_id = is_scalar( $task_id ) ? sanitize_text_field( (string) $task_id ) : '';

		if ( empty( $task_id ) || 200 < strlen( $task_id ) || ! preg_match( '/^[A-Za-z0-9_-]+$/', $task_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'No task ID was returned by the API.', 'youcam-ai-image-tools' ),
				),
				500
			);
		}
		set_transient(
			$this->task_owner_key( $task_id ),
			array(
				'requester'   => $this->requester_key(),
				'product_type' => $product_type,
			),
			DAY_IN_SECONDS
		);

		wp_send_json_success(
			array(
				'task_id' => $task_id,
				'product_type' => $product_type,
			)
		);
	}

	/**
	 * AJAX: Poll a task for its status.
	 */
	public function handle_poll() {
		$this->verify_nonce();

		$task_id = $this->post_value( 'task_id' );
		if ( empty( $task_id ) || 200 < strlen( $task_id ) || ! preg_match( '/^[A-Za-z0-9_-]+$/', $task_id ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Missing task ID.', 'youcam-ai-image-tools' ),
				),
				400
			);
		}
		$owner_key = $this->task_owner_key( $task_id );
		$task_owner = get_transient( $owner_key );
		if ( empty( $task_owner['requester'] ) || ! is_string( $task_owner['requester'] ) || ! hash_equals( $task_owner['requester'], $this->requester_key() ) ) {
			wp_send_json_error( array( 'message' => __( 'This task is unavailable. Please start a new try-on.', 'youcam-ai-image-tools' ) ), 403 );
		}
		$product_type = $task_owner['product_type'] ?? 'clothes';
		if ( ! $this->reserve_poll_slot( $task_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Please wait a moment before checking this task again.', 'youcam-ai-image-tools' ) ), 429 );
		}

		$result = $this->api->get_task_status( $task_id, $product_type );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'message' => $result->get_error_message(),
				),
				500
			);
		}

		$status = $result['data']['task_status'] ?? 'processing';
		if ( in_array( $status, array( 'success', 'error' ), true ) ) {
			delete_transient( $owner_key );
		}

		$response = array(
			'status' => $status,
		);

		if ( 'success' === $status ) {
			$response['result_url'] = $result['data']['results']['url'] ?? '';
		}

		if ( 'error' === $status ) {
			$response['message'] = __( 'The AI processing failed. Please try again.', 'youcam-ai-image-tools' );
		}

		wp_send_json_success( $response );
	}

	/** Build an opaque transient key without storing the API task ID in the key. */
	protected function task_owner_key( $task_id ) {
		return 'ycait_task_owner_' . hash( 'sha256', (string) $task_id );
	}

	/** Atomically allow no more than one status request per task every two seconds. */
	protected function reserve_poll_slot( $task_id ) {
		global $wpdb;
		$key = 'ycait_poll_' . hash( 'sha256', (string) $task_id );
		$now = time();
		$cutoff = $now - 2;
		$sql = $wpdb->prepare(
			"INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %d, 'no') ON DUPLICATE KEY UPDATE option_value = IF(CAST(option_value AS UNSIGNED) <= %d, %d, option_value)",
			$key,
			$now,
			$cutoff,
			$now
		);
		$affected = $wpdb->query( $sql );
		if ( false === $affected ) {
			return false;
		}
		wp_cache_delete( $key, 'options' );
		wp_cache_delete( 'notoptions', 'options' );
		return 0 !== $affected;
	}
}
