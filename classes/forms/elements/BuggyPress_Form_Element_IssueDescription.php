<?php

class BuggyPress_Form_Element_IssueDescription extends BuggyPress_Form_Element_WPEditor {
	const FIELD_NAME = 'buggypress_issue_description';
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
		$this->setLabel( __('Description', 'buggypress') );
		$description = 0;
		if ( isset($this->_issue) ) {
			$description = $this->_issue->get_description();
		}
		$this->setValue($description);
	}
}
