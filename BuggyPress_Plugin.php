<?php

class BuggyPress_Plugin {
	const PLUGIN_NAME = 'BuggyPress';
	const TEXT_DOMAIN = 'buggypress';
	const MIN_PHP_VERSION = '5.2';
	const MIN_WP_VERSION = '3.2';
	const VERSION = '0.4';
	const DB_VERSION = 1;
	const PLUGIN_INIT_HOOK = 'buggypress_init';
	const DEBUG = FALSE;

	/**
	 * Return $string after translating it with the plugin's text domain
	 *
	 * @static
	 * @param string $string
	 * @return string|void
	 */
	protected static function __( $string ) {
		return __($string, self::TEXT_DOMAIN);
	}

	/**
	 * Echo $string after translating it with the plugin's text domain
	 *
	 * @static
	 * @param string $string
	 * @return void
	 */
	protected static function _e( $string ) {
		_e($string, self::TEXT_DOMAIN);
	}

	/**
	 * Get the absolute system path to the plugin directory, or a file therein
	 * 
	 * @static
	 * @param string $path
	 * @return string
	 */
	protected static function plugin_path( $path ) {
		$base = dirname(__FILE__);
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
	protected static function plugin_url( $path ) {
		return plugins_url($path, __FILE__);
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
		printf( '<div class="error"><p>%s</p></div>', sprintf( self::__( '%1$s requires WordPress %2$s or higher and PHP %3$s or higher.' ), self::PLUGIN_NAME, $wp_version, $php_version ) );
	}

	public static function initialize_plugin() {
		spl_autoload_register(array(__CLASS__, 'autoloader'));
		$post_types = array(
			'BuggyPress_Project',
			'BuggyPress_Issue',
		);
		foreach ( $post_types as $pt ) {
			add_action(self::PLUGIN_INIT_HOOK, array($pt, 'init'));
		}
		do_action(self::PLUGIN_INIT_HOOK);
	}

	public static function autoloader( $class ) {
		$files = array(
			self::plugin_path($class.'.php'),
			self::plugin_path('post-types'.DIRECTORY_SEPARATOR.$class.'.php'),
			self::plugin_path('meta-boxes'.DIRECTORY_SEPARATOR.$class.'.php'),
			self::plugin_path('taxonomies'.DIRECTORY_SEPARATOR.$class.'.php'),
		);
		foreach ( $files as $file ) {
			if ( file_exists($file) ) {
				include_once($file);
				break;
			}
		}
	}
}
