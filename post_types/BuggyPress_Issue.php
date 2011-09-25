<?php
/**
 * User: jbrinley
 * Date: 5/15/11
 * Time: 4:58 PM
 */
 
class BuggyPress_Issue extends BuggyPress_Post_Type {
	protected $post_type_label_singular = 'Issue';
	protected $post_type_label_plural = 'Issues';
	protected $slug = 'issue';
	protected $post_type = 'issue';

	private static $instance;
	/**
	 * Create the instance of the class
	 *
	 * @static
	 * @return void
	 */
	public static function init() {
		self::$instance = self::get_instance();
	}

	/** Singleton */

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 * @static
	 * @return Loyalty_Activity_Subtype_Bonus_Code
	 */
	public static function get_instance() {
		if ( !is_a(self::$instance, __CLASS__) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
