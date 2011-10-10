<?php

function bp_the_issue_type() {
	$term = bp_get_the_issue_type();
	echo apply_filters('buggypress_issue_type', $term?$term->name:'', $term);
}

function bp_get_the_issue_type( $post_id = 0 ) {
	if ( !$post_id ) {
		$post_id = get_the_ID();
	}
	$term = BuggyPress_Issue::get_type($post_id);
	return $term;
}

function bp_the_issue_priority() {
	$term = bp_get_the_issue_priority();
	echo apply_filters('buggypress_issue_priority', $term?$term->name:'', $term);
}

function bp_get_the_issue_priority( $post_id = 0 ) {
	if ( !$post_id ) {
		$post_id = get_the_ID();
	}
	$term = BuggyPress_Issue::get_priority($post_id);
	return $term;
}

function bp_the_issue_status() {
	$term = bp_get_the_issue_status();
	echo apply_filters('buggypress_issue_status', $term?$term->name:'', $term);
}

function bp_get_the_issue_status( $post_id = 0 ) {
	if ( !$post_id ) {
		$post_id = get_the_ID();
	}
	$term = BuggyPress_Issue::get_status($post_id);
	return $term;
}

function bp_the_issue_assignee() {
	$assignee = bp_get_the_issue_assignee();
	$name = $assignee?$assignee->display_name:__('Unassigned');
	echo apply_filters('buggypress_issue_assignee', $assignee->display_name, $assignee);
}

function bp_get_the_issue_assignee( $post_id = 0 ) {
	if ( !$post_id ) {
		$post_id = get_the_ID();
	}
	$user = BuggyPress_Issue::get_assignee($post_id);
	return $user;
}

function bp_get_the_project( $post_id = 0 ) {
	if ( !$post_id ) {
		$post_id = get_the_ID();
	}
	$project = BuggyPress_Issue::get_project($post_id);
	return $project;
}