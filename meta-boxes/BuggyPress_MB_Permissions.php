<?php
 
class BuggyPress_MB_Permissions extends BuggyPress_Meta_Box {
	const META_KEY_VISIBILITY = '_buggypress_project_visibility';
	const FIELD_VISIBILITY = 'buggypress_project_visibility';

	const META_KEY_COMMENTING = '_buggypress_project_commenting';
	const FIELD_COMMENTING = 'buggypress_project_commenting';

	const MEMBERS = 'members';
	const USERS = 'users';
	const ALL = 'public';

	protected $defaults = array(
		'title' => 'Permissions',
		'context' => 'side',
		'priority' => 'default',
		'callback_args' => NULL,
	);

	public function __construct( $id, $args = array() ) {
		parent::__construct($id, $args);
	}

	public function render( $post ) {
		$visibility = $this->get_visibility($post->ID);
		$commenting = $this->get_commenting($post->ID);
		include(self::plugin_path('views'.DIRECTORY_SEPARATOR.'meta-box-permissions.php'));
	}

	public function save( $post_id, $post ) {
		$this->set_visibility($post_id, isset($_POST[self::FIELD_VISIBILITY])?$_POST[self::FIELD_VISIBILITY]:self::MEMBERS);
		$this->set_commenting($post_id, isset($_POST[self::FIELD_COMMENTING])?$_POST[self::FIELD_COMMENTING]:self::MEMBERS);
	}

	/**
	 * Get the current visibility setting for the project and its issues
	 *
	 * @param int $post_id
	 * @return string
	 */
	public function get_visibility( $post_id ) {
		$status = get_post_meta($post_id, self::META_KEY_VISIBILITY, TRUE);
		if ( !$status || !in_array($status, array(self::MEMBERS, self::USERS, self::ALL)) ) {
			return self::MEMBERS;
		}
		return $status;
	}

	/**
	 * Get the current commenting setting for the project and its issues
	 *
	 * @param int $post_id
	 * @return string
	 */
	public function get_commenting( $post_id ) {
		$status = get_post_meta($post_id, self::META_KEY_COMMENTING, TRUE);
		if ( !$status || !in_array($status, array(self::MEMBERS, self::USERS, self::ALL)) ) {
			return self::MEMBERS;
		}
		return $status;
	}

	/**
	 * Set the visibility setting for the project
	 *
	 * @param int $post_id
	 * @param string $status
	 * @return void
	 */
	public function set_visibility( $post_id, $status ) {
		if ( !in_array($status, array(self::MEMBERS, self::USERS, self::ALL)) ) {
			$status = self::MEMBERS;
		}
		update_post_meta($post_id, self::META_KEY_VISIBILITY, $status);
	}

	/**
	 * Set the commenting setting for the project
	 *
	 * @param int $post_id
	 * @param string $status
	 * @return void
	 */
	public function set_commenting( $post_id, $status ) {
		if ( !in_array($status, array(self::MEMBERS, self::USERS, self::ALL)) ) {
			$status = self::MEMBERS;
		}
		update_post_meta($post_id, self::META_KEY_COMMENTING, $status);
	}
}
