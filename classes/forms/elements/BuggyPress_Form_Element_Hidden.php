<?php

class BuggyPress_Form_Element_Hidden extends Zend_Form_Element_Hidden {
	public function init() {
		$this->setDisableLoadDefaultDecorators(true);
		$this->addDecorator('ViewHelper');
		$this->removeDecorator('DtDdWrapper');
		$this->removeDecorator('HtmlTag');
		$this->removeDecorator('Label');
		parent::init();
	}
}
