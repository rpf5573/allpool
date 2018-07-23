<?php

/*  No use code but, good
/* --------------------------------------------------- */
function ref_ap_is_in_expert_categories( $_post, $user_id = false ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	$expert_categories = get_user_meta( $user_id, 'expert_categories', true );
	if ( ! empty( $expert_categories ) && in_array( $_post->post_type, [ 'question', 'answer' ] ) ) {
		$terms = array();
		if ( $_post->post_type == 'question' ) {
			$terms = get_the_terms( $_post->ID, 'question_category' );
		} 
		else if ( $_post->post_type == 'answer' ) {
			$terms = get_the_terms( $_post->post_parent, 'question_category' );
		}
		$family = array();
		foreach( $terms as $term ) {
			$family = array_merge($family, get_ancestors( $term->term_id, 'question_category', 'taxonomy' ));
			$family[] = $term->term_id;
		}
		$result = array_intersect($expert_categories, $family);
		if ( ! empty( $result ) ) {
			return true;
		}
	}
	return false;	
}

function filter_questions_by_their_own_category( $query ) {
  $screen = get_current_screen();
  $user_id = get_current_user_id();

  if ( ! is_null( $screen ) && ap_is_moderator( $user_id ) ) {
    if ( $screen->id == 'edit-question' && $screen->post_type == 'question' ) {
      $terms = get_user_meta( $user_id, 'expert_categories', true );
      
      $args = array(
        'tax_query' => array(
          array(
            'taxonomy' => 'question_category',
            'field'    => 'term_id',
            'terms'    => $terms,
            'operator' => 'IN',
          ),
        ),
      );
      $query->tax_query->queries[] = $args;
      $query->query_vars['tax_query'] = $query->tax_query->queries;
    }
  }
}

function change_question_inspection_check( $post_id, $post ) {
	global $wpdb;
	$prefix = $wpdb->prefix;
	$question_id = $post->post_parent;

	// check all the other answer's inspection check status
	$sql = "SELECT count(*)
					FROM {$prefix}ap_qameta
					LEFT JOIN {$prefix}posts
					ON ({$prefix}posts.ID = {$prefix}ap_qameta.post_id)
					WHERE ({$prefix}posts.post_status = 'publish')
					AND {$prefix}posts.post_type = 'answer'
					AND {$prefix}posts.post_parent = {$question_id}
					AND {$prefix}ap_qameta.inspection_check = 0";

	$count = $wpdb->get_var( $sql );

	// all answers were inspected well
	if ( (int)$count == 0 ) {
		$sql = "UPDATE {$prefix}ap_qameta SET `inspection_check` = 1 WHERE `post_id` = {$question_id} ";
	} 
	// somee answers were not inspected
	else {
		$sql = "UPDATE {$prefix}ap_qameta SET `inspection_check` = 0 WHERE `post_id` = {$question_id} ";
	}

	$result = $wpdb->query( $sql );

	if ( ! isset( $result ) ) {
		wp_die( 'Query Error call to Administrator !' );
	}
	
}

function filter_ptags_on_images($content) {
  
  $content = preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
  return $content;
}

function tinymce_remove_root_block_tag( $init ) {
  $init['forced_root_block'] = false; 
  return $init;
}
add_filter( 'tiny_mce_before_init', 'tinymce_remove_root_block_tag' );