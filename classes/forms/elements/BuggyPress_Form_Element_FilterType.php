<?php

class BuggyPress_Form_Element_FilterType extends Zend_Form_Element_Multiselect {
	const FIELD_NAME = 'buggypress_filter_type';
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
		$this->setLabel( __('Type', 'buggypress') );

		$terms = BuggyPress_Issue::get_types();
		foreach ( $terms as $term ) {
			$this->addMultiOption( $term->term_id, $term->name);
		}

		if ( isset($this->_filter) ) {
			$this->setValue($this->_filter->get_type());
		}
	}
}
