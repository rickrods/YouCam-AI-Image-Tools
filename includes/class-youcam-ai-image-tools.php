<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://my-portfolio-app-sepia-kappa.vercel.app
 * @since      1.0.0
 *
 * @package    Youcam_Ai_Image_Tools
 * @subpackage Youcam_Ai_Image_Tools/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Youcam_Ai_Image_Tools
 * @subpackage Youcam_Ai_Image_Tools/includes
 * @author     Rickrods <rickrods@proton.me>
 */
class Youcam_Ai_Image_Tools {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Youcam_Ai_Image_Tools_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/** Settings shared by the integrated try-on services. */
	protected $settings;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'YOUCAM_AI_IMAGE_TOOLS_VERSION' ) ) {
			$this->version = YOUCAM_AI_IMAGE_TOOLS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'youcam-ai-image-tools';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Youcam_Ai_Image_Tools_Loader. Orchestrates the hooks of the plugin.
	 * - Youcam_Ai_Image_Tools_i18n. Defines internationalization functionality.
	 * - Youcam_Ai_Image_Tools_Admin. Defines all hooks for the admin area.
	 * - Youcam_Ai_Image_Tools_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-youcam-ai-image-tools-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-youcam-ai-image-tools-api-client.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-youcam-ai-image-tools-ajax.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-youcam-ai-image-tools-frontend.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-youcam-ai-image-tools-tools.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-youcam-ai-image-tools-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-youcam-ai-image-tools-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-youcam-ai-image-tools-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-youcam-ai-image-tools-public.php';

		$this->loader = new Youcam_Ai_Image_Tools_Loader();

		$this->settings = new YCAIT_Settings();
		$api = new YCAIT_API_Client( $this->settings );
		new YCAIT_Ajax( $api, $this->settings );
		new YCAIT_Frontend( $this->settings );
		new YCAIT_Tools( $api, $this->settings );

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Youcam_Ai_Image_Tools_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Youcam_Ai_Image_Tools_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Youcam_Ai_Image_Tools_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Youcam_Ai_Image_Tools_Public( $this->get_plugin_name(), $this->get_version() );

		// The integrated frontend service enqueues the try-on assets on product pages.

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Youcam_Ai_Image_Tools_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
