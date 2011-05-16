<?php
/**
 * User: jbrinley
 * Date: 5/15/11
 * Time: 4:58 PM
 */

abstract class BuggyPress {
	const PLUGIN_NAME = 'BuggyPress';
	const TEXT_DOMAIN = 'buggypress';
	const DEBUG = FALSE;
	const MIN_PHP_VERSION = '5.2';
	const MIN_WP_VERSION = '3.1';
	const VERSION = '0.3';
	const DB_VERSION = 1;


	/**
	 * A wrapper around WP's __() to add the plugin's text domain
	 *
	 * @param string $string
	 * @return string|void
	 */
	public static function __( $string ) {
		return __($string, self::TEXT_DOMAIN);
	}

	/**
	 * A wrapper around WP's _e() to add the plugin's text domain
	 *
	 * @param string $string
	 * @return void
	 */
	public static function _e( $string ) {
		return _e($string, self::TEXT_DOMAIN);
	}


	/**
	 * @static
	 * @return string The system path to this plugin's directory, with no trailing slash
	 */
	public static function plugin_path() {
		return WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) );
	}

	/**
	 * @static
	 * @return string The url to this plugin's directory, with no trailing slash
	 */
	public static function plugin_url() {
		return WP_PLUGIN_URL . '/' . basename( dirname( __FILE__ ) );
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

	public static function failed_to_load_notices( $php_version = self::MIN_PHP_VERSION, $wp_version = self::MIN_WP_VERSION ) {
		printf( '<div class="error"><p>%s</p></div>', sprintf( self::__( 'BuggyPress requires WordPress %1$s or higher and PHP %2$s or higher.' ), $wp_version, $php_version ) );
	}
}