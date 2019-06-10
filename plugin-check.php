<?php
/*
Plugin Name: Envato Plugin Check
Plugin URI: https://github.com/ivorpad/envato-plugin-check
Description: Envato Plugin Check is a fork of Envato Theme Check with additional CodeCanyon specific checks.
Author: Scott Parry & Ivor Padilla
Author URI: https://envato.com
Version: 20190610
Text Domain: plugin-check
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// if ( defined( 'WP_CLI' ) && WP_CLI ) {
// TODO: Disabled CLI for now
// 	include 'plugin-check-cli.php';
// }

class Envatoplugincheck {
	function __construct() {
		add_action( 'admin_init', array( $this, 'tc_i18n' ) );
		add_action( 'admin_menu', array( $this, 'plugincheck_add_page' ) );
	}

	function tc_i18n() {
		load_plugin_textdomain( 'plugin-check', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/'  );
	}

	function load_styles() {
		wp_enqueue_style('style', plugins_url( 'assets/style.css', __FILE__ ), null, null, 'screen');
	}

	function plugincheck_add_page() {
		$page = add_plugins_page( 'Envato Plugin Check', 'Envato Plugin Check', 'manage_options', 'envato_plugin_check', array( $this, 'plugincheck_do_page' ) );
		add_action('admin_print_styles-' . $page, array( $this, 'load_styles' ) );
	}

	function plugincheck_do_page() {
		if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'plugin-check' ) );
		}

		include 'checkbase.php';
		include 'main.php';

		?>
		<div id="plugin-check" class="wrap">

		<h1><?php _ex( 'Envato Plugin Check', 'title of the main page', 'plugin-check' ); ?></h1>
		<div class="plugin-check">
		<?php
			tc_form_envato_plugin_check();
		if ( !isset( $_POST[ 'pluginname' ] ) )  {
			tc_intro();

		}

		if( isset( $_POST[ 'pluginname' ] ) ) {
			$plugin = array_map('trim', explode('|', $_POST['pluginname']));
			check_main_plugin( $plugin );
		}

		?>
		</div> <!-- .plugin-check-->
		</div>
		<?php
	}
}
new Envatoplugincheck;
