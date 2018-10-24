<?php
	wp_register_style( 'iamport-order-view-css',  IamportPaymentPlugin::$URL . 'assets/css/order-view.css');
	wp_enqueue_style('iamport-order-view-css');

	$buyer_name 	= $iamport_order->get_buyer_name();
	$buyer_email 	= $iamport_order->get_buyer_email();
	$buyer_tel 		= $iamport_order->get_buyer_tel();
	ob_start();
?>

	<table class="iamport-order-view">
		<tbody>
			<tr>
				<th>주문번호</th>
				<td><?=$iamport_order->get_order_uid()?></td>
			</tr>
			<?php if ($iamport_order->get_order_status(true) == 'paid') : ?>
			<tr>
				<th>매출전표</th>
				<td><a href="<?=$iamport_order->get_receipt_url()?>" target="_blank">매출전표보기</a></td>
			</tr>
			<tr>
				<th>결제일자</th>
				<td><?=$iamport_order->get_paid_date()?></td>
			</tr>
			<tr>
				<th>결제금액</th>
				<td><?=number_format($iamport_order->get_order_amount())?> 원 <?=$iamport_order->get_amount_label()?></td>
			</tr>
			<?php endif; ?>
			<tr>
				<th>주문명</th>
				<td><?=$iamport_order->get_order_title()?></td>
			</tr>
			<tr>
				<th>결제수단</th>
				<td><?=$iamport_order->get_pay_method()?></td>
			</tr>
			<tr>
				<th>결제상태</th>
				<td><b><?=$iamport_order->get_order_status()?></b></td>
			</tr>
			<?php if ($iamport_order->get_pay_method(true) == 'vbank') : $vbank_info = $iamport_order->get_vbank_info(); ?>
				<?php if (!empty($vbank_info['name'])) : ?>
				<tr>
					<th>가상계좌 입금은행</th>
					<td><?=$vbank_info['name']?></td>
				</tr>
				<?php endif; ?>
				<?php if (!empty($vbank_info['account'])) : ?>
				<tr>
					<th>가상계좌번호</th>
					<td><?=$vbank_info['account']?></td>
				</tr>
				<?php endif; ?>
				<?php if (!empty($vbank_info['due'])) : ?>
				<tr>
					<th>가상계좌 입금기한</th>
					<td><?=is_numeric($vbank_info['due']) ? date('Y-m-d H:i:s', $vbank_info['due']+(get_option( 'gmt_offset' ) * HOUR_IN_SECONDS)) : $vbank_info['due']?></td>
				</tr>
				<?php endif; ?>
			<?php endif; ?>
			<?php if(!empty($buyer_name)) : ?>
			<tr>
				<th>결제자 이름</th>
				<td><?=$buyer_name?></td>
			</tr>
			<?php endif; ?>
			<?php if(!empty($buyer_email)) : ?>
			<tr>
				<th>결제자 Email</th>
				<td><?=$buyer_email?></td>
			</tr>
			<?php endif; ?>
			<?php if(!empty($buyer_tel)) : ?>
			<tr>
				<th>결제자 전화번호</th>
				<td><?=$buyer_tel?></td>
			</tr>
			<?php endif; ?>
			<?php if(!empty($shipping_addr)) : ?>
			<?php endif; ?>
			
			<?php if(!empty($fileFields)) : foreach($fileFields as $file=>$fileData) : ?>
				<tr>
					<th><?=preg_replace('/%0D%0A/', '', $file)?></th>
					<td><a href='<?=$fileData['location']?>' download><?=$fileData['name']?></a></td>
				</tr>
			<?php endforeach; endif;?>
		</tbody>
	</table>

<?php
	return ob_get_clean();
