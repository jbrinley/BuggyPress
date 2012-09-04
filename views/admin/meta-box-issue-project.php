<?php
/**
 * @var array $projects
 * @var int $current_project
 */
?>
<label for="<?php echo BuggyPress_MB_IssueProject::FIELD_PROJECT; ?>"><?php _e('Project', 'buggypress'); ?>:</label>
<select name="<?php echo BuggyPress_MB_IssueProject::FIELD_PROJECT; ?>">
	<option value="0"><?php _e('-- Select --', 'buggypress'); ?></option>
	<?php foreach ( $projects as $project ): ?>
		<option value="<?php echo $project->ID; ?>" <?php selected($current_project, $project->ID); ?>><?php echo get_the_title($project->ID); ?></option>
	<?php endforeach; ?>
</select>