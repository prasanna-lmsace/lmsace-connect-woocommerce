<?php
/**
 * LMSACE Connect - Stats Counter Visual Composer Element
 *
 * This class handles the integration of the Moodle Stats Counter
 * as a Visual Composer (WPBakery) element.
 *
 * @package LMSACE Connect
 * @subpackage StatsCounter
 * @copyright  2024 LMSACE DEV TEAM <info@lmsace.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LACONNMOD_Stats_Counter_VC' ) ) {

    /**
     * Stats Counter Visual Composer Element Class.
     */
    class LACONNMOD_Stats_Counter_VC {

        /**
         * Shortcode tag.
         * @var string
         */
        private $shortcode_tag = 'lac_moodle_stats';

        /**
         * Constructor.
         * Hooks into Visual Composer initialization.
         */
        public function __construct() {
            add_action( 'vc_before_init', array( $this, 'vc_element_map' ) );
            add_shortcode( $this->shortcode_tag, array( $this, 'render_shortcode' ) );
        }

        /**
         * Map the Visual Composer element.
         */
        public function vc_element_map() {
            if ( ! function_exists( 'vc_map' ) ) {
                return; // Visual Composer is not active.
            }

            vc_map( array(
                'name'        => __( 'Moodle Statistics Counter', 'lmsace-connect' ),
                'base'        => $this->shortcode_tag,
                'description' => __( 'Displays statistics from Moodle.', 'lmsace-connect' ),
                'category'    => __( 'LMSACE Connect', 'lmsace-connect' ),
                'icon'        => plugins_url( '../assets/img/stats-icon.png', __FILE__ ), // Placeholder icon
                'params'      => array(
                    array(
                        'type'        => 'textfield',
                        'heading'     => __( 'Title', 'lmsace-connect' ),
                        'param_name'  => 'title',
                        'description' => __( 'Enter an optional title for the stats block.', 'lmsace-connect' ),
                        'admin_label' => true,
                    ),
                    array(
                        'type'        => 'checkbox',
                        'heading'     => __( 'Statistics to Display', 'lmsace-connect' ),
                        'param_name'  => 'stats_to_show',
                        'value'       => array(
                            __( 'Total Courses', 'lmsace-connect' )          => 'total_courses',
                            __( 'Total Users', 'lmsace-connect' )            => 'total_users',
                            __( 'Active Enrolments', 'lmsace-connect' )      => 'active_enrolments',
                            __( 'Course Completions', 'lmsace-connect' )     => 'course_completions',
                            // Add more stats here as they become available from the Moodle web service
                        ),
                        'description' => __( 'Select the statistics you want to display.', 'lmsace-connect' ),
                        'admin_label' => true,
                    ),
                    // Add more parameters here if needed (e.g., layout, style)
                ),
            ) );
        }

        /**
         * Render the shortcode.
         *
         * @param array $atts Shortcode attributes.
         * @return string HTML output for the stats.
         */
        public function render_shortcode( $atts ) {
            $atts = shortcode_atts( array(
                'title'         => '',
                'stats_to_show' => '', // Comma-separated string of selected stat keys
            ), $atts, $this->shortcode_tag );

            $stats_keys_to_display = array();
            if ( ! empty( $atts['stats_to_show'] ) ) {
                $stats_keys_to_display = explode( ',', $atts['stats_to_show'] );
            }

            if ( empty( $stats_keys_to_display ) ) {
                // Default to showing all defined stats if none are specifically selected by the user in the element.
                // This requires knowing all possible keys or fetching a default set.
                // For now, let's use the keys defined in the vc_map 'value' array.
                 $stats_keys_to_display = array('total_courses', 'total_users', 'active_enrolments', 'course_completions');
            }

            // Fetch the stats using the main stats counter class
            $stats_data = LACONNMOD_Stats_Counter()->get_all_stats( $stats_keys_to_display );

            if ( is_wp_error( $stats_data ) ) {
                // Handle error: display a message or log it.
                // For admins, it might be useful to show the error.
                if ( current_user_can( 'manage_options' ) ) {
                    return '<div class="lac-stats-error">' . sprintf( __( 'Error fetching Moodle stats: %s', 'lmsace-connect' ), $stats_data->get_error_message() ) . '</div>';
                }
                return '<!-- Moodle stats unavailable -->';
            }

            if ( empty( $stats_data ) && empty( $stats_keys_to_display ) ) {
                 return '<div class="lac-stats-error">' . __( 'Please select statistics to display in the element settings.', 'lmsace-connect' ) . '</div>';
            }


            $output = '<div class="lac-moodle-stats-counter">';

            if ( ! empty( $atts['title'] ) ) {
                $output .= '<h3 class="lac-stats-title">' . esc_html( $atts['title'] ) . '</h3>';
            }

            $output .= '<ul class="lac-stats-list">';

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
                    $output .= '<li class="lac-stat-item lac-stat-' . esc_attr( $key ) . '">';
                    $output .= '<span class="lac-stat-label">' . esc_html( $label ) . ':</span> ';
                    $output .= '<span class="lac-stat-value">' . $value . '</span>';
                    $output .= '</li>';
                } else {
                     // Optionally show a message if a selected stat key returned no data
                     $label = isset( $stat_labels[ $key ] ) ? $stat_labels[ $key ] : ucfirst( str_replace( '_', ' ', $key ) );
                     $output .= '<li class="lac-stat-item lac-stat-' . esc_attr( $key ) . '">';
                     $output .= '<span class="lac-stat-label">' . esc_html( $label ) . ':</span> ';
                     $output .= '<span class="lac-stat-value">' . __( 'N/A', 'lmsace-connect' ) . '</span>';
                     $output .= '</li>';
                }
            }

            $output .= '</ul>';
            $output .= '</div>';

            return $output;
        }
    }

    // Instantiate the class to hook into VC.
    new LACONNMOD_Stats_Counter_VC();

}
?>
