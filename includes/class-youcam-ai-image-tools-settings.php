<?php
/** YouCam AI Image Tools settings. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class YCAIT_Settings {
	protected $option_map = array(
		'api_key' => 'ycait_api_key', 'api_base_url' => 'ycait_api_base_url',
		'default_model_female' => 'ycait_default_model_female', 'default_model_male' => 'ycait_default_model_male',
		'model_name_female' => 'ycait_model_name_female', 'model_name_male' => 'ycait_model_name_male',
		'guest_enabled' => 'ycait_guest_enabled', 'guest_limit' => 'ycait_guest_limit',
		'user_enabled' => 'ycait_user_enabled', 'user_limit' => 'ycait_user_limit',
		'tool_user_limit' => 'ycait_tool_user_limit',
		'usage_retention' => 'ycait_usage_retention', 'usage_retention_unit' => 'ycait_usage_retention_unit',
	);
	protected $defaults = array(
		'api_key' => '', 'api_base_url' => 'https://yce-api-01.makeupar.com',
		'default_model_female' => '', 'default_model_male' => '', 'guest_enabled' => 0,
		'model_name_female' => 'Female Model', 'model_name_male' => 'Male Model',
		'guest_limit' => 10, 'user_enabled' => 0, 'user_limit' => 30, 'tool_user_limit' => 100,
		'usage_retention' => 30, 'usage_retention_unit' => 'days',
	);
	protected $cache = null;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_media' ) );
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_page' ) );
		add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'sanitize_woocommerce_option' ), 10, 3 );
		add_action( 'ycait_cleanup_usage', array( $this, 'cleanup_usage_records' ) );
		if ( ! wp_next_scheduled( 'ycait_cleanup_usage' ) ) { wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'ycait_cleanup_usage' ); }
	}

	/** Register the try-on tab only when WooCommerce is active. */
	public function add_woocommerce_settings_page( $pages ) {
		if ( ! class_exists( 'WC_Settings_Page' ) ) { return $pages; }
		require_once __DIR__ . '/class-youcam-ai-image-tools-wc-settings-page.php';
		foreach ( $pages as $page ) { if ( $page instanceof YCAIT_WC_Settings_Page ) { return $pages; } }
		$pages[] = new YCAIT_WC_Settings_Page();
		return $pages;
	}

	/** Sanitize values submitted through WooCommerce's settings form. */
	public function sanitize_woocommerce_option( $value, $option, $raw_value = null ) {
		if ( ! is_array( $option ) || empty( $option['id'] ) ) { return $value; }
		$option_id = $option['id'];
		if ( in_array( $option_id, array( 'ycait_guest_enabled', 'ycait_user_enabled' ), true ) ) { return in_array( (string) $value, array( 'yes', '1', 'true', 'on' ), true ) ? 'yes' : 'no'; }
		if ( in_array( $option_id, array( 'ycait_guest_limit', 'ycait_user_limit' ), true ) ) { return max( 0, absint( $value ) ); }
		if ( in_array( $option_id, array( 'ycait_default_model_female', 'ycait_default_model_male' ), true ) ) { return $this->sanitize_model_image_value( $value ); }
		if ( in_array( $option_id, array( 'ycait_model_name_female', 'ycait_model_name_male' ), true ) ) { return is_scalar( $value ) ? sanitize_text_field( $value ) : ''; }
		return $value;
	}

	private function sanitize_model_image_value( $value ) {
		if ( ! is_scalar( $value ) ) { return ''; }
		if ( is_numeric( $value ) ) { $value = absint( $value ); return $value && wp_attachment_is_image( $value ) ? $value : ''; }
		$url   = esc_url_raw( trim( (string) $value ) );
		$parts = wp_parse_url( $url );
		return is_array( $parts ) && 'https' === strtolower( $parts['scheme'] ?? '' ) && ! empty( $parts['host'] ) && ! isset( $parts['user'] ) && ! isset( $parts['pass'] ) && wp_http_validate_url( $url ) ? $url : '';
	}

	public function add_settings_page() {
		add_menu_page( __( 'YouCam AI Image Tools', 'youcam-ai-image-tools' ), __( 'YouCam AI', 'youcam-ai-image-tools' ), 'edit_posts', 'youcam-ai-image-tools', array( $this, 'render_wordpress_settings_page' ), 'dashicons-format-image' );
		add_submenu_page( 'youcam-ai-image-tools', __( 'Settings', 'youcam-ai-image-tools' ), __( 'Settings', 'youcam-ai-image-tools' ), 'edit_posts', 'youcam-ai-image-tools', array( $this, 'render_wordpress_settings_page' ) );
	}

	public function register_settings() {
		foreach ( $this->option_map as $key => $option ) {
			register_setting( 'youcam_ai_image_tools', $option, array( 'sanitize_callback' => function ( $value ) use ( $key, $option ) {
		if ( ! is_scalar( $value ) ) { return get_option( $option, $this->defaults[ $key ] ?? '' ); }
				if ( in_array( $key, array( 'guest_enabled', 'user_enabled' ), true ) ) { return in_array( (string) $value, array( 'yes', '1', 'true', 'on' ), true ) ? 'yes' : 'no'; }
				if ( in_array( $key, array( 'guest_limit', 'user_limit', 'tool_user_limit' ), true ) ) { return max( 0, absint( $value ) ); }
				if ( 'usage_retention' === $key ) { return max( 1, absint( $value ) ); }
				if ( 'usage_retention_unit' === $key ) { return in_array( $value, array( 'days', 'weeks', 'months', 'years' ), true ) ? $value : 'days'; }
				if ( in_array( $key, array( 'default_model_female', 'default_model_male' ), true ) ) { return $this->sanitize_model_image_value( $value ); }
				if ( 'api_key' === $key ) { return '' === $value ? get_option( $option, '' ) : sanitize_text_field( $value ); }
				if ( 'api_base_url' === $key ) {
					$url = esc_url_raw( trim( (string) $value ) );
					$parts = wp_parse_url( $url );
					if ( ! is_array( $parts ) || 'https' !== strtolower( $parts['scheme'] ?? '' ) || empty( $parts['host'] ) || isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['query'] ) || isset( $parts['fragment'] ) || ! wp_http_validate_url( $url ) ) { return get_option( $option, $this->defaults['api_base_url'] ); }
					return untrailingslashit( $url );
				}
				return sanitize_text_field( $value );
			} ) );
		}
	}

	public function enqueue_media( $hook ) {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		if ( 'youcam-ai-image-tools' !== $page && ( 'wc-settings' !== $page || 'ycait_virtual_try_on' !== $tab ) ) { return; }
		wp_enqueue_media();
		wp_enqueue_script( 'ycait-admin', YOUCAM_AI_IMAGE_TOOLS_URL . 'admin/js/youcam-ai-image-tools-admin.js', array( 'jquery', 'media-editor', 'media-views' ), YOUCAM_AI_IMAGE_TOOLS_VERSION, true );
	}

	public function render_wordpress_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			echo '<div class="wrap"><h1 class="ycait-settings-page-title">' . esc_html__( 'YouCam AI Image Tools Settings', 'youcam-ai-image-tools' ) . '</h1><hr class="ycait-settings-title-separator"><p>' . esc_html__( 'Use the image generation and enhancement tools from the submenu.', 'youcam-ai-image-tools' ) . '</p></div>';
			return;
		}
		?>
		<div class="wrap"><h1 class="ycait-settings-page-title"><u><strong><?php esc_html_e( 'YouCam AI Image Tools Settings', 'youcam-ai-image-tools' ); ?></strong></u></h1><hr class="ycait-settings-title-separator"><form method="post" action="options.php">
		<?php settings_fields( 'youcam_ai_image_tools' ); ?>
		<h2><u><?php esc_html_e( 'API', 'youcam-ai-image-tools' ); ?></u></h2><table class="form-table"><tbody>
		<tr><th><label for="ycait_api_key">API Key:</label></th><td><input class="regular-text" type="password" autocomplete="new-password" id="ycait_api_key" name="ycait_api_key" value="" placeholder="<?php echo esc_attr( get_option( 'ycait_api_key' ) ? 'Saved; leave blank to keep current key' : 'Enter API key' ); ?>"><p class="description"><?php printf( esc_html__( 'API keys are available in the %s.', 'youcam-ai-image-tools' ), '<a href="' . esc_url( 'https://yce.makeupar.com/api-console/en/' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'YouCam API Dashboard', 'youcam-ai-image-tools' ) . '</a>' ); ?></p></td></tr>
		<tr><th><label for="ycait_api_base_url">API Base URL:</label></th><td><input class="regular-text" type="url" id="ycait_api_base_url" name="ycait_api_base_url" value="<?php echo esc_attr( get_option( 'ycait_api_base_url', $this->defaults['api_base_url'] ) ); ?>"><p class="description"><?php printf( esc_html__( 'Use an HTTPS base URL supplied by %s.', 'youcam-ai-image-tools' ), '<a href="' . esc_url( 'https://docs.perfectcorp.com/develop/introduction' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'YouCam API', 'youcam-ai-image-tools' ) . '</a>' ); ?></p></td></tr>
		</tbody></table><hr class="ycait-settings-separator"><h2><u><?php esc_html_e( 'Usage Limits', 'youcam-ai-image-tools' ); ?></u></h2><table class="form-table"><tbody>
		<tr><th><label for="ycait_tool_user_limit"><?php esc_html_e( 'Monthly Request Limit:', 'youcam-ai-image-tools' ); ?></label></th><td><input type="number" min="0" step="1" id="ycait_tool_user_limit" name="ycait_tool_user_limit" value="<?php echo esc_attr( get_option( 'ycait_tool_user_limit', $this->defaults['tool_user_limit'] ) ); ?>"><p class="description"><?php esc_html_e( 'Maximum generation and enhancement tasks per account each month (0 = unlimited). This applies to all accounts, including administrators.', 'youcam-ai-image-tools' ); ?></p></td></tr>
		</tbody></table>
		<hr class="ycait-settings-separator"><h2><u><?php esc_html_e( 'Usage data retention', 'youcam-ai-image-tools' ); ?></u></h2>
		<table class="form-table"><tbody>
		<tr><th><label for="ycait_usage_retention"><?php esc_html_e( 'Retain usage data for:', 'youcam-ai-image-tools' ); ?></label></th><td><input type="number" min="1" step="1" id="ycait_usage_retention" name="ycait_usage_retention" value="<?php echo esc_attr( get_option( 'ycait_usage_retention', $this->defaults['usage_retention'] ) ); ?>"><p class="description"><?php esc_html_e( 'How long to keep historical usage data beyond the 30 days needed for monthly limits.', 'youcam-ai-image-tools' ); ?></p></td></tr>
		<tr><th><label for="ycait_usage_retention_unit"><?php esc_html_e( 'Retention period unit:', 'youcam-ai-image-tools' ); ?></label></th><td><select id="ycait_usage_retention_unit" name="ycait_usage_retention_unit"><?php foreach ( array( 'days' => __( 'Days', 'youcam-ai-image-tools' ), 'weeks' => __( 'Weeks', 'youcam-ai-image-tools' ), 'months' => __( 'Months', 'youcam-ai-image-tools' ), 'years' => __( 'Years', 'youcam-ai-image-tools' ) ) as $unit => $label ) : ?><option value="<?php echo esc_attr( $unit ); ?>" <?php selected( get_option( 'ycait_usage_retention_unit', $this->defaults['usage_retention_unit'] ), $unit ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></td></tr>
		</tbody></table><?php submit_button(); ?></form></div>
		<?php
	}

	public function get_all() {
		if ( null === $this->cache ) { $this->cache = array(); foreach ( $this->option_map as $key => $option ) { $this->cache[ $key ] = get_option( $option, $this->defaults[ $key ] ?? '' ); } }
		return $this->cache;
	}
	public function get( $key, $default = '' ) { $all = $this->get_all(); return $all[ $key ] ?? $default; }
	private function is_tryon_enabled( $group ) {
		return in_array( $this->get( $group . '_enabled', 0 ), array( 1, '1', true, 'yes', 'true', 'on' ), true );
	}
	public function get_model_url( $key ) {
		$value = $this->get( $key );
		if ( is_numeric( $value ) ) { return wp_get_attachment_image_url( absint( $value ), 'full' ) ?: ''; }
		return esc_url_raw( $value );
	}
	public function access_error() {
		$user = wp_get_current_user();
		$privileged = array_intersect( array( 'administrator', 'editor', 'author', 'contributor' ), (array) $user->roles );
		if ( $privileged ) { return ''; }
		$group = is_user_logged_in() ? 'user' : 'guest';
		if ( ! $this->is_tryon_enabled( $group ) ) { return __( 'Virtual try-on is currently disabled for your account.', 'youcam-ai-image-tools' ); }
		$limit = absint( $this->get( $group . '_limit', 0 ) );
		if ( $limit && $this->get_usage( $group ) >= $limit ) { return __( 'You have reached this month’s virtual try-on limit.', 'youcam-ai-image-tools' ); }
		return '';
	}
	public function get_usage( $group ) {
		$identity = 'user' === $group ? 'user:' . get_current_user_id() : 'ip:' . $this->get_client_ip();
		$key = 'ycait_usage_' . current_time( 'Ym' ) . '_' . md5( $identity );
		return absint( get_option( $key, 0 ) );
	}
	public function record_usage() {
		return $this->reserve_usage();
	}

	/** Atomically reserve one monthly try-on attempt before making paid API requests. */
	public function reserve_usage() {
		$access_error = $this->access_error();
		if ( $access_error ) { return new WP_Error( 'ycait_usage_limit', $access_error ); }
		global $wpdb;
		$group = is_user_logged_in() ? 'user' : 'guest';
		$identity = 'user' === $group ? 'user:' . get_current_user_id() : 'ip:' . $this->get_client_ip();
		$key = 'ycait_usage_' . current_time( 'Ym' ) . '_' . md5( $identity );
		$limit = absint( $this->get( $group . '_limit', 0 ) );
		$user = wp_get_current_user();
		if ( array_intersect( array( 'administrator', 'editor', 'author', 'contributor' ), (array) $user->roles ) ) { $limit = 0; }
		$sql = $wpdb->prepare(
			"INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, '1', 'no') ON DUPLICATE KEY UPDATE option_value = IF(%d = 0 OR CAST(option_value AS UNSIGNED) < %d, CAST(option_value AS UNSIGNED) + 1, option_value)",
			$key,
			$limit,
			$limit
		);
		$affected = $wpdb->query( $sql );
		if ( false === $affected ) { return new WP_Error( 'ycait_usage_store', __( 'Could not reserve your try-on usage. Please try again.', 'youcam-ai-image-tools' ) ); }
		wp_cache_delete( $key, 'options' );
		wp_cache_delete( 'notoptions', 'options' );
		if ( $limit && 0 === $affected ) { return new WP_Error( 'ycait_usage_limit', __( 'You have reached this month’s virtual try-on limit.', 'youcam-ai-image-tools' ) ); }
		$this->record_units( 'try-on', 2 );
		return true;
	}

	/** Reserve a paid admin-tool request and account its documented API unit cost. */
	public function reserve_tool_usage( $feature, $units ) {
		$feature = sanitize_key( $feature );
		$units = max( 1, absint( $units ) );
		$identity = 'user:' . get_current_user_id();
		$month = current_time( 'Ym' );
		$key = 'ycait_tool_usage_' . $month . '_' . md5( $identity );
		$limit = absint( $this->get( 'tool_user_limit', 100 ) );
		global $wpdb;
		$sql = $wpdb->prepare(
			"INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, '1', 'no') ON DUPLICATE KEY UPDATE option_value = IF(%d = 0 OR CAST(option_value AS UNSIGNED) < %d, CAST(option_value AS UNSIGNED) + 1, option_value)",
			$key, $limit, $limit
		);
		$affected = $wpdb->query( $sql );
		if ( false === $affected ) { return new WP_Error( 'ycait_usage_store', __( 'Could not reserve your monthly generation and enhancement allowance. Please try again.', 'youcam-ai-image-tools' ) ); }
		wp_cache_delete( $key, 'options' );
		wp_cache_delete( 'notoptions', 'options' );
		if ( $limit && 0 === $affected ) { return new WP_Error( 'ycait_tool_limit', __( 'You have reached this month’s generation and enhancement task limit.', 'youcam-ai-image-tools' ) ); }
		$this->record_units( $feature, $units );
		return true;
	}

	/** Keep monthly request and API-unit totals for the identity and feature. */
	protected function record_units( $feature, $units ) {
		$identity = is_user_logged_in() ? 'user:' . get_current_user_id() : 'ip:' . $this->get_client_ip();
		$base = 'ycait_accounting_' . current_time( 'Ym' ) . '_' . md5( $identity );
		$this->increment_option( $base . '_requests', 1 );
		$this->increment_option( $base . '_units', $units );
		$feature_key = $base . '_' . sanitize_key( $feature );
		$this->increment_option( $feature_key . '_requests', 1 );
		$this->increment_option( $feature_key . '_units', $units );
	}

	protected function increment_option( $key, $amount ) {
		global $wpdb;
		$sql = $wpdb->prepare( "INSERT INTO {$wpdb->options} (option_name, option_value, autoload) VALUES (%s, %d, 'no') ON DUPLICATE KEY UPDATE option_value = CAST(option_value AS UNSIGNED) + %d", $key, $amount, $amount );
		$wpdb->query( $sql );
		wp_cache_delete( $key, 'options' );
		wp_cache_delete( 'notoptions', 'options' );
	}

	/** Delete expired monthly usage aggregates while preserving the active month. */
	public function cleanup_usage_records() {
		global $wpdb;
		$number = max( 1, absint( $this->get( 'usage_retention', 30 ) ) );
		$unit = $this->get( 'usage_retention_unit', 'days' );
		if ( ! in_array( $unit, array( 'days', 'weeks', 'months', 'years' ), true ) ) { $unit = 'days'; }
		$cutoff = ( new DateTimeImmutable( 'now', wp_timezone() ) )->modify( '-' . $number . ' ' . $unit )->getTimestamp();
		$like_usage = $wpdb->esc_like( 'ycait_usage_' ) . '%';
		$like_tool  = $wpdb->esc_like( 'ycait_tool_usage_' ) . '%';
		$like_acct  = $wpdb->esc_like( 'ycait_accounting_' ) . '%';
		$keys = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s", $like_usage, $like_tool, $like_acct ) );
		$current_month = current_time( 'Ym' );
		foreach ( $keys as $key ) {
			if ( ! preg_match( '/^ycait_(?:usage|tool_usage|accounting)_(\d{6})_/', $key, $matches ) || $current_month === $matches[1] ) { continue; }
			$month_start = DateTimeImmutable::createFromFormat( '!Ym', $matches[1], wp_timezone() );
			if ( ! $month_start ) { continue; }
			$month_end = $month_start->modify( '+1 month' )->getTimestamp();
			if ( $month_end < $cutoff ) {
				$wpdb->delete( $wpdb->options, array( 'option_name' => $key ), array( '%s' ) );
				wp_cache_delete( $key, 'options' );
			}
		}
		$poll_cutoff = time() - DAY_IN_SECONDS;
		$like_poll = $wpdb->esc_like( 'ycait_poll_' ) . '%';
		$like_tool_poll = $wpdb->esc_like( 'ycait_tool_poll_' ) . '%';
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE (option_name LIKE %s OR option_name LIKE %s) AND CAST(option_value AS UNSIGNED) < %d", $like_poll, $like_tool_poll, $poll_cutoff ) );
		$like_upload_rate = $wpdb->esc_like( 'ycait_upload_rate_' ) . '%';
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND SUBSTRING(option_name, 19, 10) < %s", $like_upload_rate, current_time( 'YmdH' ) ) );
		wp_cache_delete( 'notoptions', 'options' );
	}
	protected function get_client_ip() { return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ); }
}
