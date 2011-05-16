<?php
/**
 * User: jbrinley
 * Date: 5/15/11
 * Time: 9:55 PM
 */
 
class BuggyPress_File_Loader {

	private $dirs = array();

	/**
	 * @param array $dirs The directories to look in to automatically load files
	 */
	public function __construct( array $dirs = array('models', 'controllers') ) {
		$this->dirs = $dirs;

		// autoload BuggyPress classes in designated directories (so file load order doesn't matter)
		spl_autoload_register(array($this, 'autoloader'));

		// include all models and controllers
		$this->load_plugin_files();

		// unregister so we can be garbage collected
		spl_autoload_unregister(array($this, 'autoloader'));
	}

	/**
	 * Check this plugin's directories for classes to autoload
	 *
	 * @param string $name
	 * @return void
	 */
	public function autoloader( $name ) {
		foreach ( $this->dirs as $dir ) {
			if ( is_file(BuggyPress::plugin_path().'/'.$dir.'/'.$name.'.php') ) {
				include_once(BuggyPress::plugin_path().'/'.$dir.'/'.$name.'.php');
				break;
			}
		}
	}

	/**
	 * Load the plugin's files
	 *
	 * @return void
	 */
	private function load_plugin_files() {
		foreach ( $this->dirs as $dir ) {
			$this->recursively_include(BuggyPress::plugin_path().'/'.$dir);
		}
	}

	/**
	 * Recursively load all *.php files in the given directory and its sub-directories
	 *
	 * @param string $dir
	 * @return bool
	 */
	private function recursively_include( $dir ) {
		if ( !is_dir($dir) ) {
			return FALSE;
		}
		$dirHandle = opendir($dir);
		while ( FALSE !== ( $incFile = readdir($dirHandle) ) ) {
			if ( substr($incFile, -4) == '.php' ) {
				if ( is_file("$dir/$incFile") ) {
					include_once("$dir/$incFile");
				} else {
					$this->recursively_include("$dir/$incFile");
				}
			}
		}
		return TRUE;
	}
}
