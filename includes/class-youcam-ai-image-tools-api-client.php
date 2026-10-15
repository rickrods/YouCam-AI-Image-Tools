<?php
/**
 * Virtual AI Clothes Try-On API client.
 *
 * Handles communication with the PerfectCorp API:
 *  - File API: obtain a pre-signed upload URL + file_id
 *  - File upload: PUT the image to the pre-signed URL
 *  - Try-on task: POST to /s2s/v2.0/task/cloth-v4
 *  - Task polling: GET /s2s/v2.0/task/cloth-v4/{task_id}
 *
 * @package Youcam_Ai_Image_Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API client for AI Clothes Virtual Try-On.
 */
class YCAIT_API_Client {

	/**
	 * Default API base URL.
	 *
	 * @var string
	 */
	const DEFAULT_BASE_URL = 'https://yce-api-01.makeupar.com';

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
	}

	/**
	 * Get the configured API base URL without a trailing slash.
	 *
	 * @return string
	 */
	protected function get_base_url() {
		$url = $this->settings->get( 'api_base_url', self::DEFAULT_BASE_URL );
		$parts = wp_parse_url( $url );
		if ( ! is_array( $parts ) || 'https' !== strtolower( $parts['scheme'] ?? '' ) || empty( $parts['host'] ) || isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['query'] ) || isset( $parts['fragment'] ) || ! wp_http_validate_url( $url ) ) {
			$url = self::DEFAULT_BASE_URL;
		}
		return untrailingslashit( $url );
	}

	/**
	 * Get the Bearer API key.
	 *
	 * @return string
	 */
	protected function get_api_key() {
		return $this->settings->get( 'api_key', '' );
	}

	/**
	 * Determine whether the API client is configured.
	 *
	 * @return bool
	 */
	public function is_configured() {
		return ! empty( $this->get_api_key() );
	}

	/**
	 * Step 1: Request a pre-signed upload URL from the File API.
	 *
	 * @param string $content_type MIME type of the file.
	 * @param string $file_name    Original file name.
	 * @param int    $file_size    File size in bytes.
	 * @return array|WP_Error Associative array containing file information.
	 */
	public function get_upload_url( $content_type, $file_name, $file_size ) {

		$body = array(
			'files' => array(
				array(
					'content_type' => $content_type,
					'file_name'    => $file_name,
					'file_size'    => (int) $file_size,
				),
			),
		);

		$response = $this->remote_post(
			'/s2s/v2.0/file',
			$body
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$file_data = $response['data']['files'][0] ?? null;
		$file_id = is_array( $file_data ) ? ( $file_data['file_id'] ?? '' ) : '';
		$upload_request = is_array( $file_data ) && isset( $file_data['requests'][0] ) && is_array( $file_data['requests'][0] ) ? $file_data['requests'][0] : array();
		$upload_url = $upload_request['url'] ?? '';
		$upload_headers = $upload_request['headers'] ?? array();

		if (
			! is_array( $file_data )
			|| ! is_string( $file_id )
			|| '' === $file_id
			|| 512 < strlen( $file_id )
			|| preg_match( '/[\x00-\x1F\x7F]/', $file_id )
			|| ! is_string( $upload_url )
			|| 'https' !== wp_parse_url( $upload_url, PHP_URL_SCHEME )
			|| ! wp_http_validate_url( $upload_url )
			|| ! is_array( $upload_headers )
		) {
			return new WP_Error(
				'ycait_file_api_parse',
				__( 'Unexpected response from File API.', 'youcam-ai-image-tools' )
			);
		}

		$safe_headers = array();
		foreach ( $upload_headers as $name => $value ) {
			$name = is_string( $name ) ? strtolower( $name ) : '';
			if ( ! in_array( $name, array( 'content-type', 'content-length' ), true ) || ! is_scalar( $value ) || preg_match( '/[\r\n]/', (string) $value ) ) { continue; }
			$safe_headers[ 'content-type' === $name ? 'Content-Type' : 'Content-Length' ] = (string) $value;
		}

		return array( 'file_id' => $file_id, 'upload_url' => $upload_url, 'method' => 'PUT', 'headers' => $safe_headers );
	}

	/**
	 * Step 2: Upload the actual image binary to the pre-signed URL.
	 *
	 * @param string $upload_url    Pre-signed URL from get_upload_url().
	 * @param string $file_path     Local file path to the image.
	 * @param string $content_type MIME type.
	 * @param array  $extra_headers Extra headers from File API response.
	 * @return bool|WP_Error
	 */
	public function upload_file( $upload_url, $file_path, $content_type, $extra_headers = array() ) {
		if ( ! is_string( $upload_url ) || 'https' !== wp_parse_url( $upload_url, PHP_URL_SCHEME ) || ! wp_http_validate_url( $upload_url ) || ! is_file( $file_path ) || ! is_readable( $file_path ) ) {
			return new WP_Error(
				'ycait_file_missing',
				__( 'The image file could not be found.', 'youcam-ai-image-tools' )
			);
		}

		$file_size = filesize( $file_path );
		if ( false === $file_size || 0 === $file_size || 10 * 1024 * 1024 < $file_size ) {
			return new WP_Error( 'ycait_file_size', __( 'The image file must be no larger than 10 MB.', 'youcam-ai-image-tools' ) );
		}
		$file_contents = file_get_contents( $file_path );

		if ( false === $file_contents ) {
			return new WP_Error(
				'ycait_file_read',
				__( 'Unable to read the image file.', 'youcam-ai-image-tools' )
			);
		}

		$headers = array(
			'Content-Type' => $content_type,
		);

		// Merge headers supplied by the File API.
		if ( ! empty( $extra_headers ) && is_array( $extra_headers ) ) {
			foreach ( $extra_headers as $key => $value ) {
				if ( in_array( $key, array( 'Content-Type', 'Content-Length' ), true ) && is_scalar( $value ) && ! preg_match( '/[\r\n]/', (string) $value ) ) { $headers[ $key ] = (string) $value; }
			}
		}

		$response = wp_safe_remote_request(
			$upload_url,
			array(
				'method'  => 'PUT',
				'body'    => $file_contents,
				'headers' => $headers,
				'timeout'             => 60,
				'redirection'         => 0,
				'limit_response_size' => 1024,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'ycait_upload_failed', __( 'The image could not be uploaded to the YouCam File API.', 'youcam-ai-image-tools' ) );
		}

		$code = (int) wp_remote_retrieve_response_code( $response );

		if ( $code < 200 || $code >= 300 ) {
			return new WP_Error(
				'ycait_upload_failed',
				sprintf(
					/* translators: %d: HTTP status code */
					__( 'File upload failed with status code %d.', 'youcam-ai-image-tools' ),
					$code
				),
				array( 'status' => $code )
			);
		}

		return true;
	}

	/**
	 * Get the File API URL for a file ID.
	 *
	 * This is retained for compatibility with existing code.
	 * The cloth-v4 task should preferably use the file_id directly
	 * rather than converting it to a URL.
	 *
	 * @param string $file_id File ID from the File API.
	 * @return string
	 */
	public function get_file_url( $file_id ) {
		return $this->get_base_url() . '/s2s/v2.0/file/' . rawurlencode( $file_id );
	}

	/** Upload a local image to the YouCam File API and return its file ID. */
	public function upload_local_image( $path, $name, $mime ) {
		$size = filesize( $path );
		$upload = $this->get_upload_url( $mime, $name, $size );
		if ( is_wp_error( $upload ) ) { return $upload; }
		$result = $this->upload_file( $upload['upload_url'], $path, $mime, $upload['headers'] );
		if ( is_wp_error( $result ) ) { return $result; }
		return $upload['file_id'];
	}

	/** Submit a documented asynchronous tool task. */
	public function create_tool_task( $endpoint, $body ) { return $this->remote_post( $endpoint, $body ); }

	/** Fetch an asynchronous tool task status. */
	public function get_tool_task( $endpoint ) { return $this->remote_get( $endpoint ); }

	/**
	 * Step 3: Create a try-on task.
	 *
	 * Both images must already have validated YouCam File API IDs.
	 *
	 * @param string $src_file    Person image File API ID.
	 * @param string $ref_file    Clothing image File API ID.
	 * @param string $garment_category Garment category.
	 * @return array|WP_Error Task data including task_id.
	 */
	public function create_tryon_task( $src_file, $ref_file, $garment_category = 'auto', $product_type = 'clothes', $gender = 'female' ) {
		$endpoints = array(
			'clothes' => '/s2s/v2.0/task/cloth-v4',
			'hat'     => '/s2s/v2.0/task/hat',
			'scarf'   => '/s2s/v2.0/task/scarf',
			'bag'     => '/s2s/v2.0/task/bag',
		);
		if ( ! isset( $endpoints[ $product_type ] ) ) {
			$product_type = 'clothes';
		}
		if ( '' === $src_file || '' === $ref_file || filter_var( $src_file, FILTER_VALIDATE_URL ) || filter_var( $ref_file, FILTER_VALIDATE_URL ) ) {
			return new WP_Error( 'ycait_invalid_file_id', __( 'Try-on requests must use validated YouCam File API image IDs.', 'youcam-ai-image-tools' ) );
		}

		$body = array();
		if ( 'clothes' === $product_type ) {
			$body['garment_category']    = $garment_category;
			$body['filter_multi_person'] = 'off';
		} else {
			$body['gender'] = in_array( $gender, array( 'female', 'male' ), true ) ? $gender : 'female';
			$body['style']  = 'random';
		}

		$body['src_file_id'] = sanitize_text_field( $src_file );
		$body['ref_file_id'] = sanitize_text_field( $ref_file );

		return $this->remote_post( $endpoints[ $product_type ], $body );
	}

	/**
	 * Step 4: Poll a task by its ID.
	 *
	 * @param string $task_id Task ID from create_tryon_task().
	 * @return array|WP_Error Task status data.
	 */
	public function get_task_status( $task_id, $product_type = 'clothes' ) {
		$endpoints = array(
			'clothes' => 'cloth-v4',
			'hat'     => 'hat',
			'scarf'   => 'scarf',
			'bag'     => 'bag',
		);
		if ( ! isset( $endpoints[ $product_type ] ) ) {
			$product_type = 'clothes';
		}
		return $this->remote_get(
			'/s2s/v2.0/task/' . $endpoints[ $product_type ] . '/' . rawurlencode( $task_id )
		);
	}

	/**
	 * Perform a POST request to the API.
	 *
	 * @param string $endpoint API endpoint path.
	 * @param array  $body     Associative array to send as JSON.
	 * @return array|WP_Error Parsed response body or error.
	 */
	protected function remote_post( $endpoint, $body ) {
		$url = $this->get_base_url() . $endpoint;

		$response = wp_safe_remote_post(
			$url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->get_api_key(),
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
				'timeout'             => 30,
				'redirection'         => 0,
				'limit_response_size' => 1024 * 1024,
			)
		);

		return $this->parse_response( $response );
	}

	/**
	 * Perform a GET request to the API.
	 *
	 * @param string $endpoint API endpoint path.
	 * @return array|WP_Error Parsed response body or error.
	 */
	protected function remote_get( $endpoint ) {
		$url = $this->get_base_url() . $endpoint;

		$response = wp_safe_remote_get(
			$url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->get_api_key(),
					'Content-Type'  => 'application/json',
				),
				'timeout'             => 30,
				'redirection'         => 0,
				'limit_response_size' => 1024 * 1024,
			)
		);

		return $this->parse_response( $response );
	}

	/**
	 * Parse and validate an API response.
	 *
	 * @param array|WP_Error $response Raw HTTP response.
	 * @return array|WP_Error
	 */
	protected function parse_response( $response ) {
		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'ycait_http_request_failed', __( 'The YouCam API could not be reached. Check the API settings and try again.', 'youcam-ai-image-tools' ) );
		}

		$code    = (int) wp_remote_retrieve_response_code( $response );
		$raw     = wp_remote_retrieve_body( $response );
		$decoded = json_decode( $raw, true );

		if ( ! is_array( $decoded ) ) {
			return new WP_Error(
				'ycait_invalid_json',
				__( 'The API returned an invalid response.', 'youcam-ai-image-tools' ),
				array( 'status' => $code )
			);
		}

		if ( $code < 200 || $code >= 300 ) {
			return new WP_Error(
				'ycait_api_error',
				sprintf(
					/* translators: %d: HTTP status code */
					__( 'The YouCam API returned an error (HTTP %d).', 'youcam-ai-image-tools' ),
					$code
				),
				array( 'status' => $code )
			);
		}

		return $decoded;
	}
}
