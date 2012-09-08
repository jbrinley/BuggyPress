<?php

/**
 * Shows the description of a project. If no project is selected, then
 * the current context is used to select the most relevant project.
 */
class BuggyPress_Widget_ProjectDescription extends WP_Widget {
	public function __construct() {
		$widget_ops = array('classname' => 'buggypress-widget-project-description', 'description' => __('The description of a project', 'buggypress'));
		$control_ops = array('width' => 400, 'height' => 350);
		parent::__construct('buggypress-widget-project-description', __('BuggyPress: Project Description', 'buggypress'), $widget_ops, $control_ops);
	}

	public function widget( $args, $instance ) {
		extract($args);
		if ( empty($project) ) {
			$project = buggypress_get_context();
		}
		if ( !$project ) {
			return;
		}
		$post = get_post($project);
		$text = apply_filters('the_content', $post->post_content);
		if ( empty($text) ) {
			return;
		}
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		echo $before_widget;
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } ?>
		<?php echo $text;
		echo $after_widget;
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['project'] = (int)$new_instance['project'];
		return $instance;
	}

	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'project' => 0 ) );
		$title = strip_tags($instance['title']);
		$project = (int)$instance['project'];
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'buggypress'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

		<p>
			<?php wp_dropdown_pages(array(
				'post_type' => BuggyPress_Project::POST_TYPE,
				'post_status' => 'publish',
				'show_option_none' => __('Current Context', 'buggypress'),
				'option_none_value' => 0,
				'selected' => $project,
				'name' => $this->get_field_name('project'),
				'id' => $this->get_field_id('project'),
			)); ?>
		</p>
		<?php
	}
}
