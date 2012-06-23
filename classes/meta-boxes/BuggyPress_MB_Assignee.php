<?php
 
class BuggyPress_MB_Assignee extends Flightless_Meta_Box {
	const META_KEY_ASSIGNEE = '_buggypress_assignee';
	const FIELD_ASSIGNEE = 'buggypress_assignee';

	public function __construct( $id, $args = array() ) {
		$this->defaults['title'] = __('Assignee', 'buggypress');
		$this->defaults['contect'] = 'side';
		parent::__construct($id, $args);
		add_filter('buggpress_issue_changes', array($this, 'filter_change_list'), 11, 2);
	}

	public function render( $post ) {
		$issue = new BuggyPress_Issue($post->ID);
		$users = get_users();
		$assignee = $issue->get_assignee_id();
		include(BuggyPress::plugin_path('views'.DIRECTORY_SEPARATOR.'meta-box-assignee.php'));
	}

	public function save( $post_id, $post ) {
		if ( isset($_POST[self::FIELD_ASSIGNEE]) ) {
			$issue = new BuggyPress_Issue($post_id);
			$issue->set_assignee_id((int)$_POST[self::FIELD_ASSIGNEE]);
		}
	}

	public function filter_change_list( $changes, $post_id ) {
		if ( !isset($_POST[self::FIELD_ASSIGNEE]) ) {
			return $changes;
		}
		$issue = new BuggyPress_Issue($post_id);
		$current = $issue->get_assignee_id();
		if ( $current != $_POST[self::FIELD_ASSIGNEE] ) {
			if ( $current ) {
				$user = new WP_User($current);
			}
			$new = $_POST[self::FIELD_ASSIGNEE]?(new WP_User($_POST[self::FIELD_ASSIGNEE])):NULL;
			$changes['assignee'] = array(
				'label' => __('Assignee', 'buggypress'),
				'old' => $current?$user->display_name:__('Unassigned', 'buggypress'),
				'new' => $new?$new->display_name:__('Unassigned', 'buggypress'),
			);
		}
		return $changes;
	}
}
