<?php

// Create activity log table
add_action( 'init', 'shift8_jenkins_register_activity_log_table', 1 );
add_action( 'switch_blog', 'shift8_jenkins_register_activity_log_table' );
 
function shift8_jenkins_register_activity_log_table() {
    global $wpdb;
    global $shift8_jenkins_table_name;

    $wpdb->$shift8_jenkins_table_name = $wpdb->prefix . $shift8_jenkins_table_name;
	$sql_create_table = "CREATE TABLE {$wpdb->$shift8_jenkins_table_name} (
          log_id bigint(20) unsigned NOT NULL auto_increment,
          user_name varchar(60) NOT NULL default '0',
          activity varchar(20) NOT NULL default 'updated',
          activity_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY  (log_id),
          KEY user_id (user_name)
     ) $charset_collate; ";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql_create_table );
}

function shift8_jenkins_create_tables() {
    // Code for creating a table goes here
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	global $wpdb;
	global $charset_collate;
	// Call this manually as we may have missed the init hook
	shift8_jenkins_register_activity_log_table();
}
// Create tables on plugin activation
register_activation_hook( __FILE__, 'shift8_jenkins_create_tables' );

// create custom plugin settings menu
add_action('admin_menu', 'shift8_jenkins_create_menu');
function shift8_jenkins_create_menu() {
        //create new top-level menu
        if ( empty ( $GLOBALS['admin_page_hooks']['shift8-settings'] ) ) {
                add_menu_page('Shift8 Settings', 'Shift8', 'administrator', 'shift8-settings', 'shift8_main_page' , 'dashicons-building' );
        }
        add_submenu_page('shift8-settings', 'Jenkins Settings', 'Jenkins Settings', 'manage_options', __FILE__.'/custom', 'shift8_jenkins_settings_page');
        //call register settings function
        add_action( 'admin_init', 'register_shift8_jenkins_settings' );
}

// Register admin settings
function register_shift8_jenkins_settings() {
    //register our settings
    register_setting( 'shift8-jenkins-settings-group', 'shift8_jenkins_url', 'shift8_jenkins_url_validate' );
    register_setting( 'shift8-jenkins-settings-group', 'shift8_jenkins_user', 'shift8_jenkins_user_validate' );
    register_setting( 'shift8-jenkins-settings-group', 'shift8_jenkins_api', 'shift8_jenkins_api_validate' );
}

// Validate Input for Admin options
function shift8_jenkins_url_validate($data){
	if(filter_var($data, FILTER_VALIDATE_URL,FILTER_FLAG_QUERY_REQUIRED)) {
   		return $data;
   	} else {
   		add_settings_error(
            'shift8_jenkins_url',
            'shift8-jenkins-notice',
            'You did not enter a valid URL for the Jenkins push',
            'error');
   	}
}

function shift8_jenkins_user_validate($data){
	if(filter_var($data, FILTER_SANITIZE_STRING)) {
   		return $data;
   	} else {
   		add_settings_error(
            'shift8_jenkins_user',
            'shift8-jenkins-notice',
            'You did not enter a valid string for the username field',
            'error');
   	}
}

function shift8_jenkins_api_validate($data){
	if(filter_var($data, FILTER_SANITIZE_STRING)) {
   		return $data;
   	} else {
   		add_settings_error(
            'shift8_jenkins_api',
            'shift8-jenkins-notice',
            'You did not enter a valid string for the API field',
            'error');
   	}
}

// Validate admin options
function shift8_jenkins_check_options() {
    // If enabled is not set
    if(empty(esc_attr(get_option('shift8_jenkins_url') ))) return false;
    if(empty(esc_attr(get_option('shift8_jenkins_api') ))) return false;
    if(empty(esc_attr(get_option('shift8_jenkins_user') ))) return false;

    return true;

}