<?php

class BuggyPress_CommentForms {
	/** @var BuggyPress_CommentForms */
	private static $instance;

	/**
	 * Add the issue details to the comment form
	 *
	 * @param array $defaults
	 * @return array
	 */
	public function comment_form_defaults( $defaults = array() ) {
		global $post;
		if ( $post->post_type == BuggyPress_Issue::POST_TYPE ) {
			// the meta boxes are already rendering the exact same thing in the admin
			// if need be in the future, the meta boxes can distinguish based on is_admin()
			// TODO: restrict this to authorized users
			$taxonomies = Flightless_Meta_Box::get_meta_box(BuggyPress_Issue::POST_TYPE, 'BuggyPress_MB_Taxonomies');
			$assignee = Flightless_Meta_Box::get_meta_box(BuggyPress_Issue::POST_TYPE, 'BuggyPress_MB_Assignee');
			ob_start();
			$taxonomies->render($post);
			$assignee->render($post);
			$update_fields = ob_get_clean();

			$defaults['title_reply'] = __('Update Issue', 'buggypress');
			$defaults['label_submit'] = __('Update', 'buggypress');
			$defaults['comment_field'] = $update_fields.$defaults['comment_field'];
		}
		return $defaults;
	}

	/**
	 * If an issue is updated, note any updates in the comment
	 *
	 * @param int $post_id
	 * @return void
	 */
	public function add_changes_to_comment( $post_id ) {
		$post = get_post($post_id);
		if ( !is_object($post) || $post->post_type != BuggyPress_Issue::POST_TYPE ) {
			return;
		}
		/**
		 * Changes should be an array of arrays, each item having three keys:
		 *  - label - The label to use for this change
		 *  - old - The old value
		 *  - new - The new value
		 */
		$changes = apply_filters('buggpress_issue_changes', array(), $post_id);
		if ( !$changes ) {
			return; // nothing to do
		}
		$extra = '<ul class="buggy issue-updates" id="issue-update-'.time().'">'; // add timestamp to bypass WP's duplicate check
		foreach ( $changes as $change ) {
			$extra .= '<li>'.$this->get_change_comment($change['label'], $change['old'], $change['new']).'</li>';
		}
		$extra .= '</ul>';
		$_POST['comment'] .= $extra;
	}

	/**
	 * Utility function for printing change messages
	 *
	 * @param string $label
	 * @param string $old_value
	 * @param string $new_value
	 * @return string
	 */
	private function get_change_comment( $label, $old_value, $new_value ) {
		$message = sprintf(__('%s changed from <em>%s</em> to <em>%s</em>', 'buggypress'), $label, $old_value, $new_value);
		return apply_filters('buggypress_issue_change_comment', $message, $label, $old_value, $new_value);
	}

	/**
	 * Save updates that came in via the comment form
	 *
	 * @param int $comment_ID
	 * @param mixed (int|string) $approved 1 for approved, 0 for not approved, 'spam' for spam
	 * @return void
	 */
	public function save_comment_form_updates( $comment_ID, $approved = 1 ) {
		// TODO: security check: is the user allowed to update the issue via the comment form
		if ( $approved != 1 ) {
			return;
		}
		$comment = get_comment($comment_ID);
		if ( !is_object($comment) ) {
			return;
		}
		$post = get_post($comment->comment_post_ID);
		if ( !is_object($post) || $post->post_type != BuggyPress_Issue::POST_TYPE ) {
			return;
		}
		/** @var $taxonomies BuggyPress_MB_Taxonomies */
		$taxonomies = Flightless_Meta_Box::get_meta_box(BuggyPress_Issue::POST_TYPE, 'BuggyPress_MB_Taxonomies');
		/** @var $assignee BuggyPress_MB_Assignee */
		$assignee = Flightless_Meta_Box::get_meta_box(BuggyPress_Issue::POST_TYPE, 'BuggyPress_MB_Assignee');
		$taxonomies->save($post->ID, $post);
		$assignee->save($post->ID, $post);
	}

	private function add_hooks() {
		add_filter('comment_form_defaults', array($this, 'comment_form_defaults'), 1);
		add_action('pre_comment_on_post', array($this, 'add_changes_to_comment'), 1);
		add_action('comment_post', array($this, 'save_comment_form_updates'), 1);
	}

	/********** Singleton *************/

	/**
	 * Create the instance of the class
	 *
	 * @static
	 * @return void
	 */
	public static function init() {
		self::$instance = self::get_instance();
	}

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 * @static
	 * @return BuggyPress_CommentForms
	 */
	public static function get_instance() {
		if ( !is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	final public function __clone() {
		trigger_error( "Singleton. No cloning allowed!", E_USER_ERROR );
	}

	final public function __wakeup() {
		trigger_error( "Singleton. No serialization allowed!", E_USER_ERROR );
	}

	protected function __construct() {
		$this->add_hooks();
	}
}
