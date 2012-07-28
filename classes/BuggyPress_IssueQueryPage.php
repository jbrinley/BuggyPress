<?php

class BuggyPress_IssueQueryPage {
	/** @var BuggyPress_IssueQueryPage */
	private static $instance;

	public function set_rewrite_rules( $rules ) {
		global $wp_rewrite;
		$project_pto = get_post_type_object('project');
		$project_slug = $project_pto->rewrite['slug'];
		$issue_pto = get_post_type_object('issue');
		$issue_slug = $issue_pto->rewrite['slug'];
		$regex = "$project_slug/(.+)/$issue_slug(/page/([0-9]+))?/?";
		$redirect = sprintf('index.php?post_type=%s&issue_project=%s&paged=%s', 'issue', $wp_rewrite->preg_index(1), $wp_rewrite->preg_index(3));
		$rules = array_merge(array($regex=>$redirect), $rules);
		return $rules;
	}

	private function add_hooks() {
		//add_action( 'registered_post_type', array( $this, 'set_rewrite_rules' ), 10, 3 );
		add_filter( 'rewrite_rules_array', array($this, 'set_rewrite_rules'), 0, 1 );
	}

	/********** Singleton *************/

	/**
	 * Create the instance of the class
	 *
	 * @static
	 * @return void
	 */
	public static function init() {
		self::$instance = self::get_instance();
	}

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 * @static
	 * @return BuggyPress_IssueQueryPage
	 */
	public static function get_instance() {
		if ( !is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	final public function __clone() {
		trigger_error( "Singleton. No cloning allowed!", E_USER_ERROR );
	}

	final public function __wakeup() {
		trigger_error( "Singleton. No serialization allowed!", E_USER_ERROR );
	}

	protected function __construct() {
		$this->add_hooks();
	}
}
