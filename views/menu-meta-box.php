<div id="buggypress-menu-items">
	<ul id="buggypress-menu-item-checklist">
		<?php foreach ( $items as $key => $label ): ?>
			<li><label><input type="checkbox" value="<?php esc_attr_e($key); ?>" /> <?php esc_html_e($label); ?></label></li>
		<?php endforeach; ?>
	</ul>
	<p class="button-controls">
		<span class="add-to-menu">
			<input type="submit"<?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-buggypress-menu-item" id="submit-buggypress-menu-items" />
		</span>
	</p>
</div>
