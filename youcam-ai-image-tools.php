<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://my-portfolio-app-sepia-kappa.vercel.app
 * @since             1.0.0
 * @package           Youcam_Ai_Image_Tools
 *
 * @wordpress-plugin
 * Plugin Name:       YouCam AI Image Tools
 * Plugin URI:        https://my-portfolio-app-sepia-kappa.vercel.app
 * Description:       Provides YouCam AI image tools for WordPress, with optional WooCommerce virtual try-on support.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Rickrods
 * Author URI:        https://my-portfolio-app-sepia-kappa.vercel.app/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       youcam-ai-image-tools
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'YOUCAM_AI_IMAGE_TOOLS_VERSION', '1.0.0' );
define( 'YOUCAM_AI_IMAGE_TOOLS_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-youcam-ai-image-tools-activator.php
 */
function activate_youcam_ai_image_tools() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-youcam-ai-image-tools-activator.php';
	Youcam_Ai_Image_Tools_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-youcam-ai-image-tools-deactivator.php
 */
function deactivate_youcam_ai_image_tools() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-youcam-ai-image-tools-deactivator.php';
	Youcam_Ai_Image_Tools_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_youcam_ai_image_tools' );
register_deactivation_hook( __FILE__, 'deactivate_youcam_ai_image_tools' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-youcam-ai-image-tools.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_youcam_ai_image_tools() {

	$plugin = new Youcam_Ai_Image_Tools();
	$plugin->run();

}
run_youcam_ai_image_tools();
