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
			<th class="bp-member"><?php self::_e('Member'); ?></th>
			<th class="bp-admin"><?php self::_e('Admin'); ?></th>
			<th class="bp-user"></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $users as $user ): ?>
			<tr>
				<td class="bp-member" title="<?php self::_e('Member'); ?>">
					<input type="checkbox" name="<?php echo self::FIELD_MEMBERS; ?>[]" value="<?php echo $user->ID; ?>" <?php checked(TRUE, in_array($user->ID, $members)); ?> />
				</td>
				<td class="bp-admin" title="<?php self::_e('Administrator'); ?>">
					<input type="checkbox" name="<?php echo self::FIELD_ADMINS; ?>[]" value="<?php echo $user->ID; ?>" <?php checked(TRUE, in_array($user->ID, $admins)); ?> />
				</td>
				<td class="bp-user">
					<?php esc_html_e($user->user_login); ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>