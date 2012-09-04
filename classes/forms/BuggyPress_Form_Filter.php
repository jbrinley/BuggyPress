<?php

class BuggyPress_Form_Filter extends BuggyPress_Form {
	/** @var BuggyPress_Filter */
	protected $_filter = NULL;

	/**
	 * @param BuggyPress_Filter|array|NULL $options
	 */
	public function __construct( $options = NULL ) {
		if ( $options instanceof BuggyPress_Filter ) {
			$this->set_filter($options);
			$options = NULL;
		}
		parent::__construct($options);
	}

	/**
	 * @param BuggyPress_Filter $filter
	 */
	public function set_filter( BuggyPress_Filter $filter = NULL ) {
		$this->_filter = $filter;
	}

	/**
	 * @return BuggyPress_Filter|null
	 */
	public function get_filter() {
		return $this->_filter;
	}

	public function init() {
		//$this->addElement( new BuggyPress_Form_Element_FilterProject($this->_filter) );
		//$this->addElement( new BuggyPress_Form_Element_FilterTitle($this->_filter) );
		//$this->addElement( new BuggyPress_Form_Element_FilterAssignee($this->_filter) );
		$this->addElement( new BuggyPress_Form_Element_FilterType($this->_filter) );
		$this->addElement( new BuggyPress_Form_Element_FilterStatus($this->_filter) );
		$this->addElement( new BuggyPress_Form_Element_FilterPriority($this->_filter) );
		$this->addElement( new BuggyPress_Form_Element_FilterResolution($this->_filter) );
		//$this->addElement( new BuggyPress_Form_Element_FilterDescription($this->_filter) );
		$this->addElement( new Zend_Form_Element_Submit( 'submit', __( 'Update Filter', 'buggypress' ) ) );

		$flag = new Zend_Form_Element_Hidden( 'update_filter' );
		$flag->setValue(1);
		$this->addElement( $flag );
	}

	public static function submitted() {
		if ( isset($_REQUEST['update_filter']) && $_REQUEST['update_filter'] == 1 ) {
			return TRUE;
		}
		return FALSE;
	}
}
