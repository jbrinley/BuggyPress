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
		$users = get_users();
		$assignee = $this->get_assignee($post->ID);
		include(BuggyPress::plugin_path('views'.DIRECTORY_SEPARATOR.'meta-box-assignee.php'));
	}

	public function save( $post_id, $post ) {
		if ( isset($_POST[self::FIELD_ASSIGNEE]) ) {
			$this->set_assignee($post_id, (int)$_POST[self::FIELD_ASSIGNEE]);
		}
	}

	/**
	 * Get the current assignee for the issue
	 *
	 * @param int $post_id
	 * @param string $format
	 * @return int|WP_User
	 */
	public function get_assignee( $post_id, $format = 'id' ) {
		$assignee_id = (int)get_post_meta($post_id, self::META_KEY_ASSIGNEE, TRUE);
		if ( !$assignee_id ) {
			return 0;
		}
		switch ( $format ) {
			case 'object':
				$assignee = new WP_User($assignee_id);
				return $assignee;
			case 'id':
			default:
				return $assignee_id;
		}
	}

	public function set_assignee( $post_id, $user_id ) {
		update_post_meta($post_id, self::META_KEY_ASSIGNEE, (int)$user_id);
	}

	public function filter_change_list( $changes, $post_id ) {
		if ( !isset($_POST[self::FIELD_ASSIGNEE]) ) {
			return $changes;
		}
		$current = $this->get_assignee($post_id);
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
