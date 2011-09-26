<?php
 
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
	 * @return BuggyPress_Issue
	 */
	public static function get_instance() {
		if ( !is_a(self::$instance, __CLASS__) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function __construct() {
		parent::__construct();
		$type = $this->add_taxonomy('BuggyPress_Type');
		$priority = $this->add_taxonomy('BuggyPress_Priority');
		$status = $this->add_taxonomy('BuggyPress_Status');
		$resolution = $this->add_taxonomy('BuggyPress_Resolution');

		$taxonomy_args = array(
			$type->get_id() => array(
				'label' => self::__('Type'),
			),
			$priority->get_id() => array(
				'label' => self::__('Priority'),
			),
			$status->get_id() => array(
				'label' => self::__('Status'),
			),
		);

		$this->add_meta_box('BuggyPress_MB_Taxonomies', array('taxonomies' => $taxonomy_args));
		$this->add_meta_box('BuggyPress_MB_Issue_Project');
		$this->add_meta_box('BuggyPress_MB_Assignee');


	}
}
