<?php
/**
 * Extends Envato Theme Check with additional CodeCanyon reviewer specific checks.
 */
class CodeCanyon implements plugincheck {
	protected $error = array();

	function check( $php_files, $css_files, $other_files )
	{
		$ret = true;

		$req_checks = array(
			'/@import url\s?\(/'                            => esc_html__( 'Do not use @import. Instead, use wp_enqueue to load any external stylesheets and fonts correctly', 'plugin-check' ),
			'/key=AIza/'                                    => esc_html__( 'Remove personal API key(s). These should be user options', 'plugin-check' ),
			'/(:?^|\s)alt=""/'                              => esc_html__( 'Do not leave attributes empty', 'plugin-check' ),
			'/(:?^|\s)alt=" "/'                             => esc_html__( 'Do not leave attributes empty', 'plugin-check' ),
			'/(:?^|\s)title=""/'                            => esc_html__( 'Do not leave attributes empty', 'plugin-check' ),
			'/(:?^|\s)title=" "/'                           => esc_html__( 'Do not leave attributes empty', 'plugin-check' ),
			'/(:?^|\s)placeholder=""/'                      => esc_html__( 'Do not leave attributes empty', 'plugin-check' ),
			'/(:?^|\s)placeholder=" "/'                     => esc_html__( 'Do not leave attributes empty', 'plugin-check' ),
			'/[^a-z0-9](?<!_)mkdir\s?\(/'                   => esc_html__( 'mkdir() is not allowed. Use wp_mkdir_p() instead', 'plugin-check' ),
			'/[^a-z0-9](?<!_)htmlspecialchars_decode\s?\(/' => esc_html__( 'Use wp_specialchars_decode instead', 'plugin-check' ),
			'/style_loader_tag/'                            => esc_html__( 'Do not remove core functionality', 'plugin-check' ),
			'/script_loader_tag/'                           => esc_html__( 'Do not remove core functionality', 'plugin-check' ),
			'/style_loader_src/'                            => esc_html__( 'Do not remove core functionality', 'plugin-check' ),
			'/script_loader_src/'                           => esc_html__( 'Do not remove core functionality', 'plugin-check' ),
			'/wp_calculate_image_srcset/'                   => esc_html__( 'Do not remove core functionality', 'plugin-check' ),
			'/is_plugin_active\s?\(/'                       => esc_html__( 'is_plugin_active() is not reliable. Use function_exists() or class_exists() instead', 'plugin-check' ),
			'/add_action\( &\$this/'                        => esc_html__( 'When creating a callable, never use &$this, use $this instead', 'plugin-check' ),
			'/create_function\s?\(/'                        => esc_html__( 'The create_function() function has been deprecated as of PHP 7.2.0 and must no longer be used', 'plugin-check' ),
		);


		$warn_checks = array(
			'/@(?!media|keyframes|font)(\$|([a-zA-Z]))+/'     		=> esc_html__( 'Possible error suppression is being used', 'plugin-check' ),
			'/@include/'                                      		=> esc_html__( 'Possible error suppression is being used', 'plugin-check' ),
			'/@require/'                                      		=> esc_html__( 'Possible error suppression is being used', 'plugin-check' ),
			'/@file/'                                         		=> esc_html__( 'Possible error suppression is being used', 'plugin-check' ),
			'/balanceTags\s*\(\s*/'             									=> esc_html__( 'Possible data validation issues found. balanceTags() does not escape data', 'plugin-check' ),
			'/force_balance_tags\s*\(\s*/'      									=> esc_html__( 'Possible data validation issues found. force_balance_tags() does not escape data', 'plugin-check' ),
			'/(echo|print)\s*(\$|[a-zA-Z])/' 											=> esc_html__( 'Possible data validation issues found. All dynamic data must be correctly escaped for the context where it is rendered', 'plugin-check' ),
			'/$_SERVER/'                   												=> esc_html__( 'PHP Global Variable found. Ensure the context is safe and reliable', 'plugin-check' ),
			'/(?<=(?:->))get_results(?!.+\bprepare\b)\(.*\)/'			=> esc_html__( 'Possible unprepared SQL statements. All queries with this method need to be prepared', 'plugin-check' ),
			'/(?<=(?:->))query(?!.+\bprepare\b)\(.*\)/'						=> esc_html__( 'Possible unprepared SQL statements. All queries with this method need to be prepared', 'plugin-check' ),
			'/(?<=(?:->))get_row(?!.+\bprepare\b)\(.*\)/'					=> esc_html__( 'Possible unprepared SQL statements. All queries with this method need to be prepared', 'plugin-check' ),
			'/(?<=(?:->))get_var(?!.+\bprepare\b)\(.*\)/'					=> esc_html__( 'Possible unprepared SQL statements. All queries with this method need to be prepared', 'plugin-check' ),
			'/(?<=(?:->))get_col(?!.+\bprepare\b)\(.*\)/'					=> esc_html__( 'Possible unprepared SQL statements. All queries with this method need to be prepared', 'plugin-check' ),
			'/(?<=(?:->))prepare\(.*\)/'													=> esc_html__( 'Possible unprepared SQL statements. The prepare method must have placeholders for it to work correctly', 'plugin-check' ),
			'/(?<![a-zA-Z])_e\(/'																	=> esc_html__( 'All text strings are to be translatable and properly escaped', 'plugin-check' ),
			'/(?<![a-zA-Z])__\(/'																	=> esc_html__( 'All text strings are to be translatable and properly escaped', 'plugin-check' ),
		);

		$info_checks = array(
			'/remove_filter\s?\(/'                            => esc_html__( 'Possible removal of core filter. Ensure this is a valid use case', 'plugin-check' ),
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
					$this->error[] = sprintf('<span class="tc-lead tc-warning">'. __( 'REQUIRED', 'plugin-check' ) . '</span>: ' . __( 'Found %1$s in the file %2$s. %3$s. %4$s', 'plugin-check' ), '<strong>' . $error . '</strong>', '<strong>' . $filename . '</strong>', $check, $grep );
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
					$this->error[] = sprintf('<span class="tc-lead tc-warning">'. __( 'WARNING', 'plugin-check' ) . '</span>: ' . __( 'Found %1$s in the file %2$s. %3$s. %4$s', 'plugin-check' ), '<strong>' . $error . '</strong>', '<strong>' . $filename . '</strong>', $check, $grep );
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
					$this->error[] = sprintf('<span class="tc-lead tc-info">'. __( 'INFO', 'plugin-check' ) . '</span>: ' . __( 'Found %1$s in the file %2$s. %3$s. %4$s', 'plugin-check' ), '<strong>' . $error . '</strong>', '<strong>' . $filename . '</strong>', $check, $grep );
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
					$this->error[] = sprintf('<span class="tc-lead tc-warning">'. __( 'REQUIRED', 'plugin-check' ) . '</span>: ' . __( 'Found %1$s in the file %2$s. %3$s. %4$s', 'plugin-check' ), '<strong>' . $error . '</strong>', '<strong>' . $filename . '</strong>', $check, $grep );
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
					$this->error[] = sprintf('<span class="tc-lead tc-warning">'. __( 'WARNING', 'plugin-check' ) . '</span>: ' . __( 'Found %1$s in the file %2$s. %3$s. %4$s', 'plugin-check' ), '<strong>' . $error . '</strong>', '<strong>' . $filename . '</strong>', $check, $grep );
					$ret = false;
				}
			}
		}

		return $ret;
	}
	function getError() { return $this->error; }
}
$pluginchecks[] = new CodeCanyon;
