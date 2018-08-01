<?php
	ob_start();
?>
	
<style type="text/css">
  #iamport-order-action .inside {padding: 0}
</style>
<div id="minor-publishing">
  <div class="misc-pub-section">
    <label for="iamport-order-status-meta">결제상태 변경 :</label>
    <select id="iamport-order-status-meta" name="new_iamport_order_status">
      <option value="ready" <?=$order_status=='ready'?'selected':''?>>미결제</option>
      <option value="paid" <?=$order_status=='paid'?'selected':''?>>결제완료</option>
      <option value="cancelled" <?=$order_status=='cancelled'?'selected':''?>>결제취소 및 환불처리</option>
    </select>
  </div>
</div>
	
<div id="major-publishing-actions">
  <div id="delete-action"><?php
    if ( current_user_can( 'delete_post', $post->ID ) ) {
      if ( !EMPTY_TRASH_DAYS ) {
        $delete_text = '삭제하기';
      } else {
        $delete_text = '휴지통으로 이동';
      }
      ?><a class="submitdelete deletion" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>"><?php echo $delete_text; ?></a><?php
    } ?>
  </div>
  <div id="publishing-action">
    <input type="submit" class="button save_order button-primary tips" name="save" value="변경하기" />
  </div>
  <div class="clear"></div>
</div>

<?php
return ob_get_clean();