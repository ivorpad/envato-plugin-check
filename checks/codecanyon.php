<?php
/**
 * Extends Theme Check with additional Themeforest reviewer specific checks.
 */
class Themeforest implements themecheck {
	protected $error = array();

	function check( $php_files, $css_files, $other_files )
	{
		$ret = true;

		$req_checks = array(
			'/@import url\s?\(/'                            => esc_html__( 'Do not use @import. Instead, use wp_enqueue to load any external stylesheets and fonts correctly', 'theme-check' ),
			'/.bypostauthor{}/'                             => esc_html__( 'Do not use empty CSS classes to try to trick theme check', 'theme-check' ),
			'/.bypostauthor {}/'                            => esc_html__( 'Do not use empty CSS classes to try to trick theme check', 'theme-check' ),
			'/.sticky{}/'                                   => esc_html__( 'Do not use empty CSS classes to try to trick theme check', 'theme-check' ),
			'/.sticky {}/'                                  => esc_html__( 'Do not use empty CSS classes to try to trick theme check', 'theme-check' ),
			'/.gallery-caption{}/'                          => esc_html__( 'Do not use empty CSS classes to try to trick theme check', 'theme-check' ),
			'/.gallery-caption {}/'                         => esc_html__( 'Do not use empty CSS classes to try to trick theme check', 'theme-check' ),
			'/.screen-reader-text{}/'                       => esc_html__( 'Do not use empty CSS classes to try to trick theme check', 'theme-check' ),
			'/.screen-reader-text {}/'                      => esc_html__( 'Do not use empty CSS classes to try to trick theme check', 'theme-check' ),
			'/.wp-caption-text{}/'                          => esc_html__( 'Do not use empty CSS classes to try to trick theme check', 'theme-check' ),
			'/.wp-caption-text {}/'                         => esc_html__( 'Do not use empty CSS classes to try to trick theme check', 'theme-check' ),
			'/key=AIza/'                                    => esc_html__( 'Remove personal API key(s). These should be user options', 'theme-check' ),
			'/(:?^|\s)alt=""/'                              => esc_html__( 'Do not leave attributes empty', 'theme-check' ),
			'/(:?^|\s)alt=" "/'                             => esc_html__( 'Do not leave attributes empty', 'theme-check' ),
			'/(:?^|\s)title=""/'                            => esc_html__( 'Do not leave attributes empty', 'theme-check' ),
			'/(:?^|\s)title=" "/'                           => esc_html__( 'Do not leave attributes empty', 'theme-check' ),
			'/(:?^|\s)placeholder=""/'                      => esc_html__( 'Do not leave attributes empty', 'theme-check' ),
			'/(:?^|\s)placeholder=" "/'                     => esc_html__( 'Do not leave attributes empty', 'theme-check' ),
			'/[^a-z0-9](?<!_)mkdir\s?\(/'                   => esc_html__( 'mkdir() is not allowed. Use wp_mkdir_p() instead', 'theme-check' ),
			'/[^a-z0-9](?<!_)htmlspecialchars_decode\s?\(/' => esc_html__( 'Use wp_specialchars_decode instead', 'theme-check' ),
			'/style_loader_tag/'                            => esc_html__( 'Do not remove core functionality', 'theme-check' ),
			'/script_loader_tag/'                           => esc_html__( 'Do not remove core functionality', 'theme-check' ),
			'/style_loader_src/'                            => esc_html__( 'Do not remove core functionality', 'theme-check' ),
			'/script_loader_src/'                           => esc_html__( 'Do not remove core functionality', 'theme-check' ),
			'/wp_calculate_image_srcset/'                   => esc_html__( 'Do not remove core functionality', 'theme-check' ),
			'/is_plugin_active\s?\(/'                       => esc_html__( 'is_plugin_active() is not reliable. Use function_exists() or class_exists() instead', 'theme-check' ),
			'/add_action\( &\$this/'                        => esc_html__( 'When creating a callable, never use &$this, use $this instead', 'theme-check' ),
			'/admin_bar_menu/'                              => esc_html__( 'Themes must not add any entries to the admin bar', 'theme-check' ),
			'/create_function\s?\(/'                        => esc_html__( 'The create_function() function has been deprecated as of PHP 7.2.0 and must no longer be used', 'theme-check' ),
		);

		$warn_checks = array(
			'/@(?!media|keyframes|font)(\$|([a-zA-Z]))+/'                    => esc_html__( 'Possible error suppression is being used', 'theme-check' ),
			'/@include/'                                      => esc_html__( 'Possible error suppression is being used', 'theme-check' ),
			'/@require/'                                      => esc_html__( 'Possible error suppression is being used', 'theme-check' ),
			'/@file/'                                         => esc_html__( 'Possible error suppression is being used', 'theme-check' ),
			'/balanceTags\s*\(\s*\$/'             						=> esc_html__( 'Possible data validation issues found. balanceTags() does not escape data', 'theme-check' ),
			'/force_balance_tags\s*\(\s*\$/'      						=> esc_html__( 'Possible data validation issues found. force_balance_tags() does not escape data', 'theme-check' ),
			'/(echo|print)\s*(\$|[a-zA-Z])/' 									=> esc_html__( 'Possible data validation issues found. All dynamic data must be correctly escaped for the context where it is rendered', 'theme-check' ),
			'/$_SERVER/'                   										=> esc_html__( 'PHP Global Variable found. Ensure the context is safe and reliable', 'theme-check' ),
			'/(?<=(->))((query|get_results|get_row|get_var|get_col)\()(?!\s*\D*prepare)/' => esc_html__('Possible unprepared SQL statements. All queries with this method need to be prepared', 'theme-check')
		);

		$info_checks = array(
			'/remove_filter\s?\(/'                            => esc_html__( 'Possible removal of core filter. Ensure this is a valid use case', 'theme-check' ),
		);

		$grep = '';

		foreach ( $php_files as $php_key => $phpfile )
		{
			foreach ( $req_checks as $key => $check )
			{
				checkcount();

				if ( preg_match( $key, $phpfile, $matches ) )
				{
					$filename = tc_filename( $php_key );
					$error = trim( $matches[0] );
					$grep = tc_grep( $error, $php_key );
					$this->error[] = sprintf('<span class="tc-lead tc-warning">'. __( 'REQUIRED', 'theme-check' ) . '</span>: ' . __( 'Found %1$s in the file %2$s. %3$s. %4$s', 'theme-check' ), '<strong>' . $error . '</strong>', '<strong>' . $filename . '</strong>', $check, $grep );
					$ret = false;
				}
			}
		}

		foreach ( $php_files as $php_key => $phpfile )
		{
			foreach ( $warn_checks as $key => $check )
			{
				checkcount();

				if ( preg_match( $key, $phpfile, $matches ) )
				{
					$filename = tc_filename( $php_key );
					$error = trim( $matches[0] );
					$grep = tc_grep( $error, $php_key );
					$this->error[] = sprintf('<span class="tc-lead tc-warning">'. __( 'WARNING', 'theme-check' ) . '</span>: ' . __( 'Found %1$s in the file %2$s. %3$s. %4$s', 'theme-check' ), '<strong>' . $error . '</strong>', '<strong>' . $filename . '</strong>', $check, $grep );
					$ret = false;
				}
			}
		}

		foreach ( $php_files as $php_key => $phpfile )
		{
			foreach ( $info_checks as $key => $check )
			{
				checkcount();

				if ( preg_match( $key, $phpfile, $matches ) )
				{
					$filename = tc_filename( $php_key );
					$error = trim( $matches[0] );
					$grep = tc_grep( $error, $php_key );
					$this->error[] = sprintf('<span class="tc-lead tc-info">'. __( 'INFO', 'theme-check' ) . '</span>: ' . __( 'Found %1$s in the file %2$s. %3$s. %4$s', 'theme-check' ), '<strong>' . $error . '</strong>', '<strong>' . $filename . '</strong>', $check, $grep );
					$ret = false;
				}
			}
		}

		foreach ( $css_files as $php_key => $phpfile )
		{
			foreach ( $req_checks as $key => $check )
			{
				checkcount();

				if ( preg_match( $key, $phpfile, $matches ) )
				{
					$filename = tc_filename( $php_key );
					$error = trim( $matches[0] );
					$grep = tc_grep( $error, $php_key );
					$this->error[] = sprintf('<span class="tc-lead tc-warning">'. __( 'REQUIRED', 'theme-check' ) . '</span>: ' . __( 'Found %1$s in the file %2$s. %3$s. %4$s', 'theme-check' ), '<strong>' . $error . '</strong>', '<strong>' . $filename . '</strong>', $check, $grep );
					$ret = false;
				}
			}
		}

		foreach ( $css_files as $php_key => $phpfile )
		{
			foreach ( $warn_checks as $key => $check )
			{
				checkcount();

				if ( preg_match( $key, $phpfile, $matches ) )
				{
					$filename = tc_filename( $php_key );
					$error = trim( $matches[0] );
					$grep = tc_grep( $error, $php_key );
					$this->error[] = sprintf('<span class="tc-lead tc-warning">'. __( 'WARNING', 'theme-check' ) . '</span>: ' . __( 'Found %1$s in the file %2$s. %3$s. %4$s', 'theme-check' ), '<strong>' . $error . '</strong>', '<strong>' . $filename . '</strong>', $check, $grep );
					$ret = false;
				}
			}
		}

		return $ret;
	}
	function getError() { return $this->error; }
}
$themechecks[] = new Themeforest;
