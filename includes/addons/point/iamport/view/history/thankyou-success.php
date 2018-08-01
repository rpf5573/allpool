<?php
	ob_start();
?>
	<?php if ($iamport_result->data->status == 'paid') : ?>
	<h3>결제가 정상적으로 완료되었습니다.</h3>
	<?php elseif ($iamport_result->data->status == 'failed') : ?>
	<h3>결제에 실패하였습니다</h3>
	<p><?=$iamport_result->data->fail_reason?></p>
	<?php else : ?>
	<h3>(결제 전)주문이 접수되었습니다.</h3>
	<?php endif; ?>

	<p><a href="<?=$order_view_url?>">결제정보 확인하러 가기</a></p>
	<script type="text/javascript">
		location.href = '<?=$redirect_after ? $redirect_after : $order_view_url?>';
	</script>
	
<?php
	return ob_get_clean();