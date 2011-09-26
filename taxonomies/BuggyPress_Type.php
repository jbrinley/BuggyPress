<?php
 
class BuggyPress_Type extends BuggyPress_Taxonomy {
	protected $label_singular = 'Type';
	protected $label_plural = 'Types';
	protected $id = 'issue_type';
	protected $default_terms = array(
		'bug' => array('name' => 'Bug', 'description' => 'A bug that needs to be fixed'),
		'feature' => array('name' => 'Feature', 'description' => 'A new feature to add'),
		'task' => array('name' => 'Task', 'description' => 'A general task to complete'),
	);

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
	 * @return BuggyPress_Type
	 */
	public static function get_instance() {
		if ( !is_a(self::$instance, __CLASS__) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function register_taxonomy_args() {
		$args = parent::register_taxonomy_args();
		$args['public'] = FALSE;
		$args['query_var'] = TRUE;
		$args['rewrite'] = array(
			'slug' => 'type',
			'with_front' => FALSE,
			'hierarchical' => FALSE,
		);
		return $args;
	}
}
