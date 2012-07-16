<?php

/**
 * Allows for manual sorting of taxonomy terms
 */
class BuggyPress_TaxonomyOrder {
	const OPTION_PREFIX = 'bp_sort_order_';

	private $taxonomy = '';

	public function __construct( $taxonomy ) {
		$this->taxonomy = $taxonomy;
		$this->add_hooks();
	}

	/**
	 * Add a column for our weight field
	 *
	 * @param array $columns
	 * @return array
	 */
	public function register_list_table_columns( $columns ) {
		$columns['bp_sort_order'] = __( 'Sort', 'buggypress' );
		return $columns;
	}

	/**
	 * Render the weight field
	 *
	 * @param string $content
	 * @param string $column
	 * @param int $term_id
	 * @return string
	 */
	public function render_list_table_columns( $content, $column, $term_id ) {
		if ( $column == 'bp_sort_order' ) {
			$weight = $this->get_term_weight($term_id);
			$content = sprintf('<input type="text" size="2" value="%d" name="%s[%d]" />', $weight, 'bp_sort_order_field', $term_id);

		}
		return $content;
	}

	/**
	 * Enqueue scripts and styles for the edit-tags page
	 *
	 * @param string $hook_suffix
	 */
	public function enqueue_admin_resources( $hook_suffix ) {
		if ( $hook_suffix == 'edit-tags.php' ) {
			global $taxonomy;
			if ( $taxonomy == $this->taxonomy ) {
				wp_enqueue_script( 'buggypress-taxonomy-sort', BuggyPress::plugin_url('resources/taxonomy-sort.js'), array( 'jquery-ui-sortable' ), FALSE, TRUE );
				wp_enqueue_style( 'buggypress-taxonomy-sort', BuggyPress::plugin_url('resources/taxonomy-sort.css') );
			}
		}
	}

	/**
	 * Process ajax requests to manipulate term weights
	 *
	 * @todo Handle taxonomies that span multiple pages
	 */
	public function handle_ajax_sort() {
		if ( !empty($_POST['taxonomy']) && $_POST['taxonomy'] == $this->taxonomy && !empty($_POST['terms']) ) {
			foreach ( $_POST['terms'] as $term_id => $weight ) {
				$this->set_term_weight($term_id, $weight);
			}
		}
	}

	/**
	 * If the orderby arg to get_terms is the default ('name'),
	 * change to the manual sort order
	 *
	 * @param array $args
	 * @param array $taxonomies
	 * @return array
	 */
	public function filter_get_terms_args( $args, $taxonomies ) {
		if ( count($taxonomies) == 1 && reset($taxonomies) == $this->taxonomy ) {
			if ( $args['orderby'] == 'name' ) {
				$args['orderby'] = 'sort_order';
				$args['sort_taxonomy'] = $this->taxonomy;
			}
		}
		return $args;
	}

	/**
	 * If we're sorting, set the ORDER BY clause
	 *
	 * @param string $orderby
	 * @param array $args
	 * @return string
	 */
	public function filter_get_terms_orderby( $orderby, $args ) {
		if ( !empty($args['orderby']) && $args['orderby'] == 'sort_order' && !empty($args['sort_taxonomy']) && $args['sort_taxonomy'] == $this->taxonomy ) {
			$order = $this->get_order();
			if ( $order ) {
				asort($order, SORT_NUMERIC);
				$term_ids = implode(',', array_map('intval', array_keys($order)));
				$orderby = "FIELD(t.term_id,$term_ids)";
			}
		}
		return $orderby;
	}

	private function add_hooks() {
		// modify the list table
		add_filter( 'manage_edit-'.$this->taxonomy.'_columns', array( $this, 'register_list_table_columns' ), 100, 1 );
		add_filter( 'manage_'.$this->taxonomy.'_custom_column', array( $this, 'render_list_table_columns'), 10, 3 );

		// save changes from the list table
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_resources' ), 10, 1 );
		add_action( 'wp_ajax_buggpress_taxonomy_sort', array( $this, 'handle_ajax_sort' ), 10, 2 );

		// sort terms
		add_filter( 'get_terms_args', array( $this, 'filter_get_terms_args' ), 10, 2 );
		add_filter( 'get_terms_orderby', array( $this, 'filter_get_terms_orderby' ), 10, 2 );
	}

	/**
	 * Get the weight of the term in this taxonomy
	 *
	 * @param $term_id
	 * @return int
	 */
	private function get_term_weight( $term_id ) {
		$order = $this->get_order();
		$weight = 0;
		if ( !empty($order[$term_id]) ) {
			$weight = (int)$order[$term_id];
		}
		return $weight;
	}

	/**
	 * Set the weight of the term
	 *
	 * @param int $term_id
	 * @param int $weight
	 */
	private function set_term_weight( $term_id, $weight ) {
		$order = $this->get_order();
		$order[(int)$term_id] = (int)$weight;
		$this->set_order($order);
	}

	/**
	 * Get the order of terms for this taxonomy
	 *
	 * @return array Keys are term IDs, values are weights
	 */
	private function get_order() {
		$option = $this->option_name();
		$value = get_option( $option, array() );
		if ( !is_array($value) ) {
			$value = array();
		}
		return $value;
	}

	/**
	 * Update the option in the DB
	 *
	 * @param array $order
	 */
	private function set_order( $order ) {
		$option = $this->option_name();
		if ( !is_array($order) ) {
			$order = array();
		}
		update_option($option, $order);
	}

	/**
	 * Get the name of the option to use to store this taxonomy's order
	 * @return string
	 */
	private function option_name() {
		return self::OPTION_PREFIX.$this->taxonomy;
	}
}
