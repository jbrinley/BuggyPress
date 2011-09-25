<?php
/**
 * Defines a meta box that may be shared among several post types.
 */
abstract class BuggyPress_Meta_Box extends BuggyPress_Plugin {
	protected $id;
	protected $title;
	protected $context;
	protected $priority;
	protected $callback_args;

	protected $defaults = array(
		'title' => '',
		'context' => 'advanced',
		'priority' => 'default',
		'callback_args' => NULL,
	);

	public function __construct( $id, $args = array() ) {
		$this->id = $id;
		if ( !$this->defaults['title'] ) {
			$this->defaults['title'] = $this->id;
		}
		$args = wp_parse_args($args, $this->defaults);
		$this->title = self::__($args['title']);
		$this->context = $args['context'];
		$this->priority = $args['priority'];
		$this->callback_args = $args['callback_args'];
	}

	/**
	 * @abstract
	 * @param object $post The post being edited
	 * @return void
	 */
	abstract public function render( $post );

	/**
	 * @abstract
	 * @param int $post_id The ID of the post being saved
	 * @param object $post The post being saved
	 * @return void
	 */
	abstract public function save( $post_id, $post );

	/**
	 * Return the ID of the meta box
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Return the translated title of the meta box
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}


	/**
	 * Return the context in which to display the meta box
	 *
	 * @return string
	 */
	public function get_context() {
		return $this->context;
	}

	/**
	 * Return the priority in which to display the meta box
	 *
	 * @return string
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 * Return arguments to pass to the meta box
	 *
	 * @return array|null
	 */
	public function get_callback_args() {
		return $this->callback_args;
	}

	/**
	 * Set the arguments to pass to the meta box
	 *
	 * @param array|null $args
	 * @return void
	 */
	public function set_callback_args( $args ) {
		$this->callback_args = $args;
	}
}
