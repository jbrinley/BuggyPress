<?php

class BuggyPress_Issue {
	const POST_TYPE = 'issue';

	private $post_id = NULL;
	private $assignee_id = NULL;
	private $project_id = NULL;
	private $status = '';
	private $priority = '';
	private $resolution = '';
	private $type = '';

	/**
	 * @var BuggyPress_Post_Type
	 */
	private static $cpt = NULL;
	/**
	 * @var Flightless_Taxonomy
	 */
	private static $tax_priority = NULL;
	/**
	 * @var Flightless_Taxonomy
	 */
	private static $tax_resolution = NULL;
	/**
	 * @var Flightless_Taxonomy
	 */
	private static $tax_status = NULL;
	/**
	 * @var Flightless_Taxonomy
	 */
	private static $tax_type = NULL;
	/**
	 * @var BuggyPress_MB_Taxonomies
	 */
	private static $mb_taxonomies = NULL;
	/**
	 * @var BuggyPress_MB_Assignee
	 */
	private static $mb_assignee = NULL;
	/**
	 * @var BuggyPress_MB_IssueProject
	 */
	private static $mb_project = NULL;


	/**
	 * @param int $post_id
	 */
	public function __construct( $post_id ) {
		if ( !(int)$post_id ) {
			throw new InvalidArgumentException(__('A valid post ID must be supplied.', 'buggypress'));
		}
		$this->post_id = $post_id;
	}

	public function get_id() {
		return $this->post_id;
	}

	public function get_post() {
		return get_post($this->post_id);
	}

	public function get_status() {
		if ( !$this->status ) {
			$this->status = self::$mb_taxonomies->get_current_value( $this->post_id, self::$tax_status->get_id(), 'object' );
		}
		return $this->status;
	}

	public function set_status( $status ) {
		self::$mb_taxonomies->set_value( $this->post_id, $status, self::$tax_status->get_id() );
		$this->status = $status;
	}

	public function get_priority() {
		if ( !$this->priority ) {
			$this->priority = self::$mb_taxonomies->get_current_value( $this->post_id, self::$tax_priority->get_id(), 'object' );
		}
		return $this->priority;
	}

	public function set_priority( $priority ) {
		self::$mb_taxonomies->set_value( $this->post_id, $priority, self::$tax_priority->get_id() );
		$this->priority = $priority;
	}

	public function get_resolution() {
		if ( !$this->resolution ) {
			$this->resolution = self::$mb_taxonomies->get_current_value( $this->post_id, self::$tax_resolution->get_id(), 'object' );
		}
		return $this->resolution;
	}

	public function set_resolution( $resolution ) {
		self::$mb_taxonomies->set_value( $this->post_id, $resolution, self::$tax_resolution->get_id() );
		$this->resolution = $resolution;
	}

	public function get_type() {
		if ( !$this->type ) {
			$this->type = self::$mb_taxonomies->get_current_value( $this->post_id, self::$tax_type->get_id(), 'object' );
		}
		return $this->type;
	}

	public function set_type( $type ) {
		self::$mb_taxonomies->set_value( $this->post_id, $type, self::$tax_type->get_id() );
		$this->type = $type;
	}

	public function get_assignee_id() {
		if ( is_null($this->assignee_id) ) {
			$this->assignee_id = self::$mb_assignee->get_assignee( $this->post_id );
		}
		return $this->assignee_id;
	}

	public function set_assignee_id( $user_id ) {
		self::$mb_assignee->set_assignee( $this->post_id, $user_id );
		$this->assignee_id = $user_id;
	}

	public function get_project_id() {
		if ( is_null($this->project_id) ) {
			$this->project_id = self::$mb_project->get_project( $this->post_id );
		}
		return $this->project_id;
	}

	public function set_project_id( $project_id ) {
		self::$mb_project->set_project( $this->post_id, $project_id );
		$this->project_id = $project_id;
	}


	/**
	 * @static
	 * Intialize the post type and related features
	 */
	public static function init() {
		self::create_post_type();
	}

	public static function create_post_type() {
		self::$cpt = new BuggyPress_Post_Type( self::POST_TYPE );
		self::$cpt->set_post_type_label( __('Issue', 'buggypress'), __('Issues', 'buggypress') );
		self::$cpt->slug = '%parent_project%/'._x( 'issues', 'post type slug', 'buggypress' );
		self::$cpt->add_support(array('comments', 'revisions'));

		self::register_taxonomies();
		self::register_meta_boxes();

		self::$cpt->capability_type = 'issues';
		self::$cpt->capabilities = array(
			'read' => 'read_issues',
		);
		$permitter = BuggyPress_Permissions::get_instance();
		$permitter->add_permissions('issues', 'administrator');
		$permitter->add_permissions('issues', 'editor');
	}

