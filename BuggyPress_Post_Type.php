<?php
/**
 * User: jbrinley
 * Date: 5/15/11
 * Time: 9:52 PM
 */
 
abstract class BuggyPress_Post_Type extends BuggyPress_Plugin {
	const NONCE_ACTION = 'buggypress_save_post';
	const NONCE_NAME = 'buggypress_save_post_nonce';

	/**
	 * Create the instance of the class
	 *
	 * @static
	 * @return void
	 */
	abstract public static function init();

	/** Singleton */

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 * @static
	 * @return Loyalty_Activity_Post_Type
	 */
	abstract public static function get_instance();

	final public function __clone() {
		trigger_error("No cloning allowed!", E_USER_ERROR);
	}

	final public function __sleep() {
		trigger_error("No serialization allowed!", E_USER_ERROR);
	}

	/**
	 * Hello. What's your name?
	 * @var string
	 */
	protected $post_type_label_singular = '';
	protected $post_type_label_plural = '';
	protected $slug = 'activities';

	/**
	 * The ID of the post type
	 * @var string
	 */
	protected $post_type;

	protected function __construct() {
		if ( !$this->post_type ) {
			throw new UnexpectedValueException(self::__('Post type must be set'));
		}
		$this->add_hooks();
	}

	protected function add_hooks() {
		add_action('init', array($this, 'register_post_type'), 10, 0);
		add_action('post_submitbox_misc_actions', array($this, 'display_nonce'));
		add_action('save_post', array($this, 'save_meta_boxes'), 10, 2);
		add_filter('template_include', array( $this, 'select_post_template' ), 10, 1 );
	}



  /**
   * Register this post type with WordPress
   *
   * @return void
   */
	public function register_post_type() {
		$response = register_post_type($this->post_type, $this->post_type_args());
	}

	/**
	 * The the post type defined by this class
	 *
	 * @param string $format Either 'id' (for the post type ID) or 'object' (for the WP post type object)
	 * @return object|string
	 */
	public function get_post_type( $format = 'id' ) {
		switch ( $format ) {
			case 'object':
				return get_post_type_object($this->post_type);
			default:
				return $this->post_type;
		}
	}

	/**
	 * Return the slug of the supertype
	 *
	 * @return string supertype slug
	 */
	protected function get_slug() {
		return $this->slug;
	}

	/**
	 * Build the args array for the post type definition
	 *
	 * @return array
	 */
	protected function post_type_args() {
		$labels = $this->post_type_labels();
		$args = array(
			'labels' => $labels,
			'description' => self::__('A BuggyPress Post'),
			'public' => TRUE,
			'publicly_queryable' => TRUE,
			'show_ui' => TRUE,
			'show_in_menu' => TRUE,
			'menu_icon' => NULL,
			'capability_type' => 'post',
			'hierarchical' => FALSE,
			'supports' => array('title', 'editor', 'thumbnail', 'author'),
			'register_meta_box_cb' => array($this, 'register_meta_boxes'),
			'has_archive' => $this->rewrite_slug(),
			'rewrite' => array(
				'slug' => $this->rewrite_slug(),
				'with_front' => FALSE,
			),
			'menu_position' => 4,
		);

		return $args;
	}

	/**
	 * Get the rewrite slug for this post type
	 *
	 * @return string
	 */
	protected function rewrite_slug() {
		return $this->slug;
	}

	/**
	 * Build the labels array for the post type definition
	 *
	 * @param string $single
	 * @param string $plural
	 * @return array
	 */
	protected function post_type_labels( $single = '', $plural = '' ) {
		$single = $single?$single:$this->post_type_label('singular');
		$plural = $plural?$plural:$this->post_type_label('plural');
		$labels = array(
			'name' => self::__($plural),
			'singular_name' => self::__($single),
			'add_new' => self::__('Add New'),
			'add_new_item' => self::__('Add New '.$single),
			'edit_item' => self::__('Edit '.$single),
			'new_item' => self::__('New '.$single),
			'view_item' => self::__('View '.$single),
			'search_items' => self::__('Search '.$plural),
			'not_found' => self::__('No '.$plural.' Found'),
			'not_found_in_trash' => self::__('No '.$plural.' Found in Trash'),
			'menu_name' => self::__($plural),
		);

		return $labels;
	}

	protected function post_type_label( $quantity = 'singular' ) {
		switch ( $quantity ) {
			case 'plural':
				if ( $this->post_type_label_plural ) {
					return $this->post_type_label_plural;
				}
				return $this->post_type_label('singular').'s'; // a highly robust technique for making any word plural
			default:
				if ( $this->post_type_label_singular ) {
					return $this->post_type_label_singular;
				}
				return $this->post_type;
		}
	}

	/**
	 * Save the meta boxes for this post type
	 *
	 * @param int $post_id The ID of the post being saved
	 * @param object $post The post being saved
	 * @return void
	 */
	public function save_meta_boxes( $post_id, $post ) {
		if ( !$this->should_meta_boxes_be_saved($post_id, $post) ) {
			return;
		}
		global $wp_filter;
		$current = key($wp_filter['save_post']);



		/*
		 * if any of the meta boxes creates/updates a different
		 * post, we'll end up leaving the $wp_filter['save_post']
		 * array in an incorrect state
		 * see http://xplus3.net/2011/08/18/wordpress-action-nesting/
		 */
		if ( key($wp_filter['save_post']) != $current ) {
			reset($wp_filter['save_post']);
			foreach ( array_keys($wp_filter['save_post']) as $key ) {
				if ( $key == $current ) {
					break;
				}
				next($wp_filter['save_post']);
			}
		}
	}

	/**
	 * Put our nonce in the Publish box, so we can share it
	 * across all meta boxes
	 *
	 * @return void
	 */
	public function display_nonce() {
		global $post;
		if ( $post->post_type == $this->post_type ) {
			wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
		}
	}

	/**
	 * Make sure this is a save_post where we actually want to update the meta
	 *
	 * @param int $post_id
	 * @param object $post
	 * @return bool
	 */
	private function should_meta_boxes_be_saved( $post_id, $post ) {
		// we're not interested in some post types
		if ( $post->post_type != $this->post_type ) {
			return FALSE;
		}

		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if ( wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined('DOING_AJAX') || isset($_GET['bulk_edit']) ) {
			return FALSE;
		}

		// make sure this is a valid submission
		if ( !isset($_POST[self::NONCE_NAME]) || !wp_verify_nonce($_POST[self::NONCE_NAME], self::NONCE_ACTION) ) {
			return FALSE;
		}

		// looks like the answer is Yes
		return TRUE;
	}
}
