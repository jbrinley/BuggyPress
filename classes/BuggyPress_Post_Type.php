<?php
 
class BuggyPress_Post_Type extends Flightless_Post_Type {

	protected function add_hooks() {
		parent::add_hooks();
		add_filter('template_include', array( $this, 'select_post_template' ), 10, 1 );
	}

	public function select_post_template( $template ) {
		if ( is_single() && get_query_var('post_type') == $this->post_type ) {
			// check in the theme's loyalty directory
			if ( $found = locate_template(array('buggypress/'.$this->post_type.'.php'), FALSE) ) {
				return $found;
			}
			if ( file_exists(BuggyPress::plugin_path('post-templates/'.$this->post_type.'.php')) ) {
				return BuggyPress::plugin_path('post-templates/'.$this->post_type.'.php');
			}
		}
		return $template;
	}
}