	private static function register_taxonomies() {
		// TODO: show in menu, but not in meta box

		self::$tax_priority = new Flightless_Taxonomy('issue_priority');
		self::$tax_priority->post_types[] = self::POST_TYPE;
		self::$tax_priority->set_label( __('Priority', 'buggypress'), __('Priorities', 'buggypress') );
		self::$tax_priority->public = TRUE;
		self::$tax_priority->query_var = TRUE;
		self::$tax_priority->slug = _x( 'priority', 'taxonomy slug', 'buggypress' );
		self::$tax_priority->set_default_terms(array(
			'critical' => array('name' => 'Critical', 'description' => 'Must be fixed ASAP'),
			'high' => array('name' => 'High', 'description' => 'High priority'),
			'medium' => array('name' => 'Medium', 'description' => 'Medium priority'),
			'low' => array('name' => 'Low', 'description' => 'Low priority'),
		));

		self::$tax_resolution = new Flightless_Taxonomy('issue_resolution');
		self::$tax_resolution->post_types[] = self::POST_TYPE;
		self::$tax_resolution->set_label( __('Resolution', 'buggypress'), __('Resolutions', 'buggypress') );
		self::$tax_resolution->public = TRUE;
		self::$tax_resolution->query_var = TRUE;
		self::$tax_resolution->slug = _x( 'resolution', 'taxonomy slug', 'buggypress' );
		self::$tax_resolution->set_default_terms(array(
			'unresolved' => array('name' => 'Unresolved', 'description' => 'Not yet resolved'),
			'fixed' => array('name' => 'Fixed', 'description' => 'All necessary action has been taken'),
			'wont-fix' => array('name' => 'Will Not Fix', 'description' => 'A decision has been made to leave it as-is'),
			'duplicate' => array('name' => 'Duplicate', 'description' => 'This duplicates another issue'),
			'cant-reproduce' => array('name' => 'Cannot Reproduce', 'description' => 'The problem cannot be reproduced'),
		));

		self::$tax_status = new Flightless_Taxonomy('issue_status');
		self::$tax_status->post_types[] = self::POST_TYPE;
		self::$tax_status->set_label( __('Status', 'buggypress'), __('Statuses', 'buggypress') );
		self::$tax_status->public = TRUE;
		self::$tax_status->query_var = TRUE;
		self::$tax_status->slug = _x( 'status', 'taxonomy slug', 'buggypress' );
		self::$tax_status->set_default_terms(array(
			'open' => array('name' => 'Open', 'description' => 'Not yet complete'),
			'resolved' => array('name' => 'Resolved', 'description' => 'Completed, but not yet verified'),
			'closed' => array('name' => 'Closed', 'description' => 'Completed and verified'),
			'deferred' => array('name' => 'Deferred', 'description' => 'Action may be taken in the future'),
		));

		self::$tax_type = new Flightless_Taxonomy('issue_type');
		self::$tax_type->post_types[] = self::POST_TYPE;
		self::$tax_type->set_label( __('Type', 'buggypress'), __('Types', 'buggypress') );
		self::$tax_type->public = TRUE;
		self::$tax_type->query_var = TRUE;
		self::$tax_type->slug = _x( 'type', 'taxonomy slug', 'buggypress' );
		self::$tax_type->set_default_terms(array(
			'bug' => array('name' => 'Bug', 'description' => 'A bug that needs to be fixed'),
			'feature' => array('name' => 'Feature', 'description' => 'A new feature to add'),
			'task' => array('name' => 'Task', 'description' => 'A general task to complete'),
		));
	}

	private static function register_meta_boxes() {
		self::$mb_assignee = add_flightless_meta_box(self::POST_TYPE, 'BuggyPress_MB_Assignee');
		self::$mb_project = add_flightless_meta_box(self::POST_TYPE, 'BuggyPress_MB_IssueProject');
		self::$mb_taxonomies = add_flightless_meta_box(self::POST_TYPE, 'BuggyPress_MB_Taxonomies', array( 'taxonomies' => array(
			self::$tax_type->get_id() => array( 'label' => self::$tax_type->get_label() ),
			self::$tax_status->get_id() => array( 'label' => self::$tax_status->get_label() ),
			self::$tax_priority->get_id() => array( 'label' => self::$tax_priority->get_label() ),
			self::$tax_resolution->get_id() => array( 'label' => self::$tax_resolution->get_label() ),
		)));
	}
}
