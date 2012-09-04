<?php

class BuggyPress_Form_Element_FilterStatus extends Zend_Form_Element_Multiselect {
	const FIELD_NAME = 'buggypress_filter_status';
	/** @var BuggyPress_Filter */
	protected $_filter = NULL;

	public function __construct( $spec, $options = NULL ) {
		if ( $spec instanceof BuggyPress_Filter ) {
			$this->_filter = $spec;
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

		if ( isset($this->_filter) ) {
			$this->setValue($this->_filter->get_status());
		}
	}
}
