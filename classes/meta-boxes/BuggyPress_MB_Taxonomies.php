<?php
 
class BuggyPress_MB_Taxonomies extends Flightless_Meta_Box {
	const FIELD_GROUP = 'buggypress_taxonomies';
	protected $taxonomies = array();

	public function __construct( $id, $args = array() ) {
		$this->defaults['title'] = __('Issue Details', 'buggypress');
		$taxonomies = array();
		if ( isset($args['taxonomies']) ) {
			$taxonomies = $args['taxonomies'];
			unset($args['taxonomies']);
		}

		parent::__construct($id, $args);

		foreach ( $taxonomies as $tax => $options ) {
			if ( !is_array($options) ) {
				$tax = $options;
				$options = array();
			}
			$defaults = array(
				'show_option_all' => '',
				'show_option_none' => '',
				'orderby' => 'id',
				'order' => 'ASC',
				'show_last_update' => 0,
				'show_count' => 0,
				'hide_empty' => 0,
				'child_of' => 0,
				'exclude' => '',
				'echo' => 1,
				'selected' => 0,
				'hierarchical' => 0,
				'name' => self::FIELD_GROUP."[$tax]",
				'id' => self::FIELD_GROUP.'-'.$tax,
				'class' => 'postform',
				'depth' => 0,
				'tab_index' => 0,
				'taxonomy' => $tax,
				'hide_if_empty' => FALSE,
				'label' => $tax,
			);
			$options = wp_parse_args($options, $defaults);
			$this->taxonomies[$tax] = $options;
		}

		add_filter('buggpress_issue_changes', array($this, 'filter_change_list'), 10, 2);
	}

	public function render( $post ) {
		foreach ( $this->taxonomies as $taxonomy => $args ) {
			$this->taxonomies[$taxonomy]['selected'] = $this->get_current_value($post->ID, $taxonomy);
		}
		include(BuggyPress::plugin_path('views'.DIRECTORY_SEPARATOR.'meta-box-taxonomies.php'));
	}

	public function save( $post_id, $post ) {
		if ( !isset($_POST[self::FIELD_GROUP]) || !is_array($_POST[self::FIELD_GROUP]) ) {
			return;
		}
		foreach ( $_POST[self::FIELD_GROUP] as $taxonomy => $term_id ) {
			$this->set_value($post_id, (int)$term_id, $taxonomy);
		}
	}

	/**
	 * Set the term for the post
	 *
	 * @param int $post_id
	 * @param int|string $term_id Term ID or slug
	 * @param string $taxonomy
	 */
	public function set_value( $post_id, $term_id, $taxonomy ) {
		wp_set_object_terms( $post_id, $term_id, $taxonomy );
	}

	/**
	 * Get the currently selected term for the given post and taxonomy
	 *
	 * @param int $post_id
	 * @param string $taxonomy
	 * @return int
	 */
	public function get_current_value( $post_id, $taxonomy, $format = 'id' ) {
		$current = wp_get_object_terms($post_id, $taxonomy);
		if ( $current && $term = reset($current) ) {
			switch ( $format ) {
				case 'object':
					return $term;
				case 'slug':
					return $term->slug;
				case 'id':
				default:
					return $term->term_id;
			}
		}
		return 0;
	}

	/**
	 * Get issue details that have changed
	 *
	 * @param array $changes
	 * @param int $post_id
	 * @return array
	 */
	public function filter_change_list( $changes, $post_id ) {
		if ( !isset($_POST[self::FIELD_GROUP]) || !is_array($_POST[self::FIELD_GROUP]) ) {
			return $changes;
		}
		foreach ( $this->taxonomies as $taxonomy => $args ) {
			if ( isset($_POST[self::FIELD_GROUP][$taxonomy]) ) {
				$current_id = $this->get_current_value($post_id, $taxonomy);
				$current = get_term($current_id, $taxonomy);
				if ( $current->term_id != $_POST[self::FIELD_GROUP][$taxonomy] ) {
					$new = get_term($_POST[self::FIELD_GROUP][$taxonomy], $taxonomy);
					if ( $new ) {
						$changes[$taxonomy] = array(
							'label' => $args['label'],
							'old' => $current->name,
							'new' => $new->name,
						);
					}
				}
			}
		}
		return $changes;
	}
}
