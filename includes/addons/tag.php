<?php

class AP_tag {
  
  public static function register_question_tag() {
    $labels = array(
			'name'               => __( 'Tags', 'anspress-question-answer' ),
			'singular_name'      => _x( 'Tag', 'anspress-question-answer' ),
			'all_items'          => __( 'All Tags', 'anspress-question-answer' ),
			'add_new_item'       => _x( 'Add New Tag', 'anspress-question-answer' ),
			'edit_item'          => __( 'Edit Tag', 'anspress-question-answer' ),
			'new_item'           => __( 'New Tag', 'anspress-question-answer' ),
			'view_item'          => __( 'View Tag', 'anspress-question-answer' ),
			'search_items'       => __( 'Search Tag', 'anspress-question-answer' ),
			'not_found'          => __( 'Nothing Found', 'anspress-question-answer' ),
			'not_found_in_trash' => __( 'Nothing found in Trash', 'anspress-question-answer' ),
			'parent_item_colon'  => '',
		);

		$args   = array(
			'hierarchical' => false,
			'labels'       => $labels,
			'rewrite'      => false,
		);

		/**
		 * Now let WordPress know about our taxonomy
		 */
		register_taxonomy( 'ap_tag', array( 'question', 'answer' ), $args );
  } 
  public static function admin_tag_menu() {
    add_submenu_page( 'anspress', __( 'Question Tags', 'anspress-question-answer' ), __( 'Tags', 'anspress-question-answer' ), 'manage_options', 'edit-tags.php?taxonomy=ap_tag' );
  }

}