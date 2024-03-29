<?php
class PHPShortTagsCheck implements plugincheck {
	protected $error = array();

	function check( $php_files, $css_files, $other_files ) {

		$ret = true;

		foreach ( $php_files as $php_key => $phpfile ) {
			checkcount();
			if ( preg_match( '/<\?\=/i', $phpfile ) ) {
				$filename = tc_filename( $php_key );
				$grep = tc_preg( '/<\?\=/', $php_key );
				$this->error[] = sprintf('<span class="tc-lead tc-warning">'.__('WARNING','plugin-check').'</span>: '.__('Found PHP short tags in file %1$s.%2$s', 'plugin-check'), '<strong>' . $filename . '</strong>', $grep);
				$ret = false;
			}
		}

		return $ret;
	}

	function getError() { return $this->error; }
}

$pluginchecks[] = new PHPShortTagsCheck;