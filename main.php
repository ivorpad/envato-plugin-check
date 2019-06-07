<?php
function check_main_plugin($plugin) {
	global $themechecks, $data, $themename;

	$plugin_path = WP_PLUGIN_DIR .'/'. plugin_basename($plugin[0]);
	$files = listdir($plugin_path);
	
	if( $files ) {
		foreach( $files as $key => $filename ) {
			if (strpos($filename, 'tgm-plugin-activation') === false && strpos($filename, 'merlin') === false) {
				if (substr($filename, -4) == '.php' && !is_dir($filename)) {
						$php[$filename] = file_get_contents($filename);
						$php[$filename] = tc_strip_comments($php[$filename]);
				} else if (substr($filename, -4) == '.css' && !is_dir($filename)) {
						$css[$filename] = file_get_contents($filename);
				} else {
						$other[$filename] = (!is_dir($filename)) ? file_get_contents($filename) : '';
				}
			}
		}
	}

	$success = run_themechecks($php, $css, $other);

	global $checkcount;

	// second loop, to display the errors
	echo '<h2>' . __('Plugin Info', 'theme-check') . ': </h2>';
	echo '<div class="theme-info">';
	

	$data = get_plugin_data(WP_PLUGIN_DIR . "/" . $plugin[1], false, false);

	echo (!empty($data['Title'])) ? '<p><label>' . __('Title', 'theme-check') . '</label><span class="info">' . $data['Title'] . '</span></p>' : '';
	echo (!empty($data['Version'])) ? '<p><label>' . __('Version', 'theme-check') . '</label><span class="info">' . $data['Version'] . '</span></p>' : '';
	echo (!empty($data['AuthorName'])) ? '<p><label>' . __('Author', 'theme-check') . '</label><span class="info">' . $data['AuthorName'] . '</span></p>' : '';
	echo (!empty($data['AuthorURI'])) ? '<p><label>' . __('Author URI', 'theme-check') . '</label><span class="info"><a href="' . $data['AuthorURI'] . '">' . $data['AuthorURI'] . '</a>' . '</span></p>' : '';
	echo (!empty($data['PluginURI'])) ? '<p><label>' . __('Plugin URI', 'theme-check') . '</label><span class="info"><a href="' . $data['PluginURI'] . '">' . $data['PluginURI'] . '</a>' . '</span></p>' : '';
	echo (!empty($data['Description'])) ? '<p><label>' . __('Description', 'theme-check') . '</label><span class="info">' . $data['Description'] . '</span></p>' : '';

	echo '<p>' . sprintf(
			__(' Running %1$s tests against %2$s', 'theme-check'),
			'<strong>' . $checkcount . '</strong>',
			'<strong>' . $data['Title'] . '</strong>'
	) . '</p>';
	$results = display_themechecks();
	if (!$success) {
			echo '<h2>' . sprintf(__('One or more errors were found for %1$s.', 'theme-check'), $data['Title']) . '</h2>';
	} else {
			echo '<h2>' . sprintf(__('%1$s passed the tests', 'theme-check'), $data['Title']) . '</h2>';
			tc_success();
	}
	if (!defined('WP_DEBUG') || WP_DEBUG == false) {
			echo '<div class="updated"><span class="tc-fail">' . __('WARNING', 'theme-check') . '</span> ' . __('<strong>WP_DEBUG is not enabled!</strong> Please test your theme with <a href="https://codex.wordpress.org/Editing_wp-config.php">debug enabled</a> before you upload!', 'theme-check') . '</div>';
	}

	echo '<div class="tc-box">';
	echo '<ul class="tc-result">';
	echo $results;
	echo '</ul></div>';
}

