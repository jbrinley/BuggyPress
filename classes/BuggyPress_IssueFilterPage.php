<?php

class BuggyPress_IssueFilterPage {
	/** @var BuggyPress_IssueFilterPage */
	private static $instance;

	public function set_rewrite_rules( $rules ) {
		global $wp_rewrite;
		$project_pto = get_post_type_object('project');
		$project_slug = $project_pto->rewrite['slug'];
		$issue_pto = get_post_type_object('issue');
		$issue_slug = $issue_pto->rewrite['slug'];
		$pagination_base = $wp_rewrite->pagination_base;
		$regex = "$project_slug/(.+)/$issue_slug(/$pagination_base/([0-9]+))?/?";
		$redirect = sprintf('index.php?post_type=%s&issue_project=%s&paged=%s', 'issue', $wp_rewrite->preg_index(1), $wp_rewrite->preg_index(3));
		$rules = array_merge(array($regex=>$redirect), $rules);
		return $rules;
	}

	/**
	 * @param WP_Query $query
	 */
	public function filter_the_query( $query ) {
		if ( !$this->is_issue_archive($query) ) {
			return;
		}

		// Project is context dependent, not saved in the filter
		$project_slug = get_query_var('issue_project');
		if ( $project_slug ) {
			$project = BuggyPress_Project::get_by_slug($project_slug);
			if ( $project ) {
				$query->query_vars['meta_query'][] = array(
					'key' => BuggyPress_Issue::META_KEY_PROJECT,
					'value' => $project->get_id(),
				);
			}
		}

		$filter = $this->get_current_filter();

		if ( $status = $filter->get_status('slug') ) {
			$query->query_vars['tax_query'][] = array(
				'taxonomy' => 'issue_status',
				'field' => 'slug',
				'terms' => $status,
				'operator' => 'IN',
			);
		}

		if ( $type = $filter->get_type('slug') ) {
			$query->query_vars['tax_query'][] = array(
				'taxonomy' => 'issue_type',
				'field' => 'slug',
				'terms' => $type,
				'operator' => 'IN',
			);
		}

		if ( $priority = $filter->get_priority('slug') ) {
			$query->query_vars['tax_query'][] = array(
				'taxonomy' => 'issue_priority',
				'field' => 'slug',
				'terms' => $priority,
				'operator' => 'IN',
			);
		}

		if ( $resolution = $filter->get_resolution('slug') ) {
			$query->query_vars['tax_query'][] = array(
				'taxonomy' => 'issue_resolution',
				'field' => 'slug',
				'terms' => $resolution,
				'operator' => 'IN',
			);
		}

		// TODO: assignee, tags, created, updated, due date
		// TODO: sorting

		// TODO: save the query as a post
		// TODO: track the user's saved queries
	}

	public function add_query_vars( $vars ) {
		$vars[] = 'issue_project';
		return $vars;
	}

	/**
	 * Render the filter form at the top of the main loop
	 *
	 * @wordpress-action loop_start
	 * @param WP_Query $query
	 */
	public function prepare_filter_form( $query ) {
		if ( !$this->is_issue_archive($query) ) {
			return;
		}
		$this->render_filter_form($this->get_current_form());
	}

	/**
	 * Handle a submitted filter form
	 *
	 * @param WP $wp
	 */
	public function process_filter_form( $wp ) {
		if ( !BuggyPress_Form_Filter::submitted() ) {
			return;
		}
		$form = $this->get_current_form();
		if ( !$form->isValid($_POST) ) {
			return;
		}
		$values = $form->getValues();

		$filter = $this->get_current_filter();
		$filter->set_type($values['buggypress_filter_type']);
		$filter->set_status($values['buggypress_filter_status']);
		$filter->set_priority($values['buggypress_filter_priority']);
		$filter->set_resolution($values['buggypress_filter_resolution']);

		global $wp_rewrite;
		// TODO: deal with sites not using permalinks?
		$url = $_SERVER['REQUEST_URI'];
		$url = preg_replace("#/$wp_rewrite->pagination_base/?[0-9]+?(/+)?$#", '/', $url); // strip off any existing paging

		wp_redirect(home_url($url), 302);
		exit();
	}

	/**
	 * @return BuggyPress_Form_Filter
	 */
	private function get_current_form() {
		$filter = $this->get_current_filter();
		$form = new BuggyPress_Form_Filter($filter);
		$form = apply_filters('buggypress_issue_filter_form', $form);
		return $form;
	}

	/**
	 * Get a user's currently set filter ID
	 * @param int $user_id
	 * @return BuggyPress_Filter
	 */
	private function get_current_filter() {
		return BuggyPress_Filter::current_filter();
	}

	private function render_filter_form( $form ) {
		include( BuggyPress::plugin_path('views/public/issue-filter-form.php') );
	}

	/**
	 * Determine if the query is the main query for an issue archive
	 *
	 * @param WP_Query $query
	 * @return bool
	 */
	private function is_issue_archive( $query ) {
		if ( !$query->is_main_query() ) {
			return FALSE;
		}
		if ( get_query_var('post_type') != 'issue' ) {
			return FALSE;
		}
		if ( !$query->is_archive() ) {
			return FALSE;
		}
		return TRUE;
	}

	public function select_template( $template ) {
		if ( is_archive() && get_query_var('post_type') == BuggyPress_Issue::POST_TYPE ) {
			// check in the theme's buggypress directory
			if ( $found = locate_template(array('buggypress/archive-'.BuggyPress_Issue::POST_TYPE.'.php'), FALSE) ) {
				return $found;
			}
			// check in the main theme directory
			if ( $found = locate_template(array('archive-'.BuggyPress_Issue::POST_TYPE.'.php'), FALSE) ) {
				return $found;
			}
			if ( file_exists(BuggyPress::plugin_path('views/page-templates/archive-'.BuggyPress_Issue::POST_TYPE.'.php')) ) {
				return BuggyPress::plugin_path('views/page-templates/archive-'.BuggyPress_Issue::POST_TYPE.'.php');
			}
		}
		return $template;
	}

	private function add_hooks() {
		add_filter( 'rewrite_rules_array', array( $this, 'set_rewrite_rules' ), 0, 1 );
		add_filter( 'pre_get_posts', array( $this, 'filter_the_query' ), 10, 1 );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 10, 1);
		add_filter( 'parse_request', array( $this, 'process_filter_form' ), 10, 1 );
		add_filter( 'template_include', array( $this, 'select_template' ), 10, 1 );
		add_action( 'issue_filter_form', array( $this, 'prepare_filter_form' ), 10, 1 );
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
	 * @return BuggyPress_IssueFilterPage
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
