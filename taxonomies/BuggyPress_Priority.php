<?php
 
class BuggyPress_Priority extends BuggyPress_Taxonomy {
	const TAXONOMY_ID = 'issue_priority';
	protected $label_singular = 'Priority';
	protected $label_plural = 'Priorities';
	protected $id = self::TAXONOMY_ID;
	protected $default_terms = array(
		'critical' => array('name' => 'Critical', 'description' => 'Must be fixed ASAP'),
		'high' => array('name' => 'High', 'description' => 'High priority'),
		'medium' => array('name' => 'Medium', 'description' => 'Medium priority'),
		'low' => array('name' => 'Low', 'description' => 'Low priority'),
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
	 * @return BuggyPress_Priority
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
			'slug' => 'priority',
			'with_front' => FALSE,
			'hierarchical' => FALSE,
		);
		return $args;
	}

	public static function get_terms( $post_id ) {
		$terms = wp_get_object_terms($post_id, self::TAXONOMY_ID);
		return $terms;
	}
}