// strip comments from a PHP file in a way that will not change the underlying structure of the file
function tc_strip_comments( $code ) {
	$strip = array( T_COMMENT => true, T_DOC_COMMENT => true);
	$newlines = array( "\n" => true, "\r" => true );
	$tokens = token_get_all($code);
	reset($tokens);
	$return = '';
	$token = current($tokens);
	while( $token ) {
		if( !is_array($token) ) {
			$return.= $token;
		} elseif( !isset( $strip[ $token[0] ] ) ) {
			$return.= $token[1];
		} else {
			for( $i = 0, $token_length = strlen($token[1]); $i < $token_length; ++$i )
			if( isset($newlines[ $token[1][$i] ]) )
			$return.= $token[1][$i];
		}
		$token = next($tokens);
	}
	return $return;
}


function tc_intro() {
?>
	<h2><?php _e( 'About', 'theme-check' ); ?></h2>
	<p><?php _e( "The Envato Plugin Check plugin is an easy way to test your theme and make sure it's up to date with the latest Themeforest review standards. With it, you can run all the same automated testing tools on your theme that Themeforest Reviewers use for theme submissions.", 'theme-check' ); ?></p>
	<?php
}

function tc_success() {
	?>
	<div class="tc-success">
		<p><?php _e( 'Congratulations! Your theme has passed the basic tests.', 'theme-check' ); ?></p>

		<p><?php _e( 'Next, import the <a href="//codex.wordpress.org/Theme_Unit_Test">WordPress Theme Unit Test Data</a> and ensure all default content is properly formatted.', 'theme-check' ); ?></p>

		<p><?php _e( 'Finally, review the <a href="//help.market.envato.com/hc/en-us/articles/202822450-WordPress-Theme-Submission-Requirements">WordPress Theme Submission Requirements</a> before uploading your WordPress Theme.', 'theme-check' ); ?></p>
	</div>
	<?php
}

function tc_form_envato_plugin_check() {
	$plugins = tc_get_plugins();

		echo '<form action="themes.php?page=envato_plugin_check" method="post">';
		echo '<select name="pluginname">';
		foreach( $plugins as $name => $info ) {

			// var_dump(get_plugin_data(WP_PLUGIN_DIR . '/' . $info['location'], false, false));

			echo '<option ';
echo 'value="' . $info["dirname"] . ' | ' . $info["location"] . ' " style="font-weight:bold;">' . $info["name"] . '</option>';
		}
		
		echo '</select>';
		echo '<input class="button" type="submit" value="submit" />';
		if ( defined( 'TC_PRE' ) || defined( 'TC_POST' ) ) echo ' <input name="trac" type="checkbox" /> ' . __( 'Output in Trac format.', 'theme-check' );
		echo '<input name="s_info" type="checkbox" /> ' . __( 'Suppress INFO.', 'theme-check' );
		echo '</form>';
}

function tc_form() {
	$themes = tc_get_themes();

	echo '<form action="themes.php?page=envato_plugin_check" method="post">';
	echo '<select name="themename">';
	foreach( $themes as $name => $location ) {
		echo '<option ';
		if ( isset( $_POST['themename'] ) ) {
			echo ( $location['Stylesheet'] === $_POST['themename'] ) ? 'selected="selected" ' : '';
		} else {
			echo ( basename( STYLESHEETPATH ) === $location['Stylesheet'] ) ? 'selected="selected" ' : '';
		}
		echo ( basename( STYLESHEETPATH ) === $location['Stylesheet'] ) ? 'value="' . $location['Stylesheet'] . '" style="font-weight:bold;">' . $name . '</option>' : 'value="' . $location['Stylesheet'] . '">' . $name . '</option>';
	}
	echo '</select>';
	echo '<input class="button" type="submit" value="' . __( 'Check it out!', 'theme-check' ) . '" />';
	if ( defined( 'TC_PRE' ) || defined( 'TC_POST' ) ) echo ' <input name="trac" type="checkbox" /> ' . __( 'Output in Trac format.', 'theme-check' );
	echo '<input name="s_info" type="checkbox" /> ' . __( 'Suppress INFO.', 'theme-check' );
	echo '</form>';
}
