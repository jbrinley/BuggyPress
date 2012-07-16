<?php

class BuggyPress_Form_Element_WPEditor extends Zend_Form_Element_Textarea {
	public $helper = 'formWPEditor';
	public function render( Zend_View_Interface $view = NULL ) {
		if ( !$view ) {
			$view = $this->getView();
		}
		if ( $view && method_exists($view, 'registerHelper') ) {
			$helper = new BuggyPress_View_Helper_FormWPEditor();
			$view->registerHelper($helper, 'formWPEditor');
		}
		return parent::render($view);
	}
}

class BuggyPress_View_Helper_FormWPEditor extends Zend_View_Helper_FormTextarea {
	/**
	 * Generates a 'textarea' element using wp_editor
	 *
	 * @access public
	 *
	 * @param string|array $name If a string, the element name.  If an
	 * array, all other parameters are ignored, and the array elements
	 * are extracted in place of added parameters.
	 *
	 * @param mixed $value The element value.
	 *
	 * @param array $attribs Attributes for the element tag.
	 *
	 * @return string The element XHTML.
	 */
	public function formWPEditor($name, $value = null, $attribs = null) {
		if (empty($attribs['rows'])) {
			$attribs['textarea_rows'] = (int) $this->rows;
		} else {
			$attribs['textarea_rows'] = (int)$attribs['rows'];
		}

		ob_start();
		wp_editor($value, $name, $attribs);
		$xhtml = ob_get_clean();

		return $xhtml;
	}
}