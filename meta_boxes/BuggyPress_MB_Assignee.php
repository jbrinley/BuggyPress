<?php
 
class BuggyPress_MB_Assignee extends BuggyPress_Meta_Box {
	const META_KEY_ASSIGNEE = '_buggypress_assignee';
	const FIELD_ASSIGNEE = 'buggypress_assignee';

	protected $defaults = array(
		'title' => 'Assignee',
		'context' => 'side',
		'priority' => 'default',
		'callback_args' => NULL,
	);

	public function render( $post ) {
		$users = get_users();
		$assignee = $this->get_assignee($post->ID);
		include(self::plugin_path('views'.DIRECTORY_SEPARATOR.'meta-box-assignee.php'));
	}

	public function save( $post_id, $post ) {
		if ( isset($_POST[self::FIELD_ASSIGNEE]) ) {
			$this->set_assignee($post_id, (int)$_POST[self::FIELD_ASSIGNEE]);
		}
	}

	public function get_assignee( $post_id ) {
		return (int)get_post_meta($post_id, self::META_KEY_ASSIGNEE, TRUE);
	}

	public function set_assignee( $post_id, $user_id ) {
		update_post_meta($post_id, self::META_KEY_ASSIGNEE, (int)$user_id);
	}
}
