<?php
 
class BuggyPress_Status extends BuggyPress_Taxonomy {
	const TAXONOMY_ID = 'issue_status';
	protected $label_singular = 'Status';
	protected $label_plural = 'Statuses';
	protected $id = self::TAXONOMY_ID;
	protected $default_terms = array(
		'open' => array('name' => 'Open', 'description' => 'Not yet complete'),
		'resolved' => array('name' => 'Resolved', 'description' => 'Completed, but not yet verified'),
		'closed' => array('name' => 'Closed', 'description' => 'Completed and verified'),
		'deferred' => array('name' => 'Deferred', 'description' => 'Action may be taken in the future'),
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
	 * @return BuggyPress_Status
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
			'slug' => 'status',
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
