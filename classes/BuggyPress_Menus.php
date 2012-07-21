<?php

class BuggyPress_Menus {
	/** @var BuggyPress_Menus */
	private static $instance;


	/**
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
	 * @wordpress-action wp_ajax_buggypress_add_to_menu
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
	 * @param $item
	 * @wordpress-filter wp_setup_nav_menu_item
	 */
	public function setup_menu_item( $item ) {
		if ( $item->type != 'buggypress-custom' ) {
			return $item;
		}
		$item->url = $this->get_menu_item_url($item->object);
		return $item;
	}

	/**
	 * @wordpress-filter wp_nav_menu_objects
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

	public function populate_menu_metabox() {
		global $nav_menu_selected_id;
		$projects = get_post_type_object('project');
		$items = array(
			'project' => $projects->label,
		);

		include(BuggyPress::plugin_path('views/menu-meta-box.php'));
	}

	private function add_hooks() {
		add_action( 'load-nav-menus.php', array( $this, 'create_menu_metabox' ), 10, 0 );
		add_action( 'wp_ajax_buggypress_add_to_menu', array( $this, 'ajax_add_to_menu' ), 10, 0 );
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'setup_menu_item' ), 10, 1 );
		add_filter( 'wp_nav_menu_objects', array( $this, 'set_current_menu_item' ), 10, 1 );
	}

	private function add_menu_item( $item_slug ) {
		switch ( $item_slug ) {
			case 'project':
				$pto = get_post_type_object('project');
				$data = array(
					'menu-item-title' => $pto->label,
					'menu-item-type' => 'buggypress-custom',
					'menu-item-object' => 'project',
					'menu-item-url' => get_post_type_archive_link('project'),
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

	private function get_menu_item_url( $item_slug ) {
		switch ( $item_slug ) {
			case 'project':
				return get_post_type_archive_link('project');
		}
		return '';
	}

	private function is_current_menu_item( $item ) {
		if ( $item->object == 'project' ) {
			if ( is_post_type_archive('project') || is_singular('project') ) {
				return TRUE;
			}
			// TODO: set to TRUE if on an issue?
		}
		return FALSE;
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
