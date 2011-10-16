<?php
 
class BuggyPress_Project extends BuggyPress_Post_Type {
	const POST_TYPE = 'project';
	protected $post_type_label_singular = 'Project';
	protected $post_type_label_plural = 'Projects';
	protected $slug = 'projects';
	protected $post_type = self::POST_TYPE;

	private static $instance;
	/**
	 * Create the instance of the class
	 *
	 * @static
	 * @return void
	 */
	public static function init() {
		self::$instance = self::get_instance();
	}

	/** Singleton */

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 * @static
	 * @return BuggyPress_Project
	 */
	public static function get_instance() {
		if ( !is_a(self::$instance, __CLASS__) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function add_hooks() {
		parent::add_hooks();
		add_action('init', array($this, 'register_rewrite_tag'), 0, 0);
		add_filter('post_type_link', array($this, 'add_parent_project_to_link'), 1, 3);
	}

	protected function post_type_args() {
		$args = parent::post_type_args();
		$args['supports'] = array('title', 'editor', 'thumbnail', 'revisions');
		return $args;
	}

	public function register_rewrite_tag() {
		global $wp_rewrite;
		$wp_rewrite->add_rewrite_tag('%parent_project%', $this->slug.'/([^/]+)/', 'parent_project=');
	}

	public function add_parent_project_to_link( $post_link, $post = 0, $leavename = FALSE ) {
		if ( strpos('%parent_project%', $post_link) !== FALSE ) {
			return $post_link;
		}
		if ( is_object($post) ) {
			$post_id = $post->ID;
		} else {
			$post_id = $post;
			$post = get_post($post_id);
		}
		$project_slug = self::__('none');
		$project_id = bp_get_the_project( $post_id );
		if ( $project_id ) {
			$project_slug = get_page_uri($project_id);
		}

		// put the project slug in place of %parent_project%
		return str_replace('%parent_project%', $this->slug.'/'.$project_slug.'/', $post_link);
	}
}
