<?php
include "rapid-addon.php";

$ap_addon = new RapidAddon('Alpool Addon', 'ap_addon');

$GLOBALS['meta_fields'] = array( "answers" ,"votes_up", "votes_down", "year", "session", "inspection_check", "views", "roles" );
foreach( $GLOBALS['meta_fields'] as $field ) {
  $ap_addon->add_field( $field, strtoupper( $field ), 'text' );
}

$ap_addon->set_import_function('ap_addon_import_function');
function ap_addon_import_function( $question_id, $data, $import_options, $article ) {
  global $wpdb;

  ap_insert_qameta( $question_id, $data );


  for( $i = 0; $i < $data['answers']; $i++ ) {
    $users = array( 13, 11, 10, 12 );
    $answer_args = array(
      'post_author'    => $users[rand(0, count($users) - 1)],
      'post_content'   => "테스트 질문의 테스트 답변입니다",
      'post_name'      => $question_id,
      'post_parent'    => $question_id,
      'post_status'    => 'publish',
      'post_title'     => $question_id,
      'post_type'      => 'answer'
    );
    $answer_id = wp_insert_post( $answer_args, false );
    ap_insert_qameta( $answer_id );
  }
}

$ap_addon->run(
	array(
		"themes" => array("Alpool")
	)
);

// $current_time = current_time( 'mysql' );
//     $sql = "INSERT INTO `wp_ap_qameta` 
//     ( `post_id`, `ptype`, `last_updated`) 
//     VALUES ( {$id}, 'question', {$current_time} )";
//     $wpdb->query( $sql );