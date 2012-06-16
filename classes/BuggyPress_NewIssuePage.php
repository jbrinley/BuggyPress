<?php

class BuggyPress_NewIssuePage {
	/** @var BuggyPress_NewIssuePage */
	private static $instance;

	/**
	 * @param WP_Router $router
	 */
	public function register_page( $router ) {
		global $wp_rewrite;
		$issue_post_type = get_post_type_object( 'issue' );

		if ( get_option( 'permalink_structure' ) && is_array( $issue_post_type->rewrite ) ) {
			$path = ( true === $issue_post_type->has_archive ) ? $issue_post_type->rewrite['slug'] : $issue_post_type->has_archive;
			if ( $issue_post_type->rewrite['with_front'] ) {
				$path = $wp_rewrite->front . $path;
			} else {
				$path = $wp_rewrite->root . $path;
			}
		} else {
			$path = 'issues';
		}
		$path = trailingslashit($path)._x('new', 'new post path', 'buggypress');
		$router->add_route('buggypress_new_issue', array(
			'path' => $path.'(/(.+?))?/?$',
			'query_vars' => array(
				'project_slug' => 2,
			),
			'title' => $issue_post_type->labels->add_new_item,
			'title_callback' => NULL,
			'page_callback' => array(
				'default' => array( $this, 'render_page' ),
				'POST' => array( $this, 'process_form' ),
			),
			'page_arguments' => array('project_slug'),
			// TODO - access control
		));
	}

	public function render_page( $project_slug = '' ) {
		// TODO - set default for project based on $project_slug
		remove_filter('the_content', 'wpautop');
		$issue = new BuggyPress_Issue(0);
		$form = new BuggyPress_UpdateIssueForm($issue);
		$form->render();
	}

	public function process_form() {
		$issue = new BuggyPress_Issue(0);
		$form = new BuggyPress_UpdateIssueForm($issue);
		$form->save($_POST);
		wp_redirect($issue->get_permalink(), 303);
		exit();
	}


	private function add_hooks() {
		add_action( 'wp_router_generate_routes', array( $this, 'register_page' ), 10, 1 );
	}

	/********** Singleton *************/

	/**
	 * Create the instance of the class
	 *
	 * @static
	 * @return void
	 */
	public static function init() {
		self::$instance = self::get_instance();
	}

	/**
	 * Get (and instantiate, if necessary) the instance of the class
	 * @static
	 * @return BuggyPress_NewIssuePage
	 */
	public static function get_instance() {
		if ( !is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	final public function __clone() {
		trigger_error( "Singleton. No cloning allowed!", E_USER_ERROR );
	}

	final public function __wakeup() {
		trigger_error( "Singleton. No serialization allowed!", E_USER_ERROR );
	}

	protected function __construct() {
		$this->add_hooks();
	}
}
