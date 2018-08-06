<?php
if ( ! isset( $template_args['price'] ) ) {
  wp_die( "No Price" );
}
$args = wp_json_encode(
  [
    '__nonce' => wp_create_nonce( 'purchase_answers_of_' . $template_args['question_id'] ),
    'id'      => $template_args['question_id'],
    'ap_ajax_action' => 'purchase_answers'
  ]
);
?>
<div class="ui tiny modal purchase-answers-modal">
  <div class="header">
    답변 구매
  </div>
  <div class="content">
    <p> <?=$template_args['price']?>원을 지불하여, <?=$template_args['answer_count']?>개의 답변을 열람하시겠습니까? </p>
  </div>
  <div class="actions">
    <div class="ui negative button">
      취소
    </div>
    <div class="ui positive button" apquery="<?=esc_js( $args )?>">
      구매
    </div>
  </div>
</div>