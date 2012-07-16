<?php
/**
 * Consolidate a set of changes to an issue,
 * committing all of them at once.
 */
class BuggyPress_Issue_Changelist {
	public $before_list = '<ul>';
	public $after_list = '</ul>';
	public $before_item = '<li>';
	public $after_item = '</li>';

	/** @var BuggyPress_Issue */
	private $issue = NULL;
	private $changes = array();
	public function __construct( BuggyPress_Issue $issue ) {
		$this->issue = $issue;
	}

	public function add_change( $field, $new_value ) {
		$old_value = $this->get_old_value($field);
		$this->remove_change($field);
		if ( $old_value != $new_value ) {
			$this->changes[$field] = array(
				'old_value' => $old_value,
				'new_value' => $new_value,
			);
		}
	}

	public function remove_change( $field ) {
		if ( isset($this->changes[$field]) ) {
			unset($this->changes[$field]);
		}
	}

	public function __toString() {
		if ( empty($this->changes) ) {
			return '';
		}
		$list = $this->before_list;
		foreach ( $this->changes as $field => $values ) {
			$list .= $this->before_item;
			$old = $this->get_value_string($values['old_value'], $field);
			$new = $this->get_value_string($values['new_value'], $field);
			$list .= $this->get_change_comment($this->get_field_label($field), $old, $new);
			$list .= $this->after_item;
		}
		$list .= $this->after_list;
		return $list;
	}

	/**
	 * Commit all of the listed changes to the issue
	 */
	public function commit_changes() {
		foreach ( $this->changes as $field => $value ) {
			$this->commit_value( $field, $value['new_value'] );
		}
		$post_fields = array();
		if ( isset($this->changes['title']) ) {
			$post_fields['post_title'] = $this->changes['title']['new_value'];
		}
		if ( isset($this->changes['description']) ) {
			$post_fields['post_content'] = $this->changes['title']['new_value'];
		}
		if ( !empty($post_fields) ) {
			$this->issue->save_post($post_fields);
		}
	}

	private function commit_value( $field, $value ) {
		switch ( $field ) {
			case 'title':
			case 'description':
				break; // these pass through for special handling later
			case 'project':
				$this->issue->set_project_id($value);
				break;
			case 'assignee':
				$this->issue->set_assignee_id($value);
				break;
			case 'type':
				$this->issue->set_type($value);
				break;
			case 'status':
				$this->issue->set_status($value);
				break;
			case 'resolution':
				$this->issue->set_resolution($value);
				break;
			case 'priority':
				$this->issue->set_priority($value);
				break;
			default:
				do_action( 'buggypress_issue_commit_value', $value, $field );
				break;
		}
	}

	/**
	 * @param string $field
	 * @return mixed
	 */
	private function get_old_value( $field ) {
		switch ( $field ) {
			case 'title':
				return $this->issue->get_title();
			case 'description':
				return $this->issue->get_description();
			case 'project':
				return $this->issue->get_project_id();
			case 'assignee':
				return $this->issue->get_assignee_id();
			case 'type':
				return $this->issue->get_type();
			case 'status':
				return $this->issue->get_status();
			case 'resolution':
				return $this->issue->get_resolution();
			case 'priority':
				return $this->issue->get_priority();
			default:
				return apply_filters( 'buggypress_issue_old_value', '', $field );
		}
	}

	private function get_field_label( $field ) {
		switch ( $field ) {
			case 'title':
				return __('Title', 'buggypress');
			case 'description':
				return __('Description', 'buggypress');
			case 'project':
				return __('Project', 'buggypress');
			case 'assignee':
				return __('Assignee', 'buggypress');
			case 'type':
				return __('Type', 'buggypress');
			case 'status':
				return __('Status', 'buggypress');
			case 'resolution':
				return __('Resolution', 'buggypress');
			case 'priority':
				return __('Priority', 'buggypress');
			default:
				return apply_filters( 'buggypress_issue_field_label', $field );
		}
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
		$message = sprintf(__('%1$s changed from <em>%2$s</em> to <em>%3$s</em>', 'buggypress'), $label, $old_value, $new_value);
		return apply_filters('buggypress_issue_change_comment', $message, $label, $old_value, $new_value);
	}

	private function get_value_string( $value, $field ) {
		switch ( $field ) {
			case 'project':
				if ( !$value ) {
					return __('None', 'buggypress');
				}
				return get_the_title($value);
			case 'assignee':
				if ( !$value ) {
					return __('None', 'buggypress');
				}
				$user = new WP_User($value);
				return $user->user_login;
			case 'type':
			case 'status':
			case 'resolution':
			case 'priority':
				if ( !$value ) {
					return __('None', 'buggypress');
				}
				$taxonomy = 'issue_'.$field;
				$term = get_term($value, $taxonomy);
				if ( !$term ) {
					return __('None', 'buggypress');
				}
				return $term->name;
			default:
				return apply_filters( 'buggypress_issue_field_value_string', $value, $field );
		}
	}
}
