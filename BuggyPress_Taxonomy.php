<?php

abstract class BuggyPress_Taxonomy extends BuggyPress_Plugin {
	/**
	 * @var array Post types this taxonomy will apply to
	 */
	protected $post_types = array();

	/**
	 * Hello. What's your name?
	 * @var string
	 */
	protected $label_singular = '';
	protected $label_plural = '';
	protected $id = '';

	/**
	 * @var array Terms that will be created the first time this taxonomy is registered
	 */
	protected $default_terms = array();

	private static $instance;
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
	 * @return BuggyPress_Taxonomy
	 */
	abstract public static function get_instance();

	final public function __clone() {
		trigger_error("No cloning allowed!", E_USER_ERROR);
	}

	final public function __sleep() {
		trigger_error("No serialization allowed!", E_USER_ERROR);
	}

	protected function __construct() {
		$this->add_hooks();
	}

	protected function add_hooks() {
		add_action('init', array($this, 'register_taxonomy'), 10, 0);
	}

	public function get_id() {
		return $this->id;
	}

	public function add_post_type( $post_type ) {
		$this->post_types[] = $post_type;
	}

	public function register_taxonomy() {
		register_taxonomy($this->id, $this->post_types, $this->register_taxonomy_args());
		foreach ( $this->post_types as $pt ) {
			register_taxonomy_for_object_type($this->id, $pt);
		}
		$this->create_default_terms();
	}

	protected function register_taxonomy_args() {
		$args = array(
			'labels' => $this->get_labels(),
			'public' => TRUE,
			'rewrite' => array(
				'slug' => $this->id,
				'with_front' => TRUE,
				'hierarchical' => FALSE,
			),
		);
		return $args;
	}

	protected function get_labels( $single = '', $plural = '' ) {
		$single = $single?$single:$this->taxonomy_label('singular');
		$plural = $plural?$plural:$this->taxonomy_label('plural');
		$labels = array(
			'name' => $plural,
			'singular_name' => $single,
			'search_items' => self::__("Search $plural"),
			'popular_items' => self::__("Popular $plural"),
			'all_items' => self::__("All $plural"),
			'parent_item' => self::__("Parent $single"),
			'parent_item_colon' => self::__("Parent $single:"),
			'edit_item' => self::__("Edit $single"),
			'update_item' => self::__("Update $single"),
			'add_new_item' => self::__("Add New $single"),
			'new_item_name' => self::__("New $single Name"),
			'separate_items_with_commas' => self::__("Separate terms with commas"),
			'add_or_remove_items' => self::__("Add or Remove $plural"),
			'choose_from_most_used' => self::__("Choose from the most used terms"),
		);
		return $labels;
	}

	protected function taxonomy_label( $quantity = 'singular' ) {
		switch ( $quantity ) {
			case 'plural':
				if ( $this->label_plural ) {
					return $this->label_plural;
				}
				return $this->taxonomy_label('singular').'s'; // a highly robust technique for making any word plural
			default:
				if ( $this->label_singular ) {
					return $this->label_singular;
				}
				return $this->id;
		}
	}

	protected function create_default_terms() {
		if ( !$this->default_terms ) {
			return;
		}
		$option = 'buggypress_taxonomy_'.$this->id.'_initialized';
		$done = get_option($option, FALSE);
		if ( !$done ) {
			foreach ( $this->default_terms as $slug => $data ) {
				if ( !is_array($data) ) {
					$data = array('name' => (string)$data);
				}
				$defaults = array(
					'slug' => $slug,
					'name' => $slug,
					'description' => '',
				);
				$data = wp_parse_args($data, $defaults);
				$data['name'] = self::__($data['name']);
				$data['description'] = self::__($data['description']);
				if ( term_exists($slug, $this->id) ) {
					continue; // already created. How did that happen?
				}
				wp_insert_term($data['name'], $this->id, $data);
			}
			update_option($option, TRUE);
		}
	}

}
