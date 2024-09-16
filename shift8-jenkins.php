<?php
/**
 * Plugin Name: Shift8 Jenkins Integration
 * Plugin URI: https://github.com/stardothosting/shift8-jenkins
 * Description: Plugin that allows you to trigger a Jenkins hook straight from the Wordpress interface. This is intended for end-users to trigger a "push" for jenkins to push a staging site (for example) to production
 * Version: 2.0.15
 * Author: Shift8 Web 
 * Author URI: https://www.shift8web.ca
 * License: GPLv3
 */

// Composer dependencies
if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
    require __DIR__ . '/vendor/autoload.php';
}

require_once(plugin_dir_path(__FILE__).'shift8-jenkins-rules.php' );
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
function shift8_jenkins_settings_page() {
?>
<div class="wrap">
<h2>Shift8 Jenkins Settings</h2>
<?php if (current_user_can('administrator')) { ?>
<form method="post" action="options.php">
    <?php settings_fields( 'shift8-jenkins-settings-group' ); ?>
    <?php do_settings_sections( 'shift8-jenkins-settings-group' ); ?>
    <?php
	$locations = get_theme_mod( 'nav_menu_locations' );
	if (!empty($locations)) {
		foreach ($locations as $locationId => $menuValue) {
			if (has_nav_menu($locationId)) {
				$shift8_jenkins_menu = $locationId;
			}
		}
	}
	?>
    <table class="form-table shift8-jenkins-table">
	<tr valign="top">
    <td><span id="shift8-jenkins-notice">
    <?php 
    settings_errors('shift8_jenkins_url');
    settings_errors('shift8_jenkins_user');
    settings_errors('shift8_jenkins_api'); 
    ?>
    </span></td>
	</tr>
	<tr valign="top">
    <th scope="row">Jenkins Build Trigger URL : </th>
    <td><input type="text" name="shift8_jenkins_url" size="34" value="<?php echo (empty(esc_attr(get_option('shift8_jenkins_url'))) ? '' : esc_attr(get_option('shift8_jenkins_url'))); ?>"></td>
	</tr>
	<tr valign="top">
    <th scope="row">Jenkins Build Username : </th>
    <td><input type="text" name="shift8_jenkins_user" size="34" value="<?php echo (empty(esc_attr(get_option('shift8_jenkins_user'))) ? '' : esc_attr(get_option('shift8_jenkins_user'))); ?>"></td>
	</tr>
	<tr valign="top">
    <th scope="row">Jenkins Build API Key : </th>
    <td><input type="password" name="shift8_jenkins_api" size="34" value="<?php echo (empty(esc_attr(get_option('shift8_jenkins_api'))) ? '' : esc_attr(get_option('shift8_jenkins_api'))); ?>"></td>
	</tr>
	</table>
    <?php submit_button(); ?>
	</form>
</div>
	<div class="shift8-jenkins-button-container">
		<div class="shift8-jenkins-push-button">
			<a id="shift8-jenkins-push" href="<?php echo wp_nonce_url( admin_url('admin-ajax.php?action=shift8_jenkins_push&schedule=immediate'), 'process'); ?>"><button class="shift8-jenkins-button">Push to Production</button></a>
		</div>
	<div class="shift8-jenkins-box">
		  <select id="shift8-jenkins-push-schedule" name="shift8_jenkins_push_schedule">
		    <!--<option value="immediate">Push Immediately</option>-->
		    <option value="tonight">Tonight</option>
		    <option value="tomorrow">Tomorrow</option>
		    <option value="two_days">Two Days from now</option>
		    <option value="three_days">Three Days from now</option>
		    <option value="four_days">Four Days from now</option>
		    <option value="five_days">Five Days from now</option>
		    <option value="six_days">Six Days from now</option>
		    <option value="seven_days">Seven Days from now</option>
		  </select>
	</div>
		<div class="shift8-jenkins-push-container">
			<div class="shift8-jenkins-push-progress"></div>
		</div>
	</div>
	<div class="shift8-jenkins-activity-log-container">
		<h3>Activity Log</h3>
	<div class="shift8-jenkins-scrollabletextbox">
		<ul>
		<?php
		$activity_log_array = shift8_jenkins_get_activity_log();
		if ($activity_log_array) {
			foreach ($activity_log_array as $activity_log) {
				echo '<li>' . $activity_log->user_name . ' ' . $activity_log->activity . ' on ' . $activity_log->activity_date . '</li>';
			}
		}
		?>
		</ul>
	</div></div>
<?php 
	} // is_admin
}


