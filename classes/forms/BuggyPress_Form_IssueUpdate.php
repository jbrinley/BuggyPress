<?php

class BuggyPress_Form_IssueUpdate extends BuggyPress_Form_Issue {
	public function init() {
		$this->is_subform(TRUE);

		$this->addElement( new BuggyPress_Form_Element_IssueProject($this->_issue) );
		$this->addElement( new BuggyPress_Form_Element_IssueTitle($this->_issue) );
		$this->addElement( new BuggyPress_Form_Element_IssueAssignee($this->_issue) );
		$this->addElement( new BuggyPress_Form_Element_IssueType($this->_issue) );
		$this->addElement( new BuggyPress_Form_Element_IssueStatus($this->_issue) );
		$this->addElement( new BuggyPress_Form_Element_IssuePriority($this->_issue) );
		$this->addElement( new BuggyPress_Form_Element_IssueResolution($this->_issue) );
		$this->addElement( new BuggyPress_Form_Element_IssueDescription($this->_issue) );
	}
}
