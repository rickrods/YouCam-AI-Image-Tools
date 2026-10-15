<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://my-portfolio-app-sepia-kappa.vercel.app
 * @since      1.0.0
 *
 * @package    Youcam_Ai_Image_Tools
 * @subpackage Youcam_Ai_Image_Tools/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Youcam_Ai_Image_Tools
 * @subpackage Youcam_Ai_Image_Tools/includes
 * @author     Rickrods <rickrods@proton.me>
 */
class Youcam_Ai_Image_Tools_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'ycait_cleanup_usage' );
	}

}
