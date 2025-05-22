<?php
/**
 * LMSACE Connect Module
 *
 * ModuleName: Stats Counter
 * Description: This module fetches and displays statistics from Moodle, such as total courses, active enrolments, course completions, and total users. It provides a Visual Composer element and a WordPress Block.
 * Version: 1.0
 *
 * @package LMSACE Connect
 * @subpackage StatsCounter
 * @copyright  2024 LMSACE DEV TEAM <info@lmsace.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Deny direct file execution if this file is called directly.
if ( ! defined( 'WPINC' ) ) {
    die( 'No direct Access!' );
}

// Define a constant for the module file path.
if ( ! defined( 'LACMOD_STATS_COUNTER_MODULE_FILE' ) ) {
	define( 'LACMOD_STATS_COUNTER_MODULE_FILE', __FILE__ );
}

// Include the main class file for the Stats Counter module.
require_once( __DIR__ .'/includes/class-lacmod-stats-counter.php' );

/**
 * Returns the main instance of LACONNMOD_Stats_Counter.
 *
 * @return LACONNMOD_Stats_Counter
 */
function LACONN_MOD_Stats_Counter() {
    return LACONNMOD_Stats_Counter::instance();
}

// Initialize the module.
// We will uncomment and complete this once the main class and its init() method are defined.

global $LACONN_MOD_Stats_Counter_Instance; // Using a unique global variable name.
$LACONN_MOD_Stats_Counter_Instance = LACONN_MOD_Stats_Counter();
if ( $LACONN_MOD_Stats_Counter_Instance && method_exists( $LACONN_MOD_Stats_Counter_Instance, 'init' ) ) {
    $LACONN_MOD_Stats_Counter_Instance->init();
}

// Placeholder for further module initialization, like loading admin classes or frontend assets.
// For example, if there's an admin component:
// if ( is_admin() ) {
//    require_once( __DIR__ . '/includes/admin/class-lacmod-stats-counter-admin.php' );
//    LACONNMOD_Stats_Counter_Admin::instance()->init();
// }

// Load the Visual Composer element class.
if ( class_exists( 'Vc_Manager' ) ) {
    require_once( __DIR__ . '/includes/class-lacmod-stats-counter-vc.php' );
}

// Load the WordPress Block handler class.
if ( function_exists( 'register_block_type' ) ) {
    require_once( __DIR__ . '/includes/class-lacmod-stats-counter-block.php' );
}

// Placeholder for loading the WordPress Block assets if not using block.json for script/style registration.
// add_action( 'enqueue_block_editor_assets', 'lacmod_stats_counter_block_editor_assets' );
// function lacmod_stats_counter_block_editor_assets() {
//     wp_enqueue_script(
//         'lacmod-stats-counter-block-editor',
//         plugins_url( 'block/build/index.js', __FILE__ ),
//         array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wp-i18n' ),
//         filemtime( plugin_dir_path( __FILE__ ) . 'block/build/index.js' )
//     );
//     wp_enqueue_style(
//         'lacmod-stats-counter-block-editor',
//         plugins_url( 'block/build/index.css', __FILE__ ),
//         array( 'wp-edit-blocks' ),
//         filemtime( plugin_dir_path( __FILE__ ) . 'block/build/index.css' )
//     );
// }

// add_action( 'enqueue_block_assets', 'lacmod_stats_counter_block_frontend_assets' );
// function lacmod_stats_counter_block_frontend_assets() {
//     if ( !is_admin()) { // Only enqueue on frontend, editor styles handled by editorStyle in block.json
//         wp_enqueue_style(
//             'lacmod-stats-counter-block-frontend',
//             plugins_url( 'block/build/style-index.css', __FILE__ ),
//             array(),
//             filemtime( plugin_dir_path( __FILE__ ) . 'block/build/style-index.css' )
//         );
//     }
// }

?>
