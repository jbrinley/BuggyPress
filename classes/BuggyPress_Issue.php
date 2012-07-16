<?php

class BuggyPress_Issue {
	const POST_TYPE = 'issue';

	const META_KEY_ASSIGNEE = '_buggypress_assignee';
	const META_KEY_PROJECT = '_buggypress_project';
	const META_KEY_MEMBERS = '_buggypress_project_member';
	const META_KEY_ADMINS = '_buggypress_project_admin';

	private $post_id = NULL;
	private $assignee_id = NULL;
	private $project_id = NULL;
	private $status = NULL;
	private $priority = NULL;
	private $resolution = NULL;
	private $type = NULL;

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
		$this->post_id = $post_id;
	}

	/**
	 * Zero out all instance variables so future gets
	 * will pull from the DB
	 */
	public function flush() {
		$this->assignee_id = 0;
		$this->project_id = 0;
		$this->status = NULL;
		$this->priority = NULL;
		$this->resolution = NULL;
		$this->type = NULL;
	}

	public function get_id() {
		return $this->post_id;
	}

	public function get_post() {
		if ( !$this->post_id ) {
			// return a stub post
			$post = new stdClass();
			$post->ID = 0;
			$post->post_status = 'draft';
			$post->post_type = self::POST_TYPE;
			$post->post_author = get_current_user_id();
			$post->post_content = '';
			$post->post_title = '';
			return $post;
		}
		return get_post($this->post_id);
	}

	/**
	 * Save the associated post, overriding existing values with those
	 * pass in $args
	 *
	 * @param array $args New values to set for the post
	 */
	public function save_post( $args = array() ) {
		$post = (array)$this->get_post();
		$args = wp_parse_args($args, $post);
		$id = wp_update_post($args);
		if ( $id && !is_wp_error($id) ) {
			$this->post_id = $id;
		}
	}

	public function get_permalink() {
		if ( $this->post_id ) {
			return get_permalink($this->post_id);
		}
		return '';
	}

	public function get_title() {
		if ( $this->post_id ) {
			return get_the_title($this->post_id);
		}
		return '';
	}

	public function get_description() {
		$post = $this->get_post();
		return $post->post_content;
	}

	public function get_status( $format = 'id' ) {
		if ( is_null($this->status) ) {
			$this->status = $this->get_assigned_term(self::$tax_status->get_id(), 'object');
		}
		return $this->format_term($this->status, $format);
	}

	public function set_status( $status ) {
		$this->set_assigned_term($status, self::$tax_status->get_id());
	}

	public function get_priority( $format = 'id' ) {
		if ( is_null($this->priority) ) {
			$this->priority = $this->get_assigned_term(self::$tax_priority->get_id(), 'object');
		}
		return $this->format_term($this->priority, $format);
	}

	public function set_priority( $priority ) {
		$this->set_assigned_term($priority, self::$tax_priority->get_id());
	}

	public function get_resolution( $format = 'id' ) {
		if ( is_null($this->resolution) ) {
			$this->resolution = $this->get_assigned_term(self::$tax_resolution->get_id(), 'object');
		}
		return $this->format_term($this->resolution, $format);
	}

	public function set_resolution( $resolution ) {
		$this->set_assigned_term($resolution, self::$tax_resolution->get_id());
	}

	/**
	 *
	 * @param string $format
	 * @return object|string|int|NULL
	 */
	public function get_type( $format = 'id') {
		if ( is_null($this->type) ) {
			$this->type = $this->get_assigned_term(self::$tax_type->get_id(), 'object');
		}
		return $this->format_term($this->type, $format);
	}

	public function set_type( $type ) {
		$this->set_assigned_term($type, self::$tax_type->get_id());
	}

	public function get_assignee_id() {
		if ( is_null($this->assignee_id) ) {
			$this->assignee_id = (int)get_post_meta($this->post_id, self::META_KEY_ASSIGNEE, TRUE);
		}
		return $this->assignee_id;
	}

	public function set_assignee_id( $user_id ) {
		update_post_meta($this->post_id, self::META_KEY_ASSIGNEE, (int)$user_id);
		$this->assignee_id = $user_id;
	}

	public function get_project_id() {
		if ( is_null($this->project_id) ) {
			$this->project_id = (int)get_post_meta($this->post_id, self::META_KEY_PROJECT, TRUE);
		}
		return $this->project_id;
	}

	public function set_project_id( $project_id ) {
		update_post_meta($this->post_id, self::META_KEY_PROJECT, (int)$project_id);
		$this->project_id = $project_id;
	}

	public function get_assigned_term( $taxonomy, $format = 'id' ) {
		$term = NULL;
		$terms = wp_get_object_terms($this->post_id, $taxonomy);
		if ( $terms ) {
			$term = reset($terms);
		}
		return $this->format_term($term, $format);
	}

	public function set_assigned_term( $term_id, $taxonomy ) {
		wp_set_object_terms( $this->post_id, $term_id, $taxonomy );
		$this->flush();
	}

	/**
	 * Format a taxonomy term object according to $format
	 *
	 * @param object $term A WP term object
	 * @param string $format 'id', 'object', or 'slug'
	 *
	 * @return int|null|string
	 */
	private function format_term( $term, $format = 'id' ) {
		switch ( $format ) {
			case 'object':
				return $term?$term:NULL;
			case 'slug':
				return $term?$term->slug:'';
			case 'name':
				return $term?$term->name:'';
			case 'id':
			default:
				return $term?$term->term_id:0;
		}
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
		self::$cpt->slug = _x( 'issues', 'post type slug', 'buggypress' );
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

	/**
	 * Get a list of issue types, properly sorted.
	 *
	 * @static
	 * return array
	 */
	public static function get_types() {
		$terms = get_terms(self::$tax_type->get_id(), array(
			'hide_empty' => FALSE,
		));
		// TODO: sort
		return $terms;
	}

	/**
	 * Get a list of issue statuses, properly sorted.
	 *
	 * @static
	 * return array
	 */
	public static function get_statuses() {
		$terms = get_terms(self::$tax_status->get_id(), array(
			'hide_empty' => FALSE,
		));
		// TODO: sort
		return $terms;
	}

	/**
	 * Get a list of issue resolutions, properly sorted.
	 *
	 * @static
	 * return array
	 */
	public static function get_resolutions() {
		$terms = get_terms(self::$tax_resolution->get_id(), array(
			'hide_empty' => FALSE,
		));
		// TODO: sort
		return $terms;
	}

	/**
	 * Get a list of issue priorities, properly sorted.
	 *
	 * @static
	 * return array
	 */
	public static function get_priorities() {
		$terms = get_terms(self::$tax_priority->get_id(), array(
			'hide_empty' => FALSE,
		));
		// TODO: sort
		return $terms;
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

		new BuggyPress_TaxonomyOrder(self::$tax_priority->get_id());

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
		new BuggyPress_TaxonomyOrder(self::$tax_resolution->get_id());

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
		new BuggyPress_TaxonomyOrder(self::$tax_status->get_id());

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
		new BuggyPress_TaxonomyOrder(self::$tax_type->get_id());
	}

	private static function register_meta_boxes() {
		self::$mb_assignee = add_flightless_meta_box(self::POST_TYPE, 'BuggyPress_MB_Assignee');
		self::$mb_project = add_flightless_meta_box(self::POST_TYPE, 'BuggyPress_MB_IssueProject');
	}
}
