<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://my-portfolio-app-sepia-kappa.vercel.app
 * @since      1.0.0
 *
 * @package    Youcam_Ai_Image_Tools
 * @subpackage Youcam_Ai_Image_Tools/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Youcam_Ai_Image_Tools
 * @subpackage Youcam_Ai_Image_Tools/includes
 * @author     Rickrods <rickrods@proton.me>
 */
class Youcam_Ai_Image_Tools_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'youcam-ai-image-tools',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
