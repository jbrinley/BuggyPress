<?php

class BuggyPress_Permissions {
	/** @var BuggyPress_Permissions */
	private static $instance;

	private $permissions_to_register = array();

	public function add_permissions( $capability_type, $role, $caps = array('read', 'read_private', 'edit', 'edit_others', 'publish') ) {
		foreach ( $caps as $base ) {
			$permission = $base.'_'.$capability_type;
			$this->permissions_to_register[$role][] = $permission;
		}
	}

	/**
	 * Register WordPress hooks
	 */
	private function add_hooks() {
		add_action('init', array($this, 'register_permissions'), 10, 0);
		//add_filter('posts_where', array($this, 'filter_query_where'), 10, 2);
		//add_filter('posts_join', array($this, 'filter_query_join'), 10, 2);
	}

	/**
	 * Add the project meta to the query
	 *
	 * @param string $join
	 * @param WP_Query $query
	 * @return string
	 */
	public function filter_query_join( $join, $query ) {
		global $wpdb;
		if ( $this->is_post_type_query(BuggyPress_Issue::POST_TYPE, $query) && !current_user_can('read_issues') ) {
			$join .= " LEFT JOIN {$wpdb->postmeta} bp_issue_project_meta ON {$wpdb->posts}.ID=bp_issue_project_meta.post_id AND bp_issue_project_meta.meta_key='".BuggyPress_Issue::META_KEY_PROJECT."'";
		}
		return $join;
	}

	/**
	 * Filter out issues from projects that the user doesn't have access to
	 *
	 * @param string $where
	 * @param WP_Query $query
	 * @return string
	 */
	public function filter_query_where( $where, $query ) {
		if ( $this->is_post_type_query(BuggyPress_Issue::POST_TYPE, $query) && !current_user_can('read_issues') ) {
			// Filter issue queries if the user can't read all issues
			$projects = $this->get_visible_project_ids();

			global $wpdb;
			if ( $projects ) {
				$where .= $wpdb->prepare(" AND ( {$wpdb->posts}.post_type != %s OR bp_issue_project_meta.meta_value IN (".implode(',', array_map('intval', $projects)).") )", BuggyPress_Issue::POST_TYPE);
			} else {
				$where .= $wpdb->prepare(" AND {$wpdb->posts}.post_type != %s", BuggyPress_Issue::POST_TYPE);
			}
		} elseif ( $this->is_post_type_query(BuggyPress_Project::POST_TYPE, $query) && !current_user_can('read_projects') ) {
			// Filter project queries if the user can't read all projects
			$projects = $this->get_visible_project_ids();

			global $wpdb;
			if ( $projects ) {
				$where .= $wpdb->prepare( " AND ( {$wpdb->posts}.post_type != %s OR {$wpdb->posts}.ID IN (".implode(',', array_map('intval', $projects)).") )", BuggyPress_Project::POST_TYPE);
			} else {
				$where .= $wpdb->prepare(" AND {$wpdb->posts}.post_type != %s", BuggyPress_Project::POST_TYPE);
			}
		}
		return $where;
	}

	/**
	 * Get the IDs of all projects the current user can access
	 *
	 * @return array
	 */
	private function get_visible_project_ids() {
		$projects = get_posts(array(
			'post_type' => BuggyPress_Project::POST_TYPE,
			'meta_query' => array(
				array(
					'key' => BuggyPress_Project::META_KEY_VISIBILITY,
					'value' => is_user_logged_in()?array(BuggyPress_MB_Permissions::ALL, BuggyPress_MB_Permissions::USERS):array(BuggyPress_MB_Permissions::ALL),
					'compare' => 'IN'
				),
			),
			'fields' => 'ids',
		));
		if ( is_user_logged_in() ) {
			// get a list of projects the user is a member of
			$more_projects = get_posts(array(
				'post_type' => BuggyPress_Project::POST_TYPE,
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
				'fields' => 'ids',
			));
			$projects = array_merge($projects, $more_projects);
		}
		return $projects;
	}

	/**
	 * Determine if the query explicitly references the given post type
	 *
	 * @param string $post_type
	 * @param WP_Query $query
	 *
	 * @return bool
	 */
	private function is_post_type_query( $post_type, $query ) {
		if ( empty($query->query_vars['post_type']) ) {
			return FALSE;
		}
		if ( $query->query_vars['post_type'] == $post_type ) {
			return TRUE;
		}
		if ( is_array($query->query_vars['post_type']) && in_array($post_type, $query->query_vars['post_type']) ) {
			return TRUE;
		}
		return FALSE;
	}

	/*
	 * Hook Callbacks
	 ********************************************************************/


	public function register_permissions() {
		foreach ( $this->permissions_to_register as $role_name => $permissions ) {
			/** @var $role WP_Role */
			$role = get_role($role_name);
			foreach ( $permissions as $permission ) {
				$role->add_cap($permission);
			}
		}
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
	 * @return BuggyPress_Permissions
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
