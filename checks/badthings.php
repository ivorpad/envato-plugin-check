<?php
class Bad_Checks implements plugincheck {
	protected $error = array();

	function check( $php_files, $css_files, $other_files ) {
		$ret = true;

		$checks = array(
			'/(?<![_|a-z0-9|\.])eval\s?\(/i' => __( 'eval() is not allowed.', 'plugin-check' ),
			'/[^a-z0-9](?<!_)(popen|proc_open|[^_]exec|shell_exec|system|passthru)\(/' => __( 'PHP system calls are often disabled by server admins and should not be in plugins', 'plugin-check' ),
			'/\s?ini_set\(/' => __( 'Plugins should not change server PHP settings', 'plugin-check' ),
			'/base64_decode/' => __( 'base64_decode() is not allowed', 'plugin-check' ),
			'/base64_encode/' => __( 'base64_encode() is not allowed', 'plugin-check' ),
			'/uudecode/ims' => __( 'uudecode() is not allowed', 'plugin-check' ),
			'/str_rot13/ims' => __( 'str_rot13() is not allowed', 'plugin-check' ),
			'/cx=[0-9]{21}:[a-z0-9]{10}/' => __( 'Google search code detected', 'plugin-check' ),
			'/pub-[0-9]{16}/i' => __( 'Google advertising code detected', 'plugin-check' )
			);

		$grep = '';

		foreach ( $php_files as $php_key => $phpfile ) {
			foreach ( $checks as $key => $check ) {
			checkcount();
				if ( preg_match( $key, $phpfile, $matches ) ) {
					$filename = tc_filename( $php_key );
					$error = ltrim( trim( $matches[0], '(' ) );
					$grep = tc_grep( $error, $php_key );
					$this->error[] = sprintf('<span class="tc-lead tc-warning">'. __( 'REQUIRED', 'plugin-check' ) . '</span>: ' . __( 'Found %1$s in the file %2$s. %3$s. %4$s', 'plugin-check' ), '<strong>' . $error . '</strong>', '<strong>' . $filename . '</strong>', $check, $grep );
					$ret = false;
				}
			}
		}


		$checks = array(
			'/cx=[0-9]{21}:[a-z0-9]{10}/' => __( 'Google search code detected', 'plugin-check' ),
			'/pub-[0-9]{16}/' => __( 'Google advertising code detected', 'plugin-check' )
			);

		foreach ( $other_files as $php_key => $phpfile ) {
			foreach ( $checks as $key => $check ) {
				checkcount();
				if ( preg_match( $key, $phpfile, $matches ) ) {
					$filename = tc_filename( $php_key );
					$error = ltrim( rtrim( $matches[0],'(' ) );
					$grep = tc_grep( $error, $php_key );
					$this->error[] = sprintf(__('<span class="tc-lead tc-warning">REQUIRED</span>: Found <strong>%1$s</strong> in the file <strong>%2$s</strong>. %3$s.%4$s', 'plugin-check'), $error, $filename, $check, $grep);
					$ret = false;
				}
			}
		}
		return $ret;
	}
	function getError() { return $this->error; }
}
$pluginchecks[] = new Bad_Checks;
