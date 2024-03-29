<?php

class DirectoriesCheck implements plugincheck {
	protected $error = array();

	function check( $php_files, $css_files, $other_files ) {

		$ret = true;
		$found = false;

		foreach ( $php_files as $name => $file ) {
			checkcount();
			if ( strpos( $name, '.git' ) !== false || strpos( $name, '.svn' ) !== false ) $found = true;
		}

		foreach ( $css_files as $name => $file ) {
			checkcount();
			if ( strpos( $name, '.git' ) !== false || strpos( $name, '.svn' ) !== false || strpos( $name, '.hg' ) !== false || strpos( $name, '.bzr' ) !== false ) $found = true;
		}

		foreach ( $other_files as $name => $file ) {
			checkcount();
			if ( strpos( $name, '.git' ) !== false || strpos( $name, '.svn' ) !== false || strpos( $name, '.hg' ) !== false || strpos( $name, '.bzr' ) !== false ) $found = true;
		}

		if ($found) {
			$this->error[] = sprintf('<span class="tc-lead tc-required">' . __( 'REQUIRED', 'plugin-check' ) . '</span>: ' . __( 'Please remove any extraneous directories like .git or .svn from the ZIP file before uploading it.', 'plugin-check') );
			$ret = false;
		}

		return $ret;
	}

	function getError() { return $this->error; }
}
$pluginchecks[] = new DirectoriesCheck;