<?php
function check_main_plugin($plugin) {
	global $pluginchecks, $data;

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

	$success = run_pluginchecks($php, $css = [], $other);

	global $checkcount;

	// second loop, to display the errors
	echo '<h2>' . __('Plugin Info', 'plugin-check') . ': </h2>';
	echo '<div class="plugin-info">';
	

	$data = get_plugin_data(WP_PLUGIN_DIR . "/" . $plugin[1], false, false);

	echo (!empty($data['Title'])) ? '<p><label>' . __('Title', 'plugin-check') . '</label><span class="info">' . $data['Title'] . '</span></p>' : '';
	echo (!empty($data['Version'])) ? '<p><label>' . __('Version', 'plugin-check') . '</label><span class="info">' . $data['Version'] . '</span></p>' : '';
	echo (!empty($data['AuthorName'])) ? '<p><label>' . __('Author', 'plugin-check') . '</label><span class="info">' . $data['AuthorName'] . '</span></p>' : '';
	echo (!empty($data['AuthorURI'])) ? '<p><label>' . __('Author URI', 'plugin-check') . '</label><span class="info"><a href="' . $data['AuthorURI'] . '">' . $data['AuthorURI'] . '</a>' . '</span></p>' : '';
	echo (!empty($data['PluginURI'])) ? '<p><label>' . __('Plugin URI', 'plugin-check') . '</label><span class="info"><a href="' . $data['PluginURI'] . '">' . $data['PluginURI'] . '</a>' . '</span></p>' : '';
	echo (!empty($data['Description'])) ? '<p><label>' . __('Description', 'plugin-check') . '</label><span class="info">' . $data['Description'] . '</span></p>' : '';

	echo '<p>' . sprintf(
			__(' Running %1$s tests against %2$s', 'plugin-check'),
			'<strong>' . $checkcount . '</strong>',
			'<strong>' . $data['Title'] . '</strong>'
	) . '</p>';
	$results = display_pluginchecks();
	if (!$success) {
			echo '<h2>' . sprintf(__('One or more errors were found for %1$s.', 'plugin-check'), $data['Title']) . '</h2>';
	} else {
			echo '<h2>' . sprintf(__('%1$s passed the tests', 'plugin-check'), $data['Title']) . '</h2>';
			tc_success();
	}
	if (!defined('WP_DEBUG') || WP_DEBUG == false) {
			echo '<div class="updated"><span class="tc-fail">' . __('WARNING', 'plugin-check') . '</span> ' . __('<strong>WP_DEBUG is not enabled!</strong> Please test your plugin with <a href="https://codex.wordpress.org/Editing_wp-config.php">debug enabled</a> before you upload!', 'plugin-check') . '</div>';
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
	<h2><?php _e( 'About', 'plugin-check' ); ?></h2>
	<p><?php _e( "The Envato Plugin Check plugin is an easy way to test your plugin and make sure it's up to date with the latest CodeCanyon review standards. With it, you can run all the same automated testing tools on your plugin that CodeCanyon Reviewers use for theme submissions.", 'plugin-check' ); ?></p>
	<?php
}

function tc_success() {
	?>
	<div class="tc-success">
		<p><?php _e( 'Congratulations! Your plugin has passed the basic tests.', 'plugin-check' ); ?></p>
	</div>
	<?php
}

function tc_form_envato_plugin_check() {
	$plugins = tc_get_plugins();

		echo '<form action="plugins.php?page=envato_plugin_check" method="post">';
		echo '<select name="pluginname">';
		foreach( $plugins as $name => $info ) {
			echo '<option ';
echo 'value="' . $info["dirname"] . ' | ' . $info["location"] . ' " style="font-weight:bold;">' . $info["name"] . '</option>';
		}
		
		echo '</select>';
		echo '<input class="button" type="submit" value="submit" />';
		if ( defined( 'TC_PRE' ) || defined( 'TC_POST' ) ) echo ' <input name="trac" type="checkbox" /> ' . __( 'Output in Trac format.', 'plugin-check' );
		echo '<input name="s_info" type="checkbox" /> ' . __( 'Suppress INFO.', 'plugin-check' );
		echo '</form>';
}
