<?php

/**
 * A simple wrapper around Zend_Form to
 * work without having to pass views
 * around.
 */
class BuggyPress_Form extends Zend_Form {
	private $_isSubform = FALSE;

	public function is_subform( $setting = NULL ) {
		if ( $setting !== NULL ) {
			$this->_isSubform = (bool)$setting;
			$this->_isArray = $this->_isSubform;
		}
		return $this->_isSubform;
	}

	/**
	 * Wrap parent to provide a default Zend_View if none
	 * is given
	 *
	 * @param Zend_View_Interface $view
	 * @return string
	 */
	public function render( Zend_View_Interface $view = NULL ) {
		if ( NULL === $view ) {
			if ( NULL == $this->getView()) {
				$this->setView( new Zend_View() );
			}
			$view = $this->getView();
		}
		return parent::render($view);
	}

	/**
	 * Wrap parent to provide a default Zend_View to
	 * the element, if none is given
	 *
	 * @param string $elementName
	 * @return NULL|Zend_Form_Element
	 */
	public function getElement($elementName) {
		$element = parent::getElement($elementName);

		if ( !isset($element) || FALSE == $element instanceof Zend_Form_Element) {
			return NULL;
		}

		if (NULL == $element->getView() ) {
			if ( NULL == $this->getView()) {
				$this->setView( new Zend_View() );
			}
			$element->setView($this->getView());
		}

		return $element;
	}

	/**
	 * Load the default decorators
	 *
	 * @return BuggyPress_Form
	 */
	public function loadDefaultDecorators() {
		if ($this->loadDefaultDecoratorsIsDisabled()) {
			return $this;
		}

		if ( $this->is_subform() ) {
			return $this->loadSubFormDefaultDecorators();
		}

		$decorators = $this->getDecorators();
		if (empty($decorators)) {
			$this->addDecorator('FormElements')
				->addDecorator('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form'))
				->addDecorator('Form');
		}
		return $this;
	}

	private function loadSubFormDefaultDecorators() {
		if ($this->loadDefaultDecoratorsIsDisabled()) {
			return $this;
		}

		$decorators = $this->getDecorators();
		if (empty($decorators)) {
			$this->addDecorator('FormElements')
				->addDecorator('HtmlTag', array('tag' => 'dl'))
				->addDecorator('Fieldset');
		}
		return $this;
	}
}
