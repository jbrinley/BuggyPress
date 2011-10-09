<?php
 
class BuggyPress_Project extends BuggyPress_Post_Type {
	protected $post_type_label_singular = 'Project';
	protected $post_type_label_plural = 'Projects';
	protected $slug = 'project';
	protected $post_type = 'project';

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
	 * @return BuggyPress_Project
	 */
	public static function get_instance() {
		if ( !is_a(self::$instance, __CLASS__) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
