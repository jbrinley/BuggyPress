<?php

class BuggyPress_Form_Element_IssueType extends Zend_Form_Element_Select {
	const FIELD_NAME = 'buggypress_issue_type';
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
		$this->setLabel( __('Type', 'buggypress') );

		$terms = BuggyPress_Issue::get_types();
		foreach ( $terms as $term ) {
			$this->addMultiOption( $term->term_id, $term->name);
		}

		if ( isset($this->_issue) ) {
			$this->setValue($this->_issue->get_type());
		}
	}
}
