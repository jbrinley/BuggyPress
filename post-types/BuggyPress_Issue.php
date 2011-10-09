<?php
 
class BuggyPress_Issue extends BuggyPress_Post_Type {
	protected $post_type_label_singular = 'Issue';
	protected $post_type_label_plural = 'Issues';
	protected $slug = 'issues';
	protected $post_type = 'issue';
	protected $taxonomies = array();

	private static $instance;
	/**
	 * Create the instance of the class
	 *
	 * @static
	 * @return void
	 */
	public static function init() {
		self::$instance = self::get_instance();
	}

	/** Singleton */

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 * @static
	 * @return BuggyPress_Issue
	 */
	public static function get_instance() {
		if ( !is_a(self::$instance, __CLASS__) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function __construct() {
		parent::__construct();
		$type = $this->add_taxonomy('BuggyPress_Type');
		$priority = $this->add_taxonomy('BuggyPress_Priority');
		$status = $this->add_taxonomy('BuggyPress_Status');
		//$resolution = $this->add_taxonomy('BuggyPress_Resolution');

		$this->taxonomies = array(
			$type->get_id() => array(
				'label' => self::__('Type'),
			),
			$priority->get_id() => array(
				'label' => self::__('Priority'),
			),
			$status->get_id() => array(
				'label' => self::__('Status'),
			),
		);

		$this->add_meta_box('BuggyPress_MB_Taxonomies', array('taxonomies' => $this->taxonomies));
		$this->add_meta_box('BuggyPress_MB_Issue_Project');
		$this->add_meta_box('BuggyPress_MB_Assignee');
	}

	public function add_hooks() {
		parent::add_hooks();
		add_filter('comment_form_defaults', array($this, 'comment_form_defaults'), 1);
    add_action('pre_comment_on_post', array($this, 'add_changes_to_comment'), 1);
    add_action('comment_post', array($this, 'save_comment_form_updates'), 1);
	}

	public function post_type_args() {
		$args = parent::post_type_args();
		$args['supports'] = array('title', 'editor', 'thumbnail', 'author', 'excerpt', 'comments', 'revisions');
		return $args;
	}

	/**
	 * @param array $defaults
	 * @return array
	 */
	public function comment_form_defaults( $defaults = array() ) {
		global $post;
		if ( $post->post_type == $this->post_type ) {
			// the meta boxes are already rendering the exact same thing in the admin
			// if need be in the future, the meta boxes can distinguish based on is_admin()
			ob_start();
			$this->meta_boxes['BuggyPress_MB_Taxonomies']->render($post);
			$this->meta_boxes['BuggyPress_MB_Assignee']->render($post);
			$update_fields = ob_get_clean();
			
			$defaults['title_reply'] = self::__('Update Issue');
			$defaults['label_submit'] = self::__('Update');
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
		if ( !is_object($post) || $post->post_type != $this->post_type ) {
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

	private function get_change_comment( $label, $old_value, $new_value ) {
		$message = sprintf(self::__('%s changed from <em>%s</em> to <em>%s</em>'), $label, $old_value, $new_value);
		return apply_filters('buggypress_issue_change_comment', $message, $label, $old_value, $new_value);
	}

  /**
   * @param int $comment_ID
   * @param mixed (int|string) $approved 1 for approved, 0 for not approved, 'spam' for spam
   * @return void
   */
	public function save_comment_form_updates( $comment_ID, $approved = 1 ) {
		if ( $approved != 1 ) {
			return;
		}
		$comment = get_comment($comment_ID);
		if ( !is_object($comment) ) {
			return;
		}
		$post = get_post($comment->comment_post_ID);
		if ( !is_object($post) || $post->post_type != $this->post_type ) {
			return;
		}
		$this->meta_boxes['BuggyPress_MB_Taxonomies']->save($post->ID, $post);
		$this->meta_boxes['BuggyPress_MB_Assignee']->save($post->ID, $post);
	}
}
