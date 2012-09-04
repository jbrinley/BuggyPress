<?php

/**
 * A filter is a lot like an issue. Basically is serves as
 * a prototype issue, and filters issues that are/are not like it.
 *
 * It's not, theoretically speaking, a subclass of of Issue,
 * but functionally they're nigh identical
 *
 * A special note on post statuses: the post status of a filter
 * determines who can see it.
 *  - publish: visible to everyone
 *  - private: visible only to the author
 *  - draft: visible only to one anonymous user (will be auto-deleted)
 */
class BuggyPress_Filter extends BuggyPress_Issue {
	const POST_TYPE = 'issue_filter';
	/** @var BuggyPress_Post_Type */
	private static $cpt = NULL;

	public function __construct( $post_id ) {
		parent::__construct($post_id);
		if ( !$post_id ) {
			$this->set_default_filters();
		}
	}

	public function get_post() {
		if ( !$this->post_id ) {
			// return a stub post
			$post = new stdClass();
			$post->ID = 0;
			$post->post_status = 'draft';
			$post->post_type = self::POST_TYPE;
			$post->post_author = get_current_user_id();
			$post->post_content = '';
			$post->post_title = '';
			return $post;
		}
		return get_post($this->post_id);
	}

	public function set_default_filters() {
		$this->status = array(get_term_by('slug', 'open', self::$tax_status->get_id()));
	}

	public function get_assigned_term( $taxonomy, $format = 'id' ) {
		$term = NULL;
		$terms = wp_get_object_terms($this->post_id, $taxonomy);
		if ( count($terms) == 1 ) {
			$terms = reset($terms);
		}
		return $this->format_term($terms, $format);
	}

	public static function current_filter( $user_id = 0 ) {
		$user_id = $user_id?$user_id:get_current_user_id();
		$filter_id = 0;
		if ( $user_id ) {
			$filter_id = get_user_option( 'issue_filter', $user_id );
		} elseif ( !empty($_COOKIE['bp_issue_filter']) ) {
			$filter_id = get_transient($_COOKIE['bp_issue_filter']);
		}
		if ( !$filter_id ) {
			$filter_id = self::create_user_filter($user_id);
		}
		// TODO: filters should have an appropriate "context"
		// TODO: save a different filter per context?
		return new BuggyPress_Filter((int)$filter_id);
	}


	/**
	 * @static
	 * Intialize the post type and related features
	 */
	public static function init() {
		self::create_post_type();
	}

	public static function create_post_type() {
		self::$cpt = new BuggyPress_Post_Type( self::POST_TYPE );
		self::$cpt->public = FALSE;
		self::$cpt->has_archive = FALSE;
		self::$cpt->show_ui = FALSE;
		self::$cpt->permalink_epmask = EP_NONE;
		self::$cpt->set_post_type_label( __('Filter', 'buggypress'), __('Filters', 'buggypress') );
		self::$cpt->slug = _x( 'issue_filters', 'post type slug', 'buggypress' );
		self::$cpt->remove_support(array('title', 'author'));

		self::register_taxonomies();
	}

	private static function register_taxonomies() {
		self::$tax_priority->post_types[] = self::POST_TYPE;
		self::$tax_resolution->post_types[] = self::POST_TYPE;
		self::$tax_status->post_types[] = self::POST_TYPE;
		self::$tax_type->post_types[] = self::POST_TYPE;
	}

	/**
	 * Create a new default filter for the given user
	 *
	 * Note: this expects to be called before headers are sent.
	 *
	 * @static
	 * @param $user_id
	 * @return int
	 */
	private static function create_user_filter( $user_id ) {
		$post = array(
			'post_author' => $user_id,
			'post_status' => $user_id?'private':'draft',
			'post_type' => self::POST_TYPE,
			'post_title' => sprintf(__('User Filter %d', 'buggypress'), $user_id),
		);
		$post_id = wp_insert_post($post);
		if ( $user_id ) {
			update_user_option( $user_id, 'issue_filter', $post_id );
		} else {
			$key = 'bp_issue_filter_'.wp_generate_password(12, FALSE);
			set_transient( $key, $post_id, 60*60*24*7 );
			setcookie( 'bp_issue_filter', $key, time()+60*60*24*7, COOKIEPATH, COOKIE_DOMAIN, NULL, TRUE );
		}
		$filter = new BuggyPress_Filter($post_id);
		$filter->set_default_filters();
		return $post_id;
	}
}
