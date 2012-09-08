<?php

class BuggyPress_ProjectDashboard {

	private $project_id = 0;

	public function register_widget_area() {
		$title = get_the_title($this->project_id);
		register_sidebar( array(
			'name' => sprintf( __( '%s Dashboard', 'buggypress'), $title ),
			'description' => sprintf( __('The dashboard on the "%s" project page', 'buggypress'), $title ),
			'id' => 'buggypress-project-'.$this->project_id,
			'before_widget' => apply_filters('buggypress_before_widget', '<aside id="%1$s" class="widget %2$s">'),
			'after_widget' => apply_filters('buggypress_after_widget', "</aside>"),
			'before_title' => apply_filters('buggypress_before_title', '<h3 class="widget-title">'),
			'after_title' => apply_filters('buggypress_after_title', '</h3>'),
		) );
	}

	protected function __construct( $project_id ) {
		$this->project_id = (int)$project_id;
		$this->add_hooks();
	}

	protected function add_hooks() {
		add_action( 'widgets_init', array( $this, 'register_widget_area' ), 10, 0 );
	}

	public static function init() {
		foreach ( self::get_project_list() as $project_id ) {
			new self($project_id);
		}
		add_action( 'widgets_init', array( __CLASS__, 'register_widgets' ), 10, 0 );
	}

	/**
	 * @static
	 * Get a list of all project IDs that will have a dashboard
	 */
	private static function get_project_list() {
		// TODO: cache this query, flush when projects saved
		$query = array(
			'post_type' => BuggyPress_Project::POST_TYPE,
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
		);
		$ids = get_posts($query);
		return $ids;
	}

	/**
	 * @static
	 * Register the widgets included with BuggyPress
	 * @todo This doesn't really belong here
	 */
	public static function register_widgets() {
		register_widget('BuggyPress_Widget_ProjectDescription');
	}
}
