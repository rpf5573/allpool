<?php

class AP_Inspection_Check {

  public static function save_inspection_check( $qameta, $post, $updated ) {
    // insert question meta at admin
    $acf = ap_isset_post_value( 'acf', false );
    
    if ( $acf ) {
      if ( isset( $acf['question_inspection_check'] ) ) {
        $qameta['inspection_check'] = (int)$acf['question_inspection_check'];
      }

      if ( isset( $acf['answer_inspection_check'] ) ) {
        $qameta['inspection_check'] = (int)$acf['answer_inspection_check'];
      }
    }

    return $qameta;
  }
}