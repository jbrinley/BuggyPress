<?php
 
class BuggyPress_Project extends BuggyPress_Post_Type {
	const POST_TYPE = 'project';
	protected $post_type_label_singular = 'Project';
	protected $post_type_label_plural = 'Projects';
	protected $slug = 'projects';
	protected $post_type = self::POST_TYPE;

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

	public static function get_administrators( $post_id ) {
		$project = self::get_instance();
		return $project->meta_boxes['BuggyPress_MB_Members']->get_administrators($post_id);
	}

	public static function get_members( $post_id ) {
		$project = self::get_instance();
		return $project->meta_boxes['BuggyPress_MB_Members']->get_members($post_id);
	}

	public static function get_visibility( $post_id ) {
		$project = self::get_instance();
		return $project->meta_boxes['BuggyPress_MB_Permissions']->get_visibility($post_id);
	}

	public static function get_commenting( $post_id ) {
		$project = self::get_instance();
		return $project->meta_boxes['BuggyPress_MB_Permissions']->get_commenting($post_id);
	}

	/**
	 * Determine if the user is eligible to view the project
	 *
	 * @static
	 * @param int $user_id
	 * @param int $project_id
	 * @return bool
	 */
	public static function user_can_view( $user_id, $project_id ) {
		$user = new WP_User($user_id);
		if ( $user->has_cap('read_projects') ) {
			return TRUE;
		}
		$visibility = self::get_visibility($project_id);
		if ( $visibility == BuggyPress_MB_Permissions::ALL ) {
			return TRUE; // everyone can view
		} elseif ( $visibility == BuggyPress_MB_Permissions::USERS ) {
			if ( $user_id > 0 ) {
				return TRUE; // all registered users can view
			}
		} else {
			$members = self::get_members($project_id);
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
	 * @param int $project_id
	 * @return bool
	 */
	public static function user_can_edit( $user_id, $project_id ) {
		$user = new WP_User($user_id);
		if ( $user->has_cap('edit_projects') ) {
			return TRUE; // site administrators can edit all projects
		}
		$admins = self::get_administrators($project_id);
		if ( in_array($user_id, $admins) ) {
			return TRUE; // project admins can edit their projects
		}
		return FALSE;
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

	protected function __construct() {
		parent::__construct();
		$this->add_meta_box('BuggyPress_MB_Members');
		$this->add_meta_box('BuggyPress_MB_Permissions');
	}

	protected function add_hooks() {
		parent::add_hooks();
		add_action('init', array($this, 'register_rewrite_tag'), 0, 0);
		add_action('init', array($this, 'register_permissions'), 10, 0);
		add_filter('posts_where', array($this, 'filter_query_where'), 10, 2);
		add_filter('post_type_link', array($this, 'add_parent_project_to_link'), 1, 3);
		add_filter('user_has_cap', array($this, 'user_has_cap'), 10, 3);
	}

	protected function post_type_args() {
		$args = parent::post_type_args();
		$args['capability_type'] = self::POST_TYPE;
		$args['capabilities'] = array(
			'read' => 'read_projects',
		);
		$args['supports'] = array('title', 'editor', 'thumbnail', 'revisions');
		return $args;
	}

	public function register_rewrite_tag() {
		global $wp_rewrite;
		$wp_rewrite->add_rewrite_tag('%parent_project%', $this->slug.'/([^/]+)/', 'parent_project=');
	}

	public function add_parent_project_to_link( $post_link, $post = 0, $leavename = FALSE ) {
		if ( strpos('%parent_project%', $post_link) !== FALSE ) {
			return $post_link;
		}
		if ( is_object($post) ) {
			$post_id = $post->ID;
		} else {
			$post_id = $post;
			$post = get_post($post_id);
		}
		$project_slug = self::__('none');
		$project_id = bp_get_the_project( $post_id );
		if ( $project_id ) {
			$project_slug = get_page_uri($project_id);
		}

		// put the project slug in place of %parent_project%
		return str_replace('%parent_project%', $this->slug.'/'.$project_slug.'/', $post_link);
	}

	public function register_permissions() {
		foreach ( array( 'administrator', 'editor') as $role_name ) {
			$role = get_role($role_name);
			$role->add_cap('read_projects');
			$role->add_cap('read_private_projects');
			$role->add_cap('edit_projects');
			$role->add_cap('edit_others_projects');
			$role->add_cap('publish_projects');
		}
	}

	/**
	 * Filter out projects that the user doesn't have access to
	 * 
	 * @param string $where
	 * @param WP_Query $query
	 * @return string
	 */
	public function filter_query_where( $where, $query ) {
		if ( $query->query_vars['post_type'] == self::POST_TYPE
				 || ( is_array($query->query_vars['post_type']) && in_array(self::POST_TYPE, $query->query_vars['post_type']) )
		) {
			if ( !current_user_can('read_projects') ) {
				// get a list of all public projects
				$projects = array();
				$posts = get_posts(array(
					'post_type' => self::POST_TYPE,
					'meta_query' => array(
						array(
							'key' => BuggyPress_MB_Permissions::META_KEY_VISIBILITY,
							'value' => is_user_logged_in()?array(BuggyPress_MB_Permissions::ALL, BuggyPress_MB_Permissions::USERS):array(BuggyPress_MB_Permissions::ALL),
							'compare' => 'IN'
						),
					),
				));
				foreach ( $posts as $post ) {
					$projects[] = $post->ID;
				}
				if ( is_user_logged_in() ) {
					// get a list of projects the user is a member of
					$posts = get_posts(array(
						'post_type' => self::POST_TYPE,
						'meta_query' => array(
							array(
								'key' => BuggyPress_MB_Permissions::META_KEY_VISIBILITY,
								'value' => array(BuggyPress_MB_Permissions::MEMBERS),
								'compare' => 'IN'
							),
							array(
								'key' => BuggyPress_MB_Members::META_KEY_MEMBERS,
								'value' => get_current_user_id(),
							),
						),
					));
					foreach ( $posts as $post ) {
						$projects[] = $post->ID;
					}
				}
				global $wpdb;
				if ( $projects ) {
					$where .= " AND ( {$wpdb->posts}.post_type != '{$this->post_type}' OR {$wpdb->posts}.ID IN (".implode(',', $projects).") )";
				} else {
					$where .= " AND {$wpdb->posts}.post_type != '{$this->post_type}'";
				}
			}
		}
		return $where;
	}

	/**
	 * Grant meta capabilities for projects to users, as appropriate
	 *
	 * @param array $caps
	 * @param array $cap
	 * @param array $args
	 * @return array
	 */
	public function user_has_cap( $caps, $cap, $args ) {
		$the_cap = is_array($cap)?$cap[0]:$cap;
		switch ( $the_cap ) {
			case 'edit_project':
			case 'delete_project':
				if ( self::user_can_edit((int)($args[1]), (int)$args[2]) ) {
					$caps[$the_cap] = TRUE;
				}
				break;
			case 'read_project':
				if ( self::user_can_view((int)($args[1]), (int)$args[2]) ) {
					$caps[$the_cap] = TRUE;
				}
				break;
			default:
				break;
		}
		return $caps;
	}
}
