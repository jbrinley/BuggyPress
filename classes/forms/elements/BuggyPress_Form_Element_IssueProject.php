<?php

class BuggyPress_Form_Element_IssueProject extends Zend_Form_Element_Select {
	const FIELD_NAME = 'buggypress_issue_project';
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
		$this->setLabel( __('Project', 'buggypress') );

		$this->addMultiOption('', __( ' -- Select Project -- ', 'buggypress' ));

		$projects = get_posts(array(
			'post_type' => BuggyPress_Project::POST_TYPE,
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
			'suppress_filters' => FALSE,
		));
		foreach ( $projects as $project ) {
			$this->addMultiOption( $project->ID, get_the_title($project));
		}

		if ( isset($this->_issue) ) {
			$this->setValue($this->_issue->get_project_id());
		}
	}
}
