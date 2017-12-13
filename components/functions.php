<?php

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
    if ( wp_verify_nonce($_GET['_wpnonce'], 'process') && $_GET['action'] == 'shift8_jenkins_push') {
        shift8_jenkins_poll();
        die();
    } else {
        die();
    }
}

// Handle the actual jenkins GET
function shift8_jenkins_poll() {
    if (current_user_can('administrator') && shift8_jenkins_check_options()) {
        global $wpdb;
        global $shift8_jenkins_table_name;
        $current_user = wp_get_current_user();

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
        if (is_array($response) && $response['response']['code'] == '200') {
            echo $date->format('Y-m-d H:i:s') . ' / ' . $current_user->user_login . ' : Pushed to production';
            $wpdb->insert( 
                $wpdb->prefix . $shift8_jenkins_table_name,
                array( 
                    'user_id' => $current_user->user_login,
                    'activity' => 'pushed to production',
                )
            );
        } else {
            echo 'error_detected : ';
            if (is_array($response['response'])) {
                echo $response['response']['code'] . ' - ' . $response['response']['message'];
            } else {
                echo 'unknown';
            }
        } 
    } 
}

// Get the log entriees 
function shift8_jenkins_get_activity_log() {
    global $wpdb;
    global $shift8_jenkins_table_name;

    $table_name = $wpdb->prefix . $shift8_jenkins_table_name;
    $activity_log_array = $wpdb->get_results("SELECT * FROM $table_name ORDER BY activity_date DESC");
    return $activity_log_array;
}