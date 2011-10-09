<?php
 
class BuggyPress_Resolution extends BuggyPress_Taxonomy {
	const TAXONOMY_ID = 'issue_resolution';
	protected $label_singular = 'Resolution';
	protected $label_plural = 'Resolutions';
	protected $id = self::TAXONOMY_ID;
	protected $default_terms = array(
		'fixed' => array('name' => 'Fixed', 'description' => 'All necessary action has been taken'),
		'wont-fix' => array('name' => 'Will Not Fix', 'description' => 'A decision has been made to leave it as-is'),
		'duplicate' => array('name' => 'Duplicate', 'description' => 'This duplicates another issue'),
		'cant-reproduce' => array('name' => 'Cannot Reproduce', 'description' => 'The problem cannot be reproduced'),
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
	 * @return BuggyPress_Resolution
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
			'slug' => 'resolution',
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
