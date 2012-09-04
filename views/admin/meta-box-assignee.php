<?php
/**
 * @var int $assignee
 * @var array $users
 */
?>
<p>
	<label for="<?php echo BuggyPress_MB_Assignee::FIELD_ASSIGNEE; ?>"><?php _e('Assigned To', 'buggypress'); ?>: </label>
	<select name="<?php echo BuggyPress_MB_Assignee::FIELD_ASSIGNEE; ?>">
		<option value="0"><?php _e('-- Select --', 'buggypress'); ?></option>
		<?php foreach ( $users as $user ): ?>
			<option value="<?php echo $user->ID; ?>" <?php selected($assignee, $user->ID); ?>><?php esc_html_e($user->display_name); ?></option>
		<?php endforeach; ?>
	</select>
</p>