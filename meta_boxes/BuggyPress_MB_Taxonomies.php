<?php
 
class BuggyPress_MB_Taxonomies extends BuggyPress_Meta_Box {
	const FIELD_GROUP = 'buggypress_taxonomies';
	protected $taxonomies = array();

	protected $defaults = array(
		'title' => 'Issue Details',
		'context' => 'side',
		'priority' => 'default',
		'callback_args' => NULL,
	);

	public function __construct( $id, $args = array() ) {
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
				'hide_if_empty' => false,
				'label' => $tax,
			);
			$options = wp_parse_args($options, $defaults);
			$this->taxonomies[$tax] = $options;
		}
	}

	public function render( $post ) {
		foreach ( $this->taxonomies as $taxonomy => $args ) {
    	$current = wp_get_object_terms($post->ID, $taxonomy);
			if ( $current && $term = reset($current) ) {
				$this->taxonomies[$taxonomy]['selected'] = $term->term_id;
			}
		}
		include(self::plugin_path('views'.DIRECTORY_SEPARATOR.'meta-box-taxonomies.php'));
	}

	public function save( $post_id, $post ) {
		if ( !isset($_POST[self::FIELD_GROUP]) || !is_array($_POST[self::FIELD_GROUP]) ) {
			return;
		}
		foreach ( $_POST[self::FIELD_GROUP] as $taxonomy => $term_id ) {
      wp_set_object_terms($post_id, (int)$term_id, $taxonomy);
		}
	}
}
