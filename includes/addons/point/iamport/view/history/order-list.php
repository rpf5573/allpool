<?php
  wp_register_style( 'iamport-order-list-css', IamportPaymentPlugin::$URL . 'assets/css/order-list.css');
	wp_enqueue_style('iamport-order-list-css');

	ob_start();
?>

	<table class="iamport-order-list">
		<thead>
			<tr>
				<th class="column-primary">주문명</th>
				<th>주문번호</th>
				<th>결제수단</th>
				<th>주문일자</th>
				<th>주문상태</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($posts as $p) : $iamport_order = new IamportOrder($p); ?>
			<tr>
				<td class="column-primary">
					<?=$p->post_title?>
					<span class="mobile-content">
						<br><span><b>주문번호</b> : <?=$iamport_order->get_order_uid()?></span>
						<br><span><b>결제수단</b> : <?=$iamport_order->get_pay_method()?></span>
						<br><span><b>주문일자</b> : <?=$iamport_order->get_paid_date()?></span>
						<br><span><b>주문상태</b> : <?=$iamport_order->get_order_status()?></span>
						<br><a target="_blank" class="view-order" href="<?=add_query_arg( 'iamport-order-view', $iamport_order->get_order_uid(), $history_page_url )?>">보기</a>
					</span>
				</td>
				<td><?=$iamport_order->get_order_uid()?></td>
				<td><?=$iamport_order->get_pay_method()?></td>
				<td><?=$iamport_order->get_paid_date()?></td>
				<td><?=$iamport_order->get_order_status()?></td>
				<td><a target="_blank" class="view-order" href="<?=add_query_arg( 'iamport-order-view', $iamport_order->get_order_uid(), $history_page_url )?>">보기</a></td>
			</tr>
			<?php endforeach; ?>

			<?php if (empty($posts)) : ?>
			<tr>
				<td colspan="6">결제내역을 찾을 수 없습니다.</td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>
  
<?php
return ob_get_clean();
