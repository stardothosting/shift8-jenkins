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

define( 'S8JENKINS_FILE', 'shift8-jenkins/shift8-jenkins.php' );

if ( !defined( 'S8JENKINS_DIR' ) )
    define( 'S8JENKINS_DIR', realpath( dirname( __FILE__ ) ) );
