<?php
/**
 * Plugin Name: Shift8 Jenkins Integration
 * Plugin URI: https://github.com/stardothosting/shift8-jenkins
 * Description: Plugin that allows you to trigger a Jenkins hook straight from the Wordpress interface. This is intended for end-users to trigger a "push" for jenkins to push a staging site (for example) to production
 * Version: 1.00
 * Author: Shift8 Web 
 * Author URI: https://www.shift8web.ca
 * License: GPLv3
 */

require_once(plugin_dir_path(__FILE__).'components/enqueuing.php' );
require_once(plugin_dir_path(__FILE__).'components/settings.php' );
require_once(plugin_dir_path(__FILE__).'components/functions.php' );


// Admin welcome page
if (!function_exists('shift8_main_page')) {
	function shift8_main_page() {
	?>
	<div class="wrap">
	<h2>Shift8 Plugins</h2>
	Shift8 is a Toronto based web development and design company. We specialize in Wordpress development and love to contribute back to the Wordpress community whenever we can! You can see more about us by visiting <a href="https://www.shift8web.ca" target="_new">our website</a>.
	</div>
	<?php
	}
}

// Admin settings page
function shift8_geoip_settings_page() {
?>
<div class="wrap">
<h2>Shift8 Geo IP Location Settings</h2>
<?php if (is_admin()) { ?>
<form method="post" action="options.php">
    <?php settings_fields( 'shift8-jenkins-settings-group' ); ?>
    <?php do_settings_sections( 'shift8-jenkins-settings-group' ); ?>
    <?php
	$locations = get_theme_mod( 'nav_menu_locations' );
	if (!empty($locations)) {
		foreach ($locations as $locationId => $menuValue) {
			if (has_nav_menu($locationId)) {
				$shift8_geoip_menu = $locationId;
			}
		}
	}
	?>
    <table class="form-table shift8-jenkins-table">
	<tr valign="top">
	<th scope="row">Core Settings</th>
	</tr>
	<tr valign="top">
    <td><span id="shift8-jenkins-notice">
    </span></td>
	</tr>
	<tr valign="top">
	<td>Enable GEO IP Location : </td>
	<td>
	<?php 
	if (esc_attr( get_option('shift8_geoip_enabled') ) == 'on') { 
		$enabled_checked = "checked";
	} else {
		$enabled_checked = "";
	}
	?>
    <label class="switch">
    <input type="checkbox" name="shift8_geoip_enabled" <?php echo $enabled_checked; ?>>
    <div class="slider round"></div>
    </label>
	</td>
	</tr>
	</table>
    <?php submit_button(); ?>
</form>
</div>
<?php 
	} // is_admin
}

// add the menu if its switched on
if (esc_attr( get_option('shift8_geoip_enabled') ) == 'on') {
	add_action('wp_footer', 'add_shift8_geoip_menu', 1);
}

