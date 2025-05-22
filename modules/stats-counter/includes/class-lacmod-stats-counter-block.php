<?php
/**
 * LMSACE Connect - Stats Counter WordPress Block Handler
 *
 * This class handles the registration and server-side rendering
 * for the Moodle Stats Counter WordPress Block (Gutenberg).
 *
 * @package LMSACE Connect
 * @subpackage StatsCounter
 * @copyright  2024 LMSACE DEV TEAM <info@lmsace.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LACONNMOD_Stats_Counter_Block' ) ) {

    /**
     * Stats Counter Block Handler Class.
     */
    class LACONNMOD_Stats_Counter_Block {

        /**
         * Instance of this class.
         *
         * @var LACONNMOD_Stats_Counter_Block
         */
        protected static $instance = null;

        /**
         * Return an instance of this class.
         *
         * @return LACONNMOD_Stats_Counter_Block A single instance of this class.
         */
        public static function instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Constructor.
         * Hooks into WordPress initialization to register the block.
         */
        protected function __construct() {
            add_action( 'init', array( $this, 'register_block' ) );
        }

        /**
         * Register the Gutenberg block.
         */
        public function register_block() {
            // Check if the LACONNMOD_Stats_Counter class is available.
            if ( ! class_exists( 'LACONNMOD_Stats_Counter' ) ) {
                // Optionally log an error or add an admin notice.
                return;
            }

            // Register the block type using the metadata loaded from block.json.
            // The `render` property in block.json points back to this class for the callback.
            // However, WordPress expects the render_callback to be explicitly passed if it's a class method.
            // So we will register it here directly.
            register_block_type( LACMOD_STATS_COUNTER_MODULE_FILE . '/../block/block.json', array(
                'render_callback' => array( $this, 'render_block' ),
            ) );
        }

        /**
         * Render the block on the server-side.
         *
         * @param array $attributes The block attributes.
         * @param string $content The block inner content (not used for dynamic blocks typically).
         * @return string HTML output for the stats block.
         */
        public function render_block( $attributes, $content ) {
            $title = isset( $attributes['title'] ) ? $attributes['title'] : '';
            $stats_keys_to_display = isset( $attributes['stats_to_show'] ) && is_array( $attributes['stats_to_show'] )
                                       ? $attributes['stats_to_show']
                                       : array('total_courses', 'total_users', 'active_enrolments', 'course_completions'); // Default if not set or wrong type

            if ( empty( $stats_keys_to_display ) ) {
                // This case should ideally be handled by defaults in block.json or edit component,
                // but as a fallback:
                $stats_keys_to_display = array('total_courses', 'total_users', 'active_enrolments', 'course_completions');
            }

            // Fetch the stats using the main stats counter class
            $stats_data = LACONNMOD_Stats_Counter()->get_all_stats( $stats_keys_to_display );

            if ( is_wp_error( $stats_data ) ) {
                if ( current_user_can( 'edit_posts' ) ) { // Show error to users who can edit, in context.
                    return '<div class="lac-stats-error">' . sprintf( __( 'Error fetching Moodle stats: %s', 'lmsace-connect' ), $stats_data->get_error_message() ) . '</div>';
                }
                return '<!-- Moodle stats unavailable -->';
            }

            if ( empty( $stats_data ) && empty( $stats_keys_to_display ) ) {
                 return '<div class="lac-stats-error">' . __( 'Please select statistics to display in the block settings.', 'lmsace-connect' ) . '</div>';
            }

            $align_class = isset($attributes['align']) && $attributes['align'] ? ' align' . $attributes['align'] : '';
            $wrapper_attributes = get_block_wrapper_attributes( array( 'class' => 'lac-moodle-stats-counter-block' . $align_class ) );

            $output = '<div ' . $wrapper_attributes . '>';

            if ( ! empty( $title ) ) {
                // Note: The title attribute in block.json was set with source: html and selector.
                // For a dynamic block, we typically render it directly here.
                // If you want the title to be part of the save() in JS and use source: html,
                // then you might not output it here, or adjust block.json.
                // For simplicity with server-side rendering, we output it here.
                $output .= '<h3 class="lac-stats-title-block">' . esc_html( $title ) . '</h3>';
            }

            $output .= '<ul class="lac-stats-list-block">'; // Using a different class to avoid CSS conflicts if VC and Block are on same page

            $stat_labels = array(
                'total_courses'      => __( 'Total Courses', 'lmsace-connect' ),
                'total_users'        => __( 'Total Users', 'lmsace-connect' ),
                'active_enrolments'  => __( 'Active Enrolments', 'lmsace-connect' ),
                'course_completions' => __( 'Course Completions', 'lmsace-connect' ),
            );

            foreach ( $stats_keys_to_display as $key ) {
                $key = trim( $key );
                if ( isset( $stats_data[ $key ] ) ) {
                    $label = isset( $stat_labels[ $key ] ) ? $stat_labels[ $key ] : ucfirst( str_replace( '_', ' ', $key ) );
                    $value = esc_html( $stats_data[ $key ] );
                    $output .= '<li class="lac-stat-item-block lac-stat-' . esc_attr( $key ) . '">';
                    $output .= '<span class="lac-stat-label-block">' . esc_html( $label ) . ':</span> ';
                    $output .= '<span class="lac-stat-value-block">' . $value . '</span>';
                    $output .= '</li>';
                } else {
                     $label = isset( $stat_labels[ $key ] ) ? $stat_labels[ $key ] : ucfirst( str_replace( '_', ' ', $key ) );
                     $output .= '<li class="lac-stat-item-block lac-stat-' . esc_attr( $key ) . '">';
                     $output .= '<span class="lac-stat-label-block">' . esc_html( $label ) . ':</span> ';
                     $output .= '<span class="lac-stat-value-block">' . __( 'N/A', 'lmsace-connect' ) . '</span>';
                     $output .= '</li>';
                }
            }

            $output .= '</ul>';
            $output .= '</div>';

            return $output;
        }
    }

    /**
     * Function to ensure the class is instantiated only once.
     */
    function LACONNMOD_Stats_Counter_Block() {
        return LACONNMOD_Stats_Counter_Block::instance();
    }

    // Initialize the block handler.
    LACONNMOD_Stats_Counter_Block();

}
?>
