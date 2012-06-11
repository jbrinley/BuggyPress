<?php

class BuggyPress_Project {
	const POST_TYPE = 'project';

	private $post_id = 0;

	/**
	 * @var BuggyPress_Post_Type
	 */
	private static $cpt = NULL;
	/**
	 * @var BuggyPress_MB_Permissions
	 */
	private static $mb_permissions = NULL;
	/**
	 * @var BuggyPress_MB_Members
	 */
	private static $mb_members = NULL;

	public function __construct( $post_id ) {
		if ( !(int)$post_id ) {
			throw new InvalidArgumentException(__('A valid post ID must be supplied.', 'buggypress'));
		}
		$this->post_id = $post_id;
	}

	public function get_administrators() {
		return self::$mb_members->get_administrators($this->post_id);
	}

	public function get_members() {
		return self::$mb_members->get_members($this->post_id);
	}

	public function get_visibility() {
		return self::$mb_permissions->get_visibility($this->post_id);
	}

	public function get_commenting() {
		return self::$mb_permissions->get_commenting($this->post_id);
	}

	/**
	 * Determine if the user is eligible to view the project
	 *
	 * @static
	 * @param int $user_id
	 * @return bool
	 */
	public function user_can_view( $user_id ) {
		$user = new WP_User($user_id);
		if ( $user->has_cap('read_projects') ) {
			return TRUE;
		}
		$visibility = $this->get_visibility();
		if ( $visibility == BuggyPress_MB_Permissions::ALL ) {
			return TRUE; // everyone can view
		} elseif ( $visibility == BuggyPress_MB_Permissions::USERS ) {
			if ( $user_id > 0 ) {
				return TRUE; // all registered users can view
			}
		} else {
			$members = $this->get_members();
			if ( in_array($user_id, $members) ) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Determine if the user is eligible to edit the project
	 *
	 * @static
	 * @param int $user_id
	 * @return bool
	 */
	public function user_can_edit( $user_id ) {
		$user = new WP_User($user_id);
		if ( $user->has_cap('edit_projects') ) {
			return TRUE; // site administrators can edit all projects
		}
		$admins = $this->get_administrators();
		if ( in_array($user_id, $admins) ) {
			return TRUE; // project admins can edit their projects
		}
		return FALSE;
	}


	/**
	 * @static
	 * Intialize the post type and related features
	 */
	public static function init() {
		self::create_post_type();
		add_action( 'init', array( __CLASS__, 'register_rewrite_tag' ), 0, 0 );
		add_filter( 'post_type_link', array( __CLASS__, 'add_parent_project_to_link' ), 1, 3 );
	}

	public static function create_post_type() {
		self::$cpt = new BuggyPress_Post_Type( self::POST_TYPE );
		self::$cpt->set_post_type_label( __('Project', 'buggypress'), __('Projects', 'buggypress') );
		self::$cpt->slug = _x( 'projects', 'post type slug', 'buggypress' );
		self::$cpt->remove_support(array('author'));

		self::register_taxonomies();
		self::register_meta_boxes();

		self::$cpt->capability_type = 'projects';
		self::$cpt->capabilities = array(
			'read' => 'read_projects',
		);
		$permitter = BuggyPress_Permissions::get_instance();
		$permitter->add_permissions('projects', 'administrator');
		$permitter->add_permissions('projects', 'editor');
	}

	private static function register_taxonomies() {
		// no taxonomies
	}

	private static function register_meta_boxes() {
		self::$mb_permissions = add_flightless_meta_box(self::POST_TYPE, 'BuggyPress_MB_Permissions');
		self::$mb_members = add_flightless_meta_box(self::POST_TYPE, 'BuggyPress_MB_Members');
	}

	public static function register_rewrite_tag() {
		global $wp_rewrite;
		$wp_rewrite->add_rewrite_tag('%parent_project%', self::$cpt->slug.'/([^/]+)/', 'parent_project=');
	}

	public static function add_parent_project_to_link( $post_link, $post = 0, $leavename = FALSE ) {
		if ( strpos('%parent_project%', $post_link) !== FALSE ) {
			return $post_link;
		}
		if ( is_object($post) ) {
			$post_id = $post->ID;
		} else {
			$post_id = $post;
			$post = get_post($post_id);
		}
		$project_slug = _x('none', 'project parent slug', 'buggypress');
		$project_id = bp_get_the_project( $post_id );
		if ( $project_id ) {
			$project_slug = get_page_uri($project_id);
		}

		// put the project slug in place of %parent_project%
		return str_replace('%parent_project%', self::$cpt->slug.'/'.$project_slug.'/', $post_link);
	}

}
