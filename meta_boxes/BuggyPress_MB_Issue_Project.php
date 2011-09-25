<?php
 
class BuggyPress_MB_Issue_Project extends BuggyPress_Meta_Box {
	const META_KEY_PROJECT = '_buggypress_project';
	const FIELD_PROJECT = 'buggypress_project';

	protected $defaults = array(
		'title' => 'Project',
		'context' => 'side',
		'priority' => 'default',
		'callback_args' => NULL,
	);

	public function render( $post ) {
		$project_post_type = BuggyPress_Project::get_instance();
		$projects = get_posts(array(
			'post_type' => $project_post_type->get_post_type(),
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
		));
		$current_project = $this->get_project($post->ID);
		include(self::plugin_path('views'.DIRECTORY_SEPARATOR.'meta-box-issue-project.php'));
	}

	public function save( $post_id, $post ) {
		if ( isset($_POST[self::FIELD_PROJECT]) ) {
			$this->set_project($post_id, (int)$_POST[self::FIELD_PROJECT]);
		}
	}

	public function get_project( $post_id ) {
		return (int)get_post_meta($post_id, self::META_KEY_PROJECT, TRUE);
	}

	public function set_project( $post_id, $user_id ) {
		update_post_meta($post_id, self::META_KEY_PROJECT, (int)$user_id);
	}
}
