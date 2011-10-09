<?php

function bp_the_issue_type() {
	$term = bp_get_the_issue_type();
	echo apply_filters('buggypress_issue_type', $term?$term->name:'');
}

function bp_get_the_issue_type( $post_id = 0 ) {
	if ( !$post_id ) {
		$post_id = get_the_ID();
	}
	$terms = BuggyPress_Type::get_terms($post_id);
	if ( !$terms ) {
		return NULL;
	}
	return reset($terms);
}

function bp_the_issue_priority() {
	$term = bp_get_the_issue_priority();
	echo apply_filters('buggypress_issue_priority', $term?$term->name:'');
}

function bp_get_the_issue_priority( $post_id = 0 ) {
	if ( !$post_id ) {
		$post_id = get_the_ID();
	}
	$terms = BuggyPress_Priority::get_terms($post_id);
	if ( !$terms ) {
		return NULL;
	}
	return reset($terms);
}

function bp_the_issue_status() {
	$term = bp_get_the_issue_status();
	echo apply_filters('buggypress_issue_status', $term?$term->name:'');
}

function bp_get_the_issue_status( $post_id = 0 ) {
	if ( !$post_id ) {
		$post_id = get_the_ID();
	}
	$terms = BuggyPress_Status::get_terms($post_id);
	if ( !$terms ) {
		return NULL;
	}
	return reset($terms);
}