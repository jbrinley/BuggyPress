<?php

abstract class BuggyPress_Form_Issue extends BuggyPress_Form {
	/** @var BuggyPress_Issue */
	protected $_issue = NULL;

	/**
	 * @param BuggyPress_Issue|array|NULL $options
	 */
	public function __construct( $options = NULL ) {
		if ( $options instanceof BuggyPress_Issue ) {
			$this->set_issue($options);
			$options = NULL;
		}
		parent::__construct($options);
	}

	/**
	 * @param BuggyPress_Issue $issue
	 */
	public function set_issue( BuggyPress_Issue $issue = NULL ) {
		$this->_issue = $issue;
	}

	/**
	 * @return BuggyPress_Issue|null
	 */
	public function get_issue() {
		return $this->_issue;
	}
}
