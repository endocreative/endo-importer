<?php
/**
 * Plugin Name: Endo Importer
 * Plugin URI: http://www.endocreative.com
 * Description: A basic class for bulk importing posts or users via a CSV file.
 * Version: 1.0.0
 * Author: Endo Creative
 * Author URI: http://www.endocreative.com
 * Text Domain: endo-importer
 * License: GPL2
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-endo-importer.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_endo_importer() {
	$plugin = new Endo_Importer();
	$plugin->run();
}
run_endo_importer();