<label for="<?php echo self::FIELD_PROJECT; ?>"><?php self::_e('Project'); ?>:</label>
<select name="<?php echo self::FIELD_PROJECT; ?>">
	<option value="0"><?php self::_e('-- Select --'); ?></option>
	<?php foreach ( $projects as $project ): ?>
		<option value="<?php echo $project->ID; ?>" <?php selected($current_project, $project->ID); ?>><?php echo get_the_title($project->ID); ?></option>
	<?php endforeach; ?>
</select>