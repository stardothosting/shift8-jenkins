<?php
/**
 * Shift8 Jenkins Settings
 *
 * Declaration of plugin settings used throughout
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    die();
}

add_action('admin_head', 'shift8_jenkins_custom_favicon');
function shift8_jenkins_custom_favicon() {
  echo '
    <style>
    .dashicons-shift8 {
        background-image: url("'. plugin_dir_url(dirname(__FILE__)) .'/img/shift8pluginicon.png");
        background-repeat: no-repeat;
        background-position: center; 
    }
    </style>
  '; 
}

// Create activity log table
add_action( 'init', 'shift8_jenkins_register_activity_log_table', 1 );
add_action( 'switch_blog', 'shift8_jenkins_register_activity_log_table' );
 
function shift8_jenkins_register_activity_log_table() {
  $installed_ver = get_option( "shift8_jenkins_db_version" );
  if ( !$installed_ver || $installed_ver != S8JENKINS_DB_VERSION ) {
    global $wpdb;

    $wpdb->S8JENKINS_TABLE = $wpdb->prefix . S8JENKINS_TABLE;
  	$sql_create_table = "CREATE TABLE {$wpdb->S8JENKINS_TABLE} (
            log_id bigint(20) unsigned NOT NULL auto_increment,
            user_name varchar(60) NOT NULL default '0',
            activity varchar(255) NOT NULL default 'updated',
            activity_date TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (log_id),
            KEY user_id (user_name)
       ) $charset_collate; ";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  	dbDelta( $sql_create_table );
    update_option("shift8_jenkins_db_version", S8JENKINS_DB_VERSION);
  }
}

// Trigger create tables on plugin activation
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

// Trigger create table check in case the db version changes
function shift8_jenkins_update_db_check() {
    if ( get_site_option( "shift8_jenkins_db_version" ) != S8JENKINS_DB_VERSION ) {
        shift8_jenkins_register_activity_log_table();
    }
}
add_action( 'plugins_loaded', 'shift8_jenkins_update_db_check' );


// create custom plugin settings menu
add_action('admin_menu', 'shift8_jenkins_create_menu');
function shift8_jenkins_create_menu() {
        //create new top-level menu
        if ( empty ( $GLOBALS['admin_page_hooks']['shift8-settings'] ) ) {
                add_menu_page('Shift8 Settings', 'Shift8', 'administrator', 'shift8-settings', 'shift8_main_page' , 'dashicons-shift8' );
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
    register_setting( 'shift8-jenkins-settings-group', 'shift8_jenkins_db_version', 'shift8_jenkins_db_version' );
}

// Uninstall hook
function shift8_jenkins_uninstall_hook() {
  // Delete setting values
  delete_option('shift8_jenkins_url');
  delete_option('shift8_jenkins_user');
  delete_option('shift8_jenkins_api');

  // Clear Cron tasks
  wp_unschedule_hook( 'shift8_jenkins_schedule_poll' );

  // Delete custom table
  global $wpdb;
  $table_name = $wpdb->prefix . S8JENKINS_TABLE;
  $sql = "DROP TABLE IF EXISTS $table_name";
  $wpdb->query($sql);
  delete_option('shift8_jenkins_db_version');

}
register_uninstall_hook( S8JENKINS_FILE, 'shift8_jenkins_uninstall_hook' );

// Deactivation hook
function shift8_jenkins_deactivation() {
  // Clear Cron tasks
  wp_unschedule_hook( 'shift8_jenkins_schedule_poll' );
}
register_deactivation_hook( S8JENKINS_FILE, 'shift8_jenkins_deactivation' );

// Validate Input for Admin options
function shift8_jenkins_url_validate($data){
	$sanitized_url = esc_url_raw($data);
	if(!empty($sanitized_url) && filter_var($sanitized_url, FILTER_VALIDATE_URL) && strlen($sanitized_url) <= 500) {
   		return $sanitized_url;
   	} else {
   		add_settings_error(
            'shift8_jenkins_url',
            'shift8-jenkins-notice',
            'You did not enter a valid URL for the Jenkins push (maximum 500 characters)',
            'error');
   		return '';
   	}
}

function shift8_jenkins_user_validate($data){
	$sanitized_data = sanitize_text_field($data);
	if(!empty($sanitized_data) && strlen($sanitized_data) <= 100) {
   		return $sanitized_data;
   	} else {
   		add_settings_error(
            'shift8_jenkins_user',
            'shift8-jenkins-notice',
            'You did not enter a valid username (maximum 100 characters)',
            'error');
   		return '';
   	}
}

function shift8_jenkins_api_validate($data){
	$sanitized_data = sanitize_text_field($data);
	if(!empty($sanitized_data) && strlen($sanitized_data) <= 200) {
   		return $sanitized_data;
   	} else {
   		add_settings_error(
            'shift8_jenkins_api',
            'shift8-jenkins-notice',
            'You did not enter a valid API key (maximum 200 characters)',
            'error');
   		return '';
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

// Force cron schedule change if detected
function shift8_jenkins_cron_validate($data){
  $cron_schedule = esc_attr($data);
  if (get_transient(S8JENKINS_CRON_SCHEDULE) && get_transient(S8JENKINS_CRON_SCHEDULE) === $cron_schedule) {
    set_transient(S8JENKINS_CRON_SCHEDULE, $cron_schedule, 0);
    return $cron_schedule;
  } else {
    set_transient(S8JENKINS_CRON_SCHEDULE, $cron_schedule, 0);
    wp_clear_scheduled_hook( 'shift8_jenkins_cron_hook' );
    return $cron_schedule;
  }
}
