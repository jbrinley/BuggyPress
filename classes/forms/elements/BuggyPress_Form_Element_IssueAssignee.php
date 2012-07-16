<?php

class BuggyPress_Form_Element_IssueAssignee extends Zend_Form_Element_Select {
	const FIELD_NAME = 'buggypress_issue_assignee';
	/** @var BuggyPress_Issue */
	protected $_issue = NULL;

	public function __construct( $spec, $options = NULL ) {
		if ( $spec instanceof BuggyPress_Issue ) {
			$this->_issue = $spec;
			$spec = self::FIELD_NAME;
		}
		parent::__construct($spec, $options);
	}

	public function init() {
		$this->setLabel( __('Assigned To', 'buggypress') );

		$users = get_users( array(
			// TODO: include => $users_on_the_project
			'orderby' => 'display_name',
			'order' => 'ASC',
			'fields' => array('ID', 'display_name', 'user_login'),
		) );
		$assignee = 0;
		if ( isset($this->_issue) ) {
			$assignee = $this->_issue->get_assignee_id();
		}
		if ( $assignee ) {
			$assigned_user = new WP_User($assignee);
			if ( $assigned_user->ID ) {
				$this->addMultiOption($assigned_user->ID, $assigned_user->display_name);
			}
		}
		foreach ( $users as $user ) {
			if ( $user->ID != $assignee ) {
				$label = $user->display_name;
				if ( $user->display_name != $user->user_login ) {
					$label .= "({$user->user_login})";
				}
				$this->addMultiOption( $user->ID, $label);
			}
		}
		$this->addMultiOption(0, __( 'Unassigned', 'buggypress' ));

		$this->setValue($assignee);
	}
}
