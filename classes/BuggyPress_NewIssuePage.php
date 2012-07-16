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
			$path = ( TRUE === $issue_post_type->has_archive ) ? $issue_post_type->rewrite['slug'] : $issue_post_type->has_archive;
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
			'path' => '((.+?)/)?'.$path.'/?$',
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
		remove_filter('the_content', 'wpautop');
		$form = $this->get_form(array('project_slug' => $project_slug));
		echo $form;

		//$form = new BuggyPress_UpdateIssueForm($issue);
		//$form->render();
	}

	public function process_form( $project_slug = '' ) {
		$form = $this->get_form(array('project_slug' => $project_slug));
		if ( !$form->isValid($_POST) ) {
			remove_filter('the_content', 'wpautop');
			echo $form;
			return;
		}
		$values = $form->getValues();
		$issue = $this->create_issue($values, $form->get_issue());
		if ( $issue ) {
			wp_redirect($issue->get_permalink(), 303);
			exit();
		}

		// if for some reason we failed, display the form again
		// TODO: handle this better, or eliminate it as a possibility
		echo $form;


		//$issue = new BuggyPress_Issue(0);
		//$form = new BuggyPress_UpdateIssueForm($issue);
		//$form->save($_POST);
		//wp_redirect($issue->get_permalink(), 303);
		//exit();
	}

	/**
	 * @param array $args
	 * @return BuggyPress_Form_IssueNew
	 */
	private function get_form( $args = array() ) {
		$defaults = array(
			'project_slug' => '',
		);
		$args = wp_parse_args($args, $defaults);
		$issue = new BuggyPress_Issue(0);

		if ( !empty($args['project_slug']) ) {
			$project = BuggyPress_Project::get_by_slug($args['project_slug']);
			if ( $project ) {
				$issue->set_project_id($project->get_id());
			}
		}

		$form = new BuggyPress_Form_IssueNew($issue);
		$form = apply_filters('buggypress_new_issue_form', $form, $args);
		return $form;
	}

	private function create_issue( $data, BuggyPress_Issue $issue = NULL ) {
		if ( !isset($issue) ) {
			$issue = new BuggyPress_Issue(0);
		}

		// TODO - security
		$args = array(
			'post_title' => $data[BuggyPress_Form_Element_IssueTitle::FIELD_NAME],
			'post_content' => $data[BuggyPress_Form_Element_IssueDescription::FIELD_NAME],
			'post_status' => 'publish', // TODO: Filter default status
		);
		$issue->save_post($args);
		$issue->set_project_id((int)$data[BuggyPress_Form_Element_IssueProject::FIELD_NAME]);
		$issue->set_assignee_id((int)$data[BuggyPress_Form_Element_IssueAssignee::FIELD_NAME]);
		$issue->set_type((int)$data[BuggyPress_Form_Element_IssueType::FIELD_NAME]);
		$issue->set_status((int)$data[BuggyPress_Form_Element_IssueStatus::FIELD_NAME]);
		$issue->set_priority((int)$data[BuggyPress_Form_Element_IssuePriority::FIELD_NAME]);
		$issue->set_resolution((int)$data[BuggyPress_Form_Element_IssueResolution::FIELD_NAME]);

		return $issue;
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
