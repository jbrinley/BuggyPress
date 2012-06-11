<?php
 
class BuggyPress_MB_Members extends Flightless_Meta_Box {
	const META_KEY_MEMBERS = '_buggypress_project_member';
	const FIELD_MEMBERS = 'buggypress_project_member';
	const META_KEY_ADMINS = '_buggypress_project_admin';
	const FIELD_ADMINS = 'buggypress_project_admin';

	public function __construct( $id, $args = array() ) {
		$this->defaults['title'] = __('Users', 'buggypress');
		$this->defaults['contect'] = 'side';
		parent::__construct($id, $args);
	}

	public function render( $post ) {
		$users = get_users();
		$members = $this->get_members($post->ID);
		$admins = $this->get_administrators($post->ID);
		include(BuggyPress::plugin_path('views'.DIRECTORY_SEPARATOR.'meta-box-members.php'));
	}

	public function save( $post_id, $post ) {
		$members = isset($_POST[self::FIELD_MEMBERS])?$_POST[self::FIELD_MEMBERS]:array();
		$admins = isset($_POST[self::FIELD_ADMINS])?$_POST[self::FIELD_ADMINS]:array();

		// all admins should also be members
		$members = array_unique(array_merge($members, $admins));

		$this->set_members($post_id, $members);
		$this->set_administrators($post_id, $admins);
	}

	/**
	 * Get the current members assigned to the project
	 *
	 * @param int $post_id
	 * @return array An array of user IDs for project members
	 */
	public function get_members( $post_id ) {
		$user_ids = get_post_meta($post_id, self::META_KEY_MEMBERS, FALSE);
		if ( !$user_ids ) {
			return array();
		}
		return array_map('intval', $user_ids);
	}

	/**
	 * Get the current administrators assigned to the project
	 *
	 * @param int $post_id
	 * @return array An array of user IDs for project administrators
	 */
	public function get_administrators( $post_id ) {
		$user_ids = get_post_meta($post_id, self::META_KEY_ADMINS, FALSE);
		if ( !$user_ids ) {
			return array();
		}
		return array_map('intval', $user_ids);
	}

	/**
	 * Set the members of the project to the user IDs specified in $user_ids
	 *
	 * @param int $post_id
	 * @param array $user_ids
	 * @return void
	 */
	public function set_members( $post_id, $user_ids = array() ) {
		delete_post_meta($post_id, self::META_KEY_MEMBERS);
		foreach ( $user_ids as $id ) {
			add_post_meta($post_id, self::META_KEY_MEMBERS, (int)$id);
		}
	}


	/**
	 * Set the administrators of the project to the user IDs specified in $user_ids
	 *
	 * @param int $post_id
	 * @param array $user_ids
	 * @return void
	 */
	public function set_administrators( $post_id, $user_ids = array() ) {
		delete_post_meta($post_id, self::META_KEY_ADMINS);
		foreach ( $user_ids as $id ) {
			add_post_meta($post_id, self::META_KEY_ADMINS, (int)$id);
		}
	}
}
