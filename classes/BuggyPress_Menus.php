<?php

class BuggyPress_Menus {
	/** @var BuggyPress_Menus */
	private static $instance;


	/**
	 * Create our menu meta box
	 *
	 * @wordpress-action load-nav-menus.php
	 */
	public function create_menu_metabox() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 0 );
		add_meta_box(
			'add-buggypress',
			__('BuggyPress'),
			array($this, 'populate_menu_metabox'),
			'nav-menus',
			'side',
			'default'
		);
	}

	public function admin_enqueue_scripts() {
		wp_enqueue_script('buggypress-menu', BuggyPress::plugin_url('resources/nav-menu-admin.js'), array('jquery'), FALSE, TRUE);
		wp_localize_script('buggypress-menu', 'BuggyPressMenu', array(
			'nonce' => wp_create_nonce('buggypress-menu'),
		));
	}

	/**
	 * Ajax callback for our menu items
	 *
	 * @wordpress-action wp_ajax_buggypress_add_to_menu
	 * @return void Exits the program on completion
	 */
	public function ajax_add_to_menu() {
		check_ajax_referer('buggypress-menu', 'buggypress_nonce');
		if ( empty($_POST['menu_items']) ) {
			die('-1');
		}
		require_once(ABSPATH.'wp-admin/includes/nav-menu.php');

		$menu_items = $_POST['menu_items'];
		$item_ids = array();
		foreach ( $menu_items as $item ) {
			$item_ids[] = $this->add_menu_item($item);
		}

		if ( is_wp_error( $item_ids) ) {
			die('-1');
		}

		// Set up menu items
		$output_menu_items = array();
		foreach ( $item_ids as $menu_item_id ) {
			$menu_obj = get_post( $menu_item_id );
			if ( !empty( $menu_obj->ID ) ) {
				$menu_obj = wp_setup_nav_menu_item( $menu_obj );
				$menu_obj->label = $menu_obj->title; // don't show "(pending)" in ajax-added items
				$menu_obj->type_label = 'BuggyPress';
				$output_menu_items[] = $menu_obj;
			}
		}

		// build the HTML output
		if ( !empty( $output_menu_items ) ) {
			$args = array(
				'after' => '',
				'before' => '',
				'link_after' => '',
				'link_before' => '',
				'walker' => new Walker_Nav_Menu_Edit(),
			);
			echo walk_nav_menu_tree( $output_menu_items, 0, (object) $args );
		}

		exit();
	}

	/**
	 * Set properties on our custom menu items
	 *
	 * @wordpress-filter wp_setup_nav_menu_item
	 * @param object $item
	 * @return object
	 */
	public function setup_menu_item( $item ) {
		if ( $item->type != 'buggypress-custom' ) {
			return $item;
		}
		$item->url = $this->get_menu_item_url($item->object);
		$item->type_label = 'BuggyPress';
		return $item;
	}

	/**
	 * Set the "current" status on menu items
	 *
	 * @wordpress-filter wp_nav_menu_objects
	 * @param array $items
	 * @return array
	 */
	public function set_current_menu_item( $items ) {
		foreach ( $items as $item ) {
			if ( 'buggypress-custom' == $item->type && $this->is_current_menu_item($item) ) {
				// set the item as current
				$item->current = TRUE;
				$item->classes[] = 'current-menu-item';

				// identify parents/ancestors
				$ancestors = array();
				$ancestor_id = (int) $item->db_id;
				while ( $ancestor_id = get_post_meta( $ancestor_id, '_menu_item_menu_item_parent', TRUE) ) {
					$ancestors[] = $ancestor_id;
				}

				// add classes to parents all the way up the tree
				if ( $ancestors ) {
					foreach ( $items as $key => $parent_item ) {
						if ( $parent_item->db_id == $item->menu_item_parent ) {
							$item->current_item_parent = TRUE;
							$item->classes[] = 'current_menu-parent';
						}
						if ( in_array($parent_item->db_id, $ancestors) ) {
							$item->current_item_ancestor = TRUE;
							$item->classes[] = 'current-menu-ancestor';
						}
					}
				}
			}
		}
		return $items;
	}

	/**
	 * Print the HTML for the buggypress menu metabox
	 */
	public function populate_menu_metabox() {
		global $nav_menu_selected_id;
		$projects = get_post_type_object('project');
		$issues = get_post_type_object( 'issue' );

		$items = array(
			'project' => $projects->label,
			'newissue' => $issues->labels->new_item,
		);

		include(BuggyPress::plugin_path('views/menu-meta-box.php'));
	}

	/**
	 * If the projects menu item is in the menu, add all
	 * projects as its children
	 *
	 * @wordpress-filter wp_get_nav_menu_items
	 *
	 * @param array $items
	 * @param string $menu
	 * @param array $args
	 *
	 * @return array
	 */
	public function add_project_submenu( $items, $menu, $args ) {
		foreach ( $items as $item ) {
			if ( $item->type == 'buggypress-custom' && $item->object == 'project' ) {
				$projects = $this->get_project_menu_items($item);
				if ( $projects ) {
					$items = array_merge($items, $projects);
				}
			}
		}
		return $items;
	}

	private function add_hooks() {
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'setup_menu_item' ), 10, 1 );
		if ( is_admin() ) {
			add_action( 'load-nav-menus.php', array( $this, 'create_menu_metabox' ), 10, 0 );
			add_action( 'wp_ajax_buggypress_add_to_menu', array( $this, 'ajax_add_to_menu' ), 10, 0 );
		} else {
			add_filter( 'wp_nav_menu_objects', array( $this, 'set_current_menu_item' ), 10, 1 );
			add_filter( 'wp_get_nav_menu_items', array( $this, 'add_project_submenu' ), 10, 3 );
		}
	}

	/**
	 * Add a buggypress menu item
	 *
	 * @param string $item_slug
	 * @return int The new menu item ID
	 */
	private function add_menu_item( $item_slug ) {
		switch ( $item_slug ) {
			case 'project':
				$pto = get_post_type_object('project');
				$data = array(
					'menu-item-title' => $pto->label,
					'menu-item-type' => 'buggypress-custom',
					'menu-item-object' => 'project',
					'menu-item-url' => $this->get_menu_item_url('project'),
				);
				break;
			case 'newissue':
				$data = array(
					'menu-item-title' => 'New Issue',
					'menu-item-type' => 'buggypress-custom',
					'menu-item-object' => 'newissue',
					'menu-item-url' => $this->get_menu_item_url('newissue'),
				);
				break;
			default:
				break;
		}
		if ( empty($data) ) {
			return 0;
		}
		return wp_update_nav_menu_item( 0, 0, $data );
	}

	/**
	 * Get the URL for a dynamic menu item
	 *
	 * @param string $item_slug
	 * @return string
	 */
	private function get_menu_item_url( $item_slug ) {
		switch ( $item_slug ) {
			case 'project':
				return get_post_type_archive_link('project');
			case 'newissue':
				$project = '';
				if ( !is_admin() ) {
					global $wp_query;
					if ( is_singular('issue') ) {
						$issue = new BuggyPress_Issue(get_queried_object_id());
						$project_id = $issue->get_project_id();
						$project_post = get_post($project_id);
						$project = $project_post->post_name;
					} elseif ( $project_query_var = get_query_var('project') ) {
						$project = $project_query_var;
					}
				}
				return home_url(BuggyPress_NewIssuePage::get_path($project));
		}
		return '';
	}

	private function is_current_menu_item( $item ) {
		if ( $item->object == 'project' ) {
			if ( is_post_type_archive('project') || is_singular('project') ) {
				return TRUE;
			}
			// TODO: set to TRUE if on an issue?
		} elseif ( $item->object == 'newissue' ) {
			if ( get_query_var('WP_Route') == 'buggypress_new_issue' ) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * Get menu items for all projects
	 *
	 * @param object $parent The parent menu item
	 * @return array Post objects converted to menu items, sorted alphabetically
	 */
	private function get_project_menu_items( $parent ) {
		// TODO: figure out how to cache this while taking into account access rules
		$projects = get_posts(array(
			'post_type' => BuggyPress_Project::POST_TYPE,
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
		));
		$projects = array_map('wp_setup_nav_menu_item', $projects);
		foreach ( $projects as $key => $project ) {
			$project->menu_item_parent = $parent->ID;
			$project->menu_order = $parent->menu_order.'.'.$key;
		}
		return $projects;
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
	 * @return BuggyPress_Menus
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
