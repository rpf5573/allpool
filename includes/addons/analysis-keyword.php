<?php
// This class responsible for creating categories for problem analysis

class AP_Analysis_Keyword {

  public static function register_question_analysis_keyword_taxonomy() {
   /**
    * Labels for category taxonomy.
    *
    * @var array
    */
   $labels = array(
     'name'               => __( 'Question Analysis Keyword', 'anspress-question-answer' ),
     'singular_name'      => __( 'Analysis', 'anspress-question-answer' ),
     'all_items'          => __( 'All analysis Keywords', 'anspress-question-answer' ),
     'add_new_item'       => __( 'Add New Analysis Keyword', 'anspress-question-answer' ),
     'edit_item'          => __( 'Edit Analysis Keyword', 'anspress-question-answer' ),
     'new_item'           => __( 'New Analysis Keyword', 'anspress-question-answer' ),
     'view_item'          => __( 'View Analysis Keyword', 'anspress-question-answer' ),
     'search_items'       => __( 'Search Analysis Keywords', 'anspress-question-answer' ),
     'not_found'          => __( 'Nothing Found', 'anspress-question-answer' ),
     'not_found_in_trash' => __( 'Nothing found in Trash', 'anspress-question-answer' ),
     'parent_item_colon'  => '',
   );

   /**
    * Arguments for category taxonomy
    *
    * @var array
    * @since 2.0
    */
   $args = array(
     'hierarchical'       => true,
     'labels'             => $labels,
     'rewrite'            => false,
     'publicly_queryable' => true,
   );

   /**
    * Now let WordPress know about our taxonomy
    */
   register_taxonomy( 'question_analysis_keyword', [ 'question' ], $args );
  }

  public static function admin_analysis_keyword_menu() {
    add_submenu_page( 'anspress', __( 'Questions Analysis Keywords', 'anspress-question-answer' ), __( 'Analysis Keyword', 'anspress-question-answer' ), 'manage_options', 'edit-tags.php?taxonomy=question_analysis_keyword' );
  }

}