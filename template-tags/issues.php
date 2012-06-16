<?php

function bp_the_issue_type() {
	$term = bp_get_the_issue_type();
	echo apply_filters('buggypress_issue_type', $term?$term->name:'', $term);
}

function bp_get_the_issue_type( $post_id = 0 ) {
	$issue = bp_get_issue($post_id);
	$term = $issue->get_type();
	return $term;
}

function bp_the_issue_priority() {
	$term = bp_get_the_issue_priority();
	echo apply_filters('buggypress_issue_priority', $term?$term->name:'', $term);
}

function bp_get_the_issue_priority( $post_id = 0 ) {
	$issue = bp_get_issue($post_id);
	$term = $issue->get_priority($post_id);
	return $term;
}

function bp_the_issue_status() {
	$term = bp_get_the_issue_status();
	echo apply_filters('buggypress_issue_status', $term?$term->name:'', $term);
}

function bp_get_the_issue_status( $post_id = 0 ) {
	$issue = bp_get_issue($post_id);
	$term = $issue->get_status($post_id);
	return $term;
}

function bp_the_issue_assignee() {
	$assignee = bp_get_the_issue_assignee();
	$name = $assignee?$assignee->display_name:__('Unassigned');
	echo apply_filters('buggypress_issue_assignee', $name, $assignee);
}

/**
 * @param int $post_id
 * @return WP_User|NULL
 */
function bp_get_the_issue_assignee( $post_id = 0 ) {
	$issue = bp_get_issue($post_id);
	$user_id = $issue->get_assignee_id($post_id);
	if ( $user_id ) {
		return new WP_User($user_id);
	} else {
		return NULL;
	}
}

function bp_get_the_project( $post_id = 0 ) {
	$issue = bp_get_issue($post_id);
	$project_id = $issue->get_project_id();
	return $project_id;
}

function bp_get_the_project_link( $post_id = 0 ) {
	$project = bp_get_the_project($post_id);
	if ( !$project ) {
		return '';
	}
	$url = get_permalink($project);
	$link = sprintf('<a href="%s">%s</a>', $url, get_the_title($project));
	return apply_filters('bp_get_the_project_link', $link, $project);
}

function bp_the_project_link() {
	echo apply_filters('bp_the_project_link', bp_get_the_project_link());
}

/**
 * @param int $post_id
 * @return BuggyPress_Issue
 */
function bp_get_issue( $post_id = 0 ) {
	if ( !$post_id ) {
		$post_id = get_the_ID();
	}
	return new BuggyPress_Issue($post_id);
}