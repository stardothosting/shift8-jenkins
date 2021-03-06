<?php
/**
 * Shift8 Jenkins Main Functions
 *
 * Collection of functions used throughout the operation of the plugin
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    die();
}

use Carbon\Carbon;
use Carbon\CarbonTimeZone;



// Function to encrypt session data
function shift8_jenkins_encrypt($key, $payload) {
    if (!empty($key) && !empty($payload)) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($payload, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    } else {
        return false;
    }
}

// Function to decrypt session data
function shift8_jenkins_decrypt($key, $garble) {
    if (!empty($key) && !empty($garble)) {
        list($encrypted_data, $iv) = explode('::', base64_decode($garble), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
    } else {
        return false;
    }
}

// Handle the ajax trigger
add_action( 'wp_ajax_shift8_jenkins_push', 'shift8_jenkins_push' );
function shift8_jenkins_push() {
    if (current_user_can('administrator') && shift8_jenkins_check_options()) {
        $user_pushed = wp_get_current_user();
        if ( wp_verify_nonce($_GET['_wpnonce'], 'process') && $_GET['action'] == 'shift8_jenkins_push' && $_GET['schedule'] == 'immediate') {
            shift8_jenkins_poll($user_pushed);
            die();
        } else if ( wp_verify_nonce($_GET['_wpnonce'], 'process') && $_GET['action'] == 'shift8_jenkins_push' && $_GET['schedule'] !== 'immediate') {
            switch ($_GET['schedule']) {
                case 'tonight':
                    shift8_jenkins_schedule_push(Carbon::now()->setTime(23,30,0)->timestamp, $user_pushed);
                    die();
                    break;
                case 'tomorrow':
                    shift8_jenkins_schedule_push(Carbon::now()->setTime(23,30,0)->add(1, 'day')->timestamp, $user_pushed);
                    die();
                    break;
                case 'two_days':
                    shift8_jenkins_schedule_push(Carbon::now()->setTime(23,30,0)->add(2, 'day')->timestamp, $user_pushed);
                    die();
                    break;
                case 'three_days':
                    shift8_jenkins_schedule_push(Carbon::now()->setTime(23,30,0)->add(3, 'day')->timestamp, $user_pushed);
                    die();
                    break;
                case 'four_days':
                    shift8_jenkins_schedule_push(Carbon::now()->setTime(23,30,0)->add(4, 'day')->timestamp, $user_pushed);
                    die();
                    break;
                case 'five_days':
                    shift8_jenkins_schedule_push(Carbon::now()->setTime(23,30,0)->add(5, 'day')->timestamp, $user_pushed);
                    die();
                    break;
                case 'six_days':
                    shift8_jenkins_schedule_push(Carbon::now()->setTime(23,30,0)->add(6, 'day')->timestamp, $user_pushed);
                    die();
                    break;
                case 'seven_days':
                    shift8_jenkins_schedule_push(Carbon::now()->setTime(23,30,0)->add(7, 'day')->timestamp, $user_pushed);
                    die();
                    break;
                default:
                    die();
                    break;
            }
        } else {
            die();
        }
    } else {
        die();
    }
}

// Handle the actual jenkins GET
function shift8_jenkins_poll($user_pushed) {
    $jenkins_user = esc_attr(get_option('shift8_jenkins_user'));
    $jenkins_api = esc_attr(get_option('shift8_jenkins_api'));
    // Set headers for WP Remote get
    $headers = array(
        'Content-type: application/json',
        'Authorization' => 'Basic ' . base64_encode($jenkins_user . ':' . $jenkins_api),
    );

    // Use WP Remote Get to poll jenkins
    $response = wp_remote_get( esc_attr(get_option('shift8_jenkins_url')),
        array(
            'headers' => $headers,
            'httpversion' => '1.1',
            'timeout' => '10',
        )
    );
    if (is_array($response) && $response['response']['code'] == '201') {
        $date = date('Y-m-d H:i:s');
        echo $date . ' / ' . $user_pushed->user_login . ' : Pushed to production';
        shift8_jenkins_activity_log($user_pushed->user_login, 'pushed to production');
    } else {
        echo 'error_detected : ';
        if (is_array($response['response'])) {
            echo $response['response']['code'] . ' - ' . $response['response']['message'];
        } else {
            echo 'unknown';
        }
    } 
}
// Setup the action that may be used as a one-off scheduled job
add_action( 'shift8_jenkins_schedule_poll', 'shift8_jenkins_poll', 10, 1 );

// Get the log entriees 
function shift8_jenkins_get_activity_log() {
    if (current_user_can('administrator')) {
        global $wpdb;
        $table_name = $wpdb->prefix . S8JENKINS_TABLE;
        $activity_log_array = $wpdb->get_results("SELECT * FROM $table_name ORDER BY activity_date DESC");
        return $activity_log_array;
    } else {
        return null;
    }
}

// One time schedule action for jenkins poll
function shift8_jenkins_schedule_push($schedule, $user_pushed) {
    if (current_user_can('administrator') && shift8_jenkins_check_options() && $schedule) {
        $schedule_human = Carbon::createFromTimestamp($schedule)->toDateTimeString();

        // Set the cron schedule
        shift8_jenkins_activity_log($user_pushed->user_login, 'scheduled push for ' . $schedule_human);
        if ( ! wp_next_scheduled( 'shift8_jenkins_schedule_poll', array ( $user_pushed ) ) ) {
            wp_schedule_single_event( $schedule, 'shift8_jenkins_schedule_poll', array($user_pushed));
        } else {
            shift8_jenkins_activity_log($user_pushed->user_login, 'cleaning up previously scheduled push');
            wp_unschedule_hook( 'shift8_jenkins_schedule_poll' );
            wp_schedule_single_event( $schedule, 'shift8_jenkins_schedule_poll', array($user_pushed));
        }
        // Display notice and log activity
        echo 'Schedule initiated to push on ' . $schedule_human;
    } else {
        echo 'An unknown error occurred while scheduling the push.';
    }
}

// Function to handle activity logging
function shift8_jenkins_activity_log($log_user, $log_activity) {
    $log_date = Carbon::now()->format('Y-m-d H:i:s');
    global $wpdb;
    $wpdb->insert( 
            $wpdb->prefix . S8JENKINS_TABLE,
            array( 
                'user_name' => sanitize_text_field($log_user),
                'activity' => sanitize_text_field($log_activity),
                'activity_date' => $log_date,
            )
        );
}
