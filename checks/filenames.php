<?php
class File_Checks implements plugincheck {
	protected $error = array();

	function check( $php_files, $css_files, $other_files ) {

		$ret = true;

		$filenames = array();

		foreach ( $php_files as $php_key => $phpfile ) {
			array_push( $filenames, strtolower( basename( $php_key ) ) );
		}
		foreach ( $other_files as $php_key => $phpfile ) {
			array_push( $filenames, strtolower( basename( $php_key ) ) );
		}
		foreach ( $css_files as $php_key => $phpfile ) {
			array_push( $filenames, strtolower( basename( $php_key ) ) );
		}
		$blacklist = array(
				'thumbs.db'				=> __( 'Windows thumbnail store', 'plugin-check' ),
				'desktop.ini'			=> __( 'windows system file', 'plugin-check' ),
				'project.properties'	=> __( 'NetBeans Project File', 'plugin-check' ),
				'project.xml'			=> __( 'NetBeans Project File', 'plugin-check' ),
				'\.kpf'					=> __( 'Komodo Project File', 'plugin-check' ),
				'^\.+[a-zA-Z0-9]'		=> __( 'Hidden Files or Folders', 'plugin-check' ),
				'php.ini'				=> __( 'PHP server settings file', 'plugin-check' ),
				'dwsync.xml'			=> __( 'Dreamweaver project file', 'plugin-check' ),
				'error_log'				=> __( 'PHP error log', 'plugin-check' ),
				'web.config'			=> __( 'Server settings file', 'plugin-check' ),
				'\.sql'					=> __( 'SQL dump file', 'plugin-check' ),
				'__MACOSX'				=> __( 'OSX system file', 'plugin-check' ),
				);

		checkcount();

		foreach( $blacklist as $file => $reason ) {
			if ( $filename = preg_grep( '/' . $file . '/', $filenames ) ) {
				$error = implode( array_unique( $filename ), ' ' );
				$this->error[] = sprintf('<span class="tc-lead tc-warning">'.__('REQUIRED','plugin-check').'</span>: '.__('%1$s %2$s found.', 'plugin-check'), '<strong>' . $error . '</strong>', $reason) ;
				$ret = false;
			}
		}

		return $ret;
	}

	function getError() { return $this->error; }
}
$pluginchecks[] = new File_Checks;
