<?php

class BuggyPress_Form_Element_IssueTitle extends Zend_Form_Element_Text {
	const FIELD_NAME = 'buggypress_issue_title';
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
		$this->setLabel( __('Title', 'buggypress') );
		$title = 0;
		if ( isset($this->_issue) ) {
			$title = $this->_issue->get_title();
		}
		$this->setValue($title);
		$this->setRequired(TRUE);
	}
}
