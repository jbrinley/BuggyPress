<?php

/**
 * Determine the current project context.
 *
 * @return int The project ID
 */
function buggypress_get_context() {
	if ( is_singular(BuggyPress_Project::POST_TYPE) ) {
		return get_queried_object_id();
	}

	if ( is_singular(BuggyPress_Issue::POST_TYPE) ) {
		return bp_get_the_project(get_queried_object_id());
	}

	if ( $project_id = get_query_var('issue_project') ) {
		return $project_id;
	}
}