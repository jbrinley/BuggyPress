<p>
	<label for="<?php echo self::FIELD_VISIBILITY; ?>"><?php self::_e('Visibility') ?>: </label>
	<select name="<?php echo self::FIELD_VISIBILITY; ?>" id="<?php echo self::FIELD_VISIBILITY; ?>">
		<option value="<?php echo self::MEMBERS; ?>" <?php selected(self::MEMBERS, $visibility); ?>><?php self::_e('Members Only'); ?></option>
		<option value="<?php echo self::USERS; ?>" <?php selected(self::USERS, $visibility); ?>><?php self::_e('Registered Users'); ?></option>
		<option value="<?php echo self::ALL; ?>" <?php selected(self::ALL, $visibility); ?>><?php self::_e('Public'); ?></option>
	</select>
</p>
<p>
	<label for="<?php echo self::FIELD_COMMENTING; ?>"><?php self::_e('Commenting') ?>: </label>
	<select name="<?php echo self::FIELD_COMMENTING; ?>" id="<?php echo self::FIELD_COMMENTING; ?>">
		<option value="<?php echo self::MEMBERS; ?>" <?php selected(self::MEMBERS, $commenting); ?>><?php self::_e('Members Only'); ?></option>
		<option value="<?php echo self::USERS; ?>" <?php selected(self::USERS, $commenting); ?>><?php self::_e('Registered Users'); ?></option>
		<option value="<?php echo self::ALL; ?>" <?php selected(self::ALL, $commenting); ?>><?php self::_e('Public'); ?></option>
	</select>
</p>