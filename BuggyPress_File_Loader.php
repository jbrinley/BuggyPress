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
	}

	public function __destruct() {
		spl_autoload_unregister(array($this, 'autoloader'));
	}

	/**
	 * Load the plugin's files
	 *
	 * @return void
	 */
	public function load() {
		foreach ( $this->dirs as $dir ) {
			$this->recursively_include(BuggyPress::plugin_path().'/'.$dir);
		}
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
	 * Calls the static init() function on the given classes
	 * 
	 * @param array $classes
	 * @return void
	 */
	public function initialize( array $classes = array() ) {
		// TODO: Check if this needs to be optimized
		if ( !$classes ) {
			$classes = $this->get_buggypress_classes();
		}
		foreach ( $classes as $class_name ) {
			if ( $this->implements_init($class_name) ) {
				add_action('init', array($class_name, 'init'), 10, 0);
			}
		}
	}

	/**
	 * @return array A list of declared classes that are subclasses of BuggyPress
	 */
	private function get_buggypress_classes() {
		$our_classes = array();
		$all_classes = get_declared_classes();
		foreach ( $all_classes as $class ) {
			if ( $class == 'BuggyPress' || is_subclass_of($class, 'BuggyPress') ) {
				$our_classes[] = $class;
			}
		}
		return $our_classes;
	}

	/**
	 * @param string $class_name
	 * @return bool Whether the class implements the static 'init' method
	 */
	private function implements_init( $class_name ) {
		$reflection = new ReflectionClass($class_name);
		if ( $reflection->hasMethod('init') ) {
			$method = $reflection->getMethod('init');
			if ( $method->isStatic() && $method->getDeclaringClass()->getName() == $class_name ) {
				return TRUE;
			}
		}
		return FALSE;
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
