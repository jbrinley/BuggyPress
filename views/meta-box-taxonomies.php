<?php
/**
 * @var BuggyPress_MB_Taxonomies $this
 */
?>
<?php foreach ( $this->taxonomies as $taxonomy => $args ): ?>
	<p class="<?php esc_attr_e($taxonomy); ?>">
		<label for="<?php echo BuggyPress_MB_Taxonomies::FIELD_GROUP; ?>-<?php esc_attr_e($taxonomy); ?>"><?php esc_html_e($args['label']); ?>:</label>
		<?php wp_dropdown_categories($args); ?>
	</p>
<?php endforeach; ?>