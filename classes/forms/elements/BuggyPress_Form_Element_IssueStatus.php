<?php

class BuggyPress_Form_Element_IssueStatus extends Zend_Form_Element_Select {
	const FIELD_NAME = 'buggypress_issue_status';
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
		$this->setLabel( __('Status', 'buggypress') );

		$terms = BuggyPress_Issue::get_statuses();
		foreach ( $terms as $term ) {
			$this->addMultiOption( $term->term_id, $term->name);
		}

		if ( isset($this->_issue) ) {
			$this->setValue($this->_issue->get_status());
		}
	}
}
