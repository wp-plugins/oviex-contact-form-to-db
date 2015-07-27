<?php 
/*
Plugin Name: Oviex Contact Form to DB
Plugin URI: http://oviex.com
Description: Oviex Contact form to DB is an addon to Oviex Contact Form to save all submitted contact form entries into database and show them in admin panel for site owner's ease.
Version: 1.0
Author: Umang Goyal
Author URI: http://oviex.com
*/

global $ocfdb_version;
$ocfdb_version = '1.0';
define('OCFPATH',trailingslashit(plugin_dir_path(__FILE__)),true);


add_action( 'plugins_loaded', 'ocfdb_check_dependencies' );
function ocfdb_check_dependencies(){
if (function_exists('ocf_form')){
require_once( OCFPATH.'functions.php');	
}
else{
		if ( current_user_can( 'activate_plugins' ) ) {
				  add_action( 'admin_init', 'ocfdb_plugin_deactivate' );
				  add_action( 'admin_notices', 'ocfdb_admin_notice' );
				}
		}
} 
/*create our database table on plugin activation */

function ocfdb_activ_func(){
        global $wpdb;
		global $ocfdb_version;
		$entries_table = $wpdb->prefix . 'oviex_contact_form';
		$query ="CREATE TABLE IF NOT EXISTS $entries_table (
				id INT NOT NULL AUTO_INCREMENT ,
				first_name TEXT NOT NULL ,
				last_name TEXT NOT NULL ,
				phone TEXT NOT NULL ,
				email VARCHAR( 255 ) NOT NULL ,
				comment VARCHAR( 255 ) NOT NULL ,
				ip VARCHAR( 255 ) NOT NULL ,
				date_submitted VARCHAR( 255 ) NOT NULL,
				PRIMARY KEY  (id)
				);";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $query );
		update_option("ocfdb_version", $ocfdb_version);
}
register_activation_hook( __FILE__, 'ocfdb_activ_func' );
/*deactivation of plugin if dependencies not found. */
function ocfdb_plugin_deactivate() {
	deactivate_plugins( plugin_basename( __FILE__ ) );
}
function ocfdb_admin_notice() {
  echo '<div id="message" class="error"><p><strong>Oviex Contact Form</strong> is required and activated to run this plugin. The plug-in has been <strong>deactivated</strong>.</p></div>';
  if ( isset( $_GET['activate'] ) )
	unset( $_GET['activate'] );
}

?>