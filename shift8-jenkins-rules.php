<?php
/**
 * Shift8 Jenkins Define rules
 *
 * Defined rules used throughout the plugin operations
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    die();
}

if ( !defined( 'S8JENKINS_FILE' ) )
define( 'S8JENKINS_FILE', 'shift8-jenkins/shift8-jenkins.php' );

if ( !defined( 'S8JENKINS_DIR' ) )
    define( 'S8JENKINS_DIR', realpath( dirname( __FILE__ ) ) );

if ( !defined( 'S8JENKINS_TABLE' ) )
	define( 'S8JENKINS_TABLE', 'jenkins_activity_log' );

if ( !defined( 'S8JENKINS_DB_VERSION' ) )
	define( 'S8JENKINS_DB_VERSION', '1.0' );