<?php

class BuggyPress_CommentForms {
	/** @var BuggyPress_CommentForms */
	private static $instance;

	/** @var BuggyPress_Issue_Changelist */
	private $changelist = NULL;

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
			$form = $this->get_form();

			$defaults['title_reply'] = __('Update Issue', 'buggypress');
			$defaults['label_submit'] = __('Update', 'buggypress');
			$defaults['comment_field'] = $form.$defaults['comment_field'];
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
		if ( get_post_type($post_id) != BuggyPress_Issue::POST_TYPE ) {
			return;
		}
		$changelist = $this->build_change_list($post_id, $_POST);
		if ( $changelist === NULL ) {
			return; // validation error
			// TODO: handle errors
		}
		$this->set_change_list($changelist);
		$changelist->before_list = '<ul class="buggy issue-updates" id="issue-update-'.time().'">';
		$_POST['comment'] .= $changelist;
	}

	private function set_change_list( BuggyPress_Issue_Changelist $changelist = NULL ) {
		$this->changelist = $changelist;
	}

	/**
	 * @return BuggyPress_Issue_Changelist|null
	 */
	private function get_change_list() {
		return $this->changelist;
	}

	/**
	 * @param $post_id
	 * @param $submitted
	 *
	 * @return BuggyPress_Issue_Changelist|NULL
	 */
	private function build_change_list( $post_id, $submitted ) {
		$form = $this->get_form(array('post_id' => $post_id));
		if ( !$form->isValid($submitted) ) {
			return NULL;
		}
		$data = $form->getValues(TRUE);
		// TODO: handle each field
		$changelist = new BuggyPress_Issue_Changelist($form->get_issue());
		$changelist->add_change('title', $data[BuggyPress_Form_Element_IssueTitle::FIELD_NAME]);
		$changelist->add_change('description', $data[BuggyPress_Form_Element_IssueDescription::FIELD_NAME]);
		$changelist->add_change('project', (int)$data[BuggyPress_Form_Element_IssueProject::FIELD_NAME]);
		$changelist->add_change('assignee', (int)$data[BuggyPress_Form_Element_IssueAssignee::FIELD_NAME]);
		$changelist->add_change('type', (int)$data[BuggyPress_Form_Element_IssueType::FIELD_NAME]);
		$changelist->add_change('status', (int)$data[BuggyPress_Form_Element_IssueStatus::FIELD_NAME]);
		$changelist->add_change('priority', (int)$data[BuggyPress_Form_Element_IssuePriority::FIELD_NAME]);
		$changelist->add_change('resolution', (int)$data[BuggyPress_Form_Element_IssueResolution::FIELD_NAME]);

		$changelist = apply_filters( 'buggypress_issue_changelist', $changelist, $submitted );
		return $changelist;
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
		if ( get_post_type($comment->comment_post_ID) != BuggyPress_Issue::POST_TYPE ) {
			return;
		}

		$changelist = $this->get_change_list();
		if ( $changelist ) {
			$changelist->commit_changes();
		}
	}

	/**
	 * @param array $args
	 * @return BuggyPress_Form_IssueNew
	 */
	private function get_form( $args = array() ) {
		$defaults = array(
			'post_id' => get_the_ID(),
		);
		$args = wp_parse_args($args, $defaults);
		$issue = new BuggyPress_Issue($args['post_id']);

		$form = new BuggyPress_Form_IssueUpdate($issue);
		$form = apply_filters('buggypress_update_issue_form', $form, $args);
		return $form;
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
