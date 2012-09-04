<?php
/**
 * @var string $visibility
 * @var string $commenting
 */
?>
<p>
	<label for="<?php echo BuggyPress_MB_Permissions::FIELD_VISIBILITY; ?>"><?php _e('Visibility', 'buggypress') ?>: </label>
	<select name="<?php echo BuggyPress_MB_Permissions::FIELD_VISIBILITY; ?>" id="<?php echo BuggyPress_MB_Permissions::FIELD_VISIBILITY; ?>">
		<option value="<?php echo BuggyPress_MB_Permissions::MEMBERS; ?>" <?php selected(BuggyPress_MB_Permissions::MEMBERS, $visibility); ?>><?php _e('Members Only', 'buggypress'); ?></option>
		<option value="<?php echo BuggyPress_MB_Permissions::USERS; ?>" <?php selected(BuggyPress_MB_Permissions::USERS, $visibility); ?>><?php _e('Registered Users', 'buggypress'); ?></option>
		<option value="<?php echo BuggyPress_MB_Permissions::ALL; ?>" <?php selected(BuggyPress_MB_Permissions::ALL, $visibility); ?>><?php _e('Public', 'buggypress'); ?></option>
	</select>
</p>
<p>
	<label for="<?php echo BuggyPress_MB_Permissions::FIELD_COMMENTING; ?>"><?php _e('Commenting', 'buggypress') ?>: </label>
	<select name="<?php echo BuggyPress_MB_Permissions::FIELD_COMMENTING; ?>" id="<?php echo BuggyPress_MB_Permissions::FIELD_COMMENTING; ?>">
		<option value="<?php echo BuggyPress_MB_Permissions::MEMBERS; ?>" <?php selected(BuggyPress_MB_Permissions::MEMBERS, $commenting); ?>><?php _e('Members Only', 'buggypress'); ?></option>
		<option value="<?php echo BuggyPress_MB_Permissions::USERS; ?>" <?php selected(BuggyPress_MB_Permissions::USERS, $commenting); ?>><?php _e('Registered Users', 'buggypress'); ?></option>
		<option value="<?php echo BuggyPress_MB_Permissions::ALL; ?>" <?php selected(BuggyPress_MB_Permissions::ALL, $commenting); ?>><?php _e('Public', 'buggypress'); ?></option>
	</select>
</p>