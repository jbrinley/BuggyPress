<?php
/**
 * @var array $users
 * @var array $members
 * @var array $admins
 */
?>
<style type="text/css">
	table.bp-project-members {
		width: 100%;
	}
	table.bp-project-members .bp-member,
	table.bp-project-members .bp-admin {
		text-align: center;
		width: 25%;
	}
</style>
<table class="bp-project-members">
	<thead>
		<tr>
			<th class="bp-member"><?php _e('Member', 'buggypress'); ?></th>
			<th class="bp-admin"><?php _e('Admin', 'buggypress'); ?></th>
			<th class="bp-user"></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $users as $user ): ?>
			<tr>
				<td class="bp-member" title="<?php _e('Member', 'buggypress'); ?>">
					<input type="checkbox" name="<?php echo BuggyPress_MB_Members::FIELD_MEMBERS; ?>[]" value="<?php echo $user->ID; ?>" <?php checked(TRUE, in_array($user->ID, $members)); ?> />
				</td>
				<td class="bp-admin" title="<?php _e('Administrator', 'buggypress'); ?>">
					<input type="checkbox" name="<?php echo BuggyPress_MB_Members::FIELD_ADMINS; ?>[]" value="<?php echo $user->ID; ?>" <?php checked(TRUE, in_array($user->ID, $admins)); ?> />
				</td>
				<td class="bp-user">
					<?php esc_html_e($user->user_login); ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>