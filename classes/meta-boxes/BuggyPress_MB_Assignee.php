<?php
 
class BuggyPress_MB_Assignee extends Flightless_Meta_Box {
	const FIELD_ASSIGNEE = 'buggypress_assignee';

	public function __construct( $id, $args = array() ) {
		$this->defaults['title'] = __('Assignee', 'buggypress');
		$this->defaults['contect'] = 'side';
		parent::__construct($id, $args);
	}

	public function render( $post ) {
		$issue = new BuggyPress_Issue($post->ID);
		$users = get_users();
		$assignee = $issue->get_assignee_id();
		include(BuggyPress::plugin_path('views/admin/meta-box-assignee.php'));
	}

	public function save( $post_id, $post ) {
		if ( isset($_POST[self::FIELD_ASSIGNEE]) ) {
			$issue = new BuggyPress_Issue($post_id);
			$issue->set_assignee_id((int)$_POST[self::FIELD_ASSIGNEE]);
		}
	}
}
