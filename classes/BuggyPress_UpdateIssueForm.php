<?php

class BuggyPress_UpdateIssueForm {
	/** @var BuggyPress_Issue */
	private $issue = NULL;
	private $action = '';

	public function __construct( BuggyPress_Issue $issue ) {
		$this->issue = $issue;
		$this->action = $_SERVER['REQUEST_URI'];
	}

	public function set_action( $action ) {
		$this->action = $action;
	}

	public function render() {
		printf('<form method="post" action="%s">', $this->action);
		$this->render_project_select();
		$this->render_type_field();
		$this->render_title_field();
		$this->render_description_field();
		$this->submit_button(__('Create Issue', 'buggypress'));
		echo '</form>';
	}

	public function save( $data ) {
		// TODO - security
		$args = array(
			'post_title' => $data['issue-title'],
			'post_content' => $data['issue-description'],
			'post_status' => 'publish',
		);
		$this->issue->save_post($args);
		$this->issue->set_project_id($data['issue-project']);
		$this->issue->set_type((int)$data['taxonomies']['issue_type']);
	}

	public function render_project_select() {
		?>
		<p class="issue-project">
			<label for="issue-project"><?php _e('Project', 'buggypress'); ?>:</label>
			<?php wp_dropdown_pages(array(
				'post_type' => BuggyPress_Project::POST_TYPE,
				'post_status' => 'publish',
				'name' => 'issue-project',
				'selected' => $this->issue->get_project_id(),
			)); ?>
		</p>
		<?php
	}

	public function render_type_field() {
		$this->render_taxonomy_select('issue_type', __('Type'));
	}

	public function render_title_field() {
		?>
		<p class="issue-title">
			<label for="issue-title"><?php _e('Title', 'buggypress'); ?>:</label>
			<input type="text" value="<?php esc_attr_e($this->issue->get_title()); ?>" name="issue-title" id="issue-title" size="40" />
		</p>
		<?php
	}

	public function render_description_field() {
		wp_editor($this->issue->get_description(), 'issue-description', array(
			'textarea_rows' => 5,
		));
	}

	private function render_taxonomy_select( $taxonomy, $label ) {
		$args = array(
			'show_option_all' => '',
			'show_option_none' => '',
			'orderby' => 'id',
			'order' => 'ASC',
			'show_last_update' => 0,
			'show_count' => 0,
			'hide_empty' => 0,
			'child_of' => 0,
			'exclude' => '',
			'echo' => 1,
			'selected' => 0,
			'hierarchical' => 0,
			'name' => 'taxonomies'."[$taxonomy]",
			'id' => 'taxonomies-'.$taxonomy,
			'class' => 'postform',
			'depth' => 0,
			'tab_index' => 0,
			'taxonomy' => $taxonomy,
			'hide_if_empty' => FALSE,
		);
		?>
		<p class="<?php esc_attr_e($taxonomy); ?>">
			<label for="<?php esc_attr_e($args['id']); ?>"><?php esc_html_e($label); ?>:</label>
			<?php wp_dropdown_categories($args); ?>
		</p>
		<?php
	}

	private function submit_button( $label ) {
		$button = '<input type="submit" name="submit" id="submit" class="primary" value="%s" />';
		$button = sprintf($button, $label);
		$button = '<p class="submit">' . $button . '</p>';
		echo $button;
	}
}
