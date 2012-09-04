<?php
 
class BuggyPress_MB_IssueProject extends Flightless_Meta_Box {
	const FIELD_PROJECT = 'buggypress_project';

	public function __construct( $id, $args = array() ) {
		$this->defaults['title'] = __('Project', 'buggypress');
		$this->defaults['contect'] = 'side';
		parent::__construct($id, $args);
	}

	public function render( $post ) {
		$issue = new BuggyPress_Issue($post->ID);
		$projects = get_posts(array(
			'post_type' => BuggyPress_Project::POST_TYPE,
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
		));
		$current_project = $issue->get_project_id();
		include(BuggyPress::plugin_path('views/admin/meta-box-issue-project.php'));
	}

	public function save( $post_id, $post ) {
		if ( isset($_POST[self::FIELD_PROJECT]) ) {
			$issue = new BuggyPress_Issue($post_id);
			$issue->set_project_id((int)$_POST[self::FIELD_PROJECT]);
		}
	}
}
