<?php

class BuggyPress {
	const PLUGIN_NAME = 'BuggyPress';
	const TEXT_DOMAIN = 'buggypress';
	const MIN_PHP_VERSION = '5.2';
	const MIN_WP_VERSION = '3.2';
	const VERSION = '0.5';
	const DB_VERSION = 2;
	const PLUGIN_INIT_HOOK = 'buggypress_loaded';
	const DEBUG = FALSE;


	const MEMBERS = 'members';
	const USERS = 'users';
	const ALL = 'public';

	private static $plugin_file = '';

	/**
	 * Get the absolute system path to the plugin directory, or a file therein
	 * 
	 * @static
	 * @param string $path
	 * @return string
	 */
	public static function plugin_path( $path ) {
		$base = dirname(self::$plugin_file);
		if ( $path ) {
			return trailingslashit($base).$path;
		} else {
			return untrailingslashit($base);
		}
	}

	/**
	 * Get the absolute URL to the plugin directory, or a file therein
	 * @static
	 * @param string $path
	 * @return string
	 */
	public static function plugin_url( $path ) {
		return plugins_url($path, self::$plugin_file);
	}

	public static function set_plugin_basedir() {
		if ( !empty(self::$plugin_file) ) {
			return;
		}
		global $plugin, $mu_plugin, $network_plugin;
		$plugin_file = dirname(__FILE__);
		if ( !empty($plugin) ) {
			$plugin_file = $plugin;
		} elseif ( !empty($mu_plugin) ) {
			$plugin_file = $mu_plugin;
		} elseif ( !empty($network_plugin) ) {
			$plugin_file = $network_plugin;
		}
		self::$plugin_file = $plugin_file;
	}

	/**
	 * Check that the minimum PHP and WP versions are met
	 *
	 * @static
	 * @param string $php_version
	 * @param string $wp_version
	 * @return bool Whether the test passed
	 */
	public static function prerequisites_met( $php_version, $wp_version ) {
		$pass = TRUE;
		$pass = $pass && version_compare( $php_version, self::MIN_PHP_VERSION, '>=');
		$pass = $pass && version_compare( $wp_version, self::MIN_WP_VERSION, '>=');
		return $pass;
	}

	/**
	 * Print a notice indicating why BuggyPress refused to load
	 *
	 * @static
	 * @param string $php_version
	 * @param string $wp_version
	 * @return void
	 */
	public static function failed_to_load_notices( $php_version = self::MIN_PHP_VERSION, $wp_version = self::MIN_WP_VERSION ) {
		printf( '<div class="error"><p>%s</p></div>', sprintf( __( '%1$s requires WordPress %2$s or higher and PHP %3$s or higher.', 'buggypress' ), self::PLUGIN_NAME, $wp_version, $php_version ) );
	}

	public static function initialize_plugin() {
		self::set_plugin_basedir();
		// initialize the Zend library
		set_include_path(implode(PATH_SEPARATOR, array(get_include_path(), self::plugin_path('lib'))));
		require_once('Zend/Loader/Autoloader.php');
		Zend_Loader_Autoloader::getInstance();
		spl_autoload_register(array(__CLASS__, 'autoloader'));
		$to_init = array(
			'BuggyPress_Issue',
			'BuggyPress_Project',
			'BuggyPress_CommentForms',
			'BuggyPress_NewIssuePage',
		);
		foreach ( $to_init as $pt ) {
			add_action(self::PLUGIN_INIT_HOOK, array($pt, 'init'));
		}

		// load all the template tags
		foreach ( glob(self::plugin_path("template-tags/*.php")) as $filename ) {
			include $filename;
		}
		do_action(self::PLUGIN_INIT_HOOK);
	}

	public static function autoloader( $class ) {
		if ( strpos($class, 'BuggyPress') === 0 ) {
			if ( strpos($class, 'BuggyPress_MB') === 0 ) {
				$file = self::plugin_path('classes'.DIRECTORY_SEPARATOR.'meta-boxes'.DIRECTORY_SEPARATOR.$class.'.php');
			} elseif ( strpos($class, 'BuggyPress_Form_Element') === 0 ) {
				$file = self::plugin_path('classes'.DIRECTORY_SEPARATOR.'forms'.DIRECTORY_SEPARATOR.'elements'.DIRECTORY_SEPARATOR.$class.'.php');
			} elseif ( strpos($class, 'BuggyPress_Form') === 0 ) {
				$file = self::plugin_path('classes'.DIRECTORY_SEPARATOR.'forms'.DIRECTORY_SEPARATOR.$class.'.php');
			} else {
				$file = self::plugin_path('classes'.DIRECTORY_SEPARATOR.$class.'.php');
			}
			if ( file_exists($file) ) {
				include_once($file);
			}
		}
	}
}
