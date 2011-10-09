<p>
	<label for="<?php echo self::FIELD_ASSIGNEE; ?>"><?php self::_e('Assigned To'); ?>: </label>
	<select name="<?php echo self::FIELD_ASSIGNEE; ?>">
		<option value="0"><?php self::_e('-- Select --'); ?></option>
		<?php foreach ( $users as $user ): ?>
			<option value="<?php echo $user->ID; ?>" <?php selected($assignee, $user->ID); ?>><?php esc_html_e($user->display_name); ?></option>
		<?php endforeach; ?>
	</select>
</p>