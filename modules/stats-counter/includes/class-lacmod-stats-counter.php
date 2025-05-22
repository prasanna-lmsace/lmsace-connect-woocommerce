<?php
/**
 * LMSACE Connect - Stats Counter Module Main Class
 *
 * This class handles the core logic for the Stats Counter module,
 * including fetching statistics from Moodle, caching them, and providing
 * methods to access these statistics.
 *
 * @package LMSACE Connect
 * @subpackage StatsCounter
 * @copyright  2024 LMSACE DEV TEAM <info@lmsace.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'LACONNMOD_Stats_Counter' ) ) {

    /**
     * Main Stats Counter Class.
     */
    class LACONNMOD_Stats_Counter extends LACONN_Main {

        /**
         * Instance of this class.
         *
         * @var LACONNMOD_Stats_Counter
         */
        protected static $instance = null;

        /**
         * Cache key for storing Moodle stats.
         *
         * @var string
         */
        const STATS_CACHE_KEY = 'lac_moodle_stats_cache';

        /**
         * Cache expiration time in seconds (e.g., 1 hour).
         *
         * @var int
         */
        const STATS_CACHE_EXPIRATION = HOUR_IN_SECONDS;

        /**
         * Moodle web service function name for fetching stats.
         *
         * @var string
         */
        const MOODLE_STATS_WS_FUNCTION = 'local_lmsaceconnect_get_stats'; // As discussed.

        /**
         * Return an instance of this class.
         *
         * @return LACONNMOD_Stats_Counter A single instance of this class.
         */
        public static function instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Constructor.
         */
        protected function __construct() {
            // You can add initialization hooks here if needed, e.g., for admin settings.
            // parent::__construct(); // Call parent constructor if LACONN_Main has one that needs to be called.
        }

        /**
         * Initialize the module - register hooks, etc.
         */
        public function init() {
            // Add actions or filters needed for the module.
            // For example, registering the shortcode for the VC element will go here or in the VC class.
        }

        /**
         * Fetches all specified statistics from Moodle.
         *
         * @param array $stat_keys An array of statistic keys to fetch (e.g., ['total_courses', 'total_users']).
         * @return array|WP_Error An array of statistics on success, or WP_Error on failure.
         */
        public function fetch_stats_from_moodle( array $stat_keys ) {
            if ( empty( $stat_keys ) ) {
                return new WP_Error( 'no_stats_requested', __( 'No statistics were requested.', 'lmsace-connect' ) );
            }

            $params = array(
                'statkeys' => $stat_keys
            );

            // Get the LACONN_Client instance.
            $client = LACONN_Client::instance();

            // Make the API call.
            $response = $client->request( self::MOODLE_STATS_WS_FUNCTION, $params );

            if ( is_wp_error( $response ) ) {
                // Log error: LACONN_Log::add(self::MOODLE_STATS_WS_FUNCTION, 'Error fetching Moodle stats: ' . $response->get_error_message());
                return $response;
            }

            if ( ! is_array( $response ) ) {
                // Log error: LACONN_Log::add(self::MOODLE_STATS_WS_FUNCTION, 'Unexpected response format from Moodle stats API.');
                return new WP_Error( 'invalid_response_format', __( 'Invalid response format from Moodle.', 'lmsace-connect' ) );
            }

            // Potentially, Moodle might return an error structure within the success response.
            // This depends on how the Moodle web service is implemented.
            // e.g., if (isset($response['errorcode'])) { return new WP_Error($response['errorcode'], $response['message']); }

            return $response; // Assuming $response is the array like {'total_courses': 10, ...}
        }

        /**
         * Gets a specific statistic value.
         *
         * @param string $stat_key The key of the statistic to retrieve (e.g., 'total_courses').
         * @param array  $default_stats_to_fetch Default stats to fetch if cache is empty.
         * @return mixed|null The statistic value, or null if not found or error.
         */
        public function get_stat( $stat_key, $default_stats_to_fetch = array('total_courses', 'total_users', 'active_enrolments', 'course_completions') ) {
            $all_stats = $this->get_all_stats( $default_stats_to_fetch );

            if ( is_wp_error( $all_stats ) ) {
                return null; // Or handle error appropriately.
            }

            return isset( $all_stats[ $stat_key ] ) ? $all_stats[ $stat_key ] : null;
        }

        /**
         * Gets all statistics, using cache if available.
         *
         * @param array $stats_to_fetch An array of statistic keys to fetch if the cache is empty or stale.
         * @return array|WP_Error An array of all statistics or WP_Error on failure.
         */
        public function get_all_stats( array $stats_to_fetch ) {
            $cached_stats = get_transient( self::STATS_CACHE_KEY );

            if ( false === $cached_stats ) {
                $stats = $this->fetch_stats_from_moodle( $stats_to_fetch );

                if ( is_wp_error( $stats ) ) {
                    return $stats; // Return WP_Error to be handled by the caller.
                }
                set_transient( self::STATS_CACHE_KEY, $stats, self::STATS_CACHE_EXPIRATION );
                return $stats;
            } else {
                // Optional: Check if the cached stats contain all $stats_to_fetch.
                // If not, you might want to fetch the missing ones or refresh all.
                // For simplicity now, we assume if cache exists, it's sufficient.
            }
            return $cached_stats;
        }

        /**
         * Clears the Moodle statistics cache.
         */
        public function clear_stats_cache() {
            delete_transient( self::STATS_CACHE_KEY );
        }

    }

}

/**
 * Helper function to access the main instance of the class.
 *
 * @return LACONNMOD_Stats_Counter
 */
function LACONNMOD_Stats_Counter() {
    return LACONNMOD_Stats_Counter::instance();
}

?>
