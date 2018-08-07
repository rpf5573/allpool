<?php
$args = wp_json_encode(
  [
    '__nonce' => wp_create_nonce( 'user_info_edit_' . $template_args['user_data']->ID ),
    'id'      => $template_args['user_data']->ID,
    'ap_ajax_action' => 'user_info_edit_password'
  ]
);?>
<div class="ui mini modal ap-user-info-edit-modal --password">
  <div class="header">
    닉네임 변경
  </div>
  <div class="content">
    닉네임을 입력해 주세요
  </div>
  <div class="actions">
    <div class="ui negative button">
      취소
    </div>
    <div class="ui positive button" apquery="<?=esc_js( $args )?>">
      확인
    </div>
  </div>
</div>