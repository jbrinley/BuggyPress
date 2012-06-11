<?php
 
class BuggyPress_MB_IssueProject extends Flightless_Meta_Box {
	const META_KEY_PROJECT = '_buggypress_project';
	const FIELD_PROJECT = 'buggypress_project';

	public function __construct( $id, $args = array() ) {
		$this->defaults['title'] = __('Project', 'buggypress');
		$this->defaults['contect'] = 'side';
		parent::__construct($id, $args);
	}

	public function render( $post ) {
		$projects = get_posts(array(
			'post_type' => BuggyPress_Project::POST_TYPE,
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
		));
		$current_project = $this->get_project($post->ID);
		include(BuggyPress::plugin_path('views'.DIRECTORY_SEPARATOR.'meta-box-issue-project.php'));
	}

	public function save( $post_id, $post ) {
		if ( isset($_POST[self::FIELD_PROJECT]) ) {
			$this->set_project($post_id, (int)$_POST[self::FIELD_PROJECT]);
		}
	}

	public function get_project( $post_id, $format = 'id' ) {
		$project_id = (int)get_post_meta($post_id, self::META_KEY_PROJECT, TRUE);
		switch ( $format ) {
			case 'object':
				return $project_id?get_post($project_id):NULL;
			case 'id':
			default:
				return $project_id;
		}
	}

	public function set_project( $post_id, $project_id ) {
		update_post_meta($post_id, self::META_KEY_PROJECT, (int)$project_id);
	}
}
