<?php
/**
 * Shift8 Enqueuing Files
 *
 * Function to load styles and front end scripts
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    die();
}

// Register admin scripts for custom fields
function load_shift8_jenkins_wp_admin_style() {
        // admin always last
        wp_enqueue_style( 'shift8_jenkins_css', plugin_dir_url(dirname(__FILE__)) . 'css/shift8_jenkins_admin.css', array(), '1.1.5' );
        wp_enqueue_script( 'shift8_jenkins_script', plugin_dir_url(dirname(__FILE__)) . 'js/shift8_jenkins_admin.js', array(), '1.1.2' );
        wp_localize_script( 'shift8_jenkins_script', 'the_ajax_script', array( 
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( "shift8_jenkins_response_nonce"),
        ));  
}
add_action( 'admin_enqueue_scripts', 'load_shift8_jenkins_wp_admin_style' );
