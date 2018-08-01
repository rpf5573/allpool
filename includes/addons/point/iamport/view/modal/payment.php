<?php
	ob_start();
?>
	<div class="iamport-modal <?=$device?>" id="<?=$uuid?>" style="display:none">
		<div class="iamport-modal-header">
			<div class="iamport-modal-close"><img src="<?=IamportPaymentPlugin::$URL . 'assets/img/close-button.png'?>" /></div>
			<div class="main-title"><?=$attr['title']?></div>
			<div class="sub-title"><?=$attr['description']?></div>
		</div>

		<div class="iamport-modal-container">
			<div class="iamport-modal-content">
			</div>
		</div>
		<?php if ( $hasCustomFields ) : ?>
			<p class="button-holder" style="text-align:center">
				<a href="#" class="iamport-go-back" id="iamport-go-back"><img src="<?=IamportPaymentPlugin::$URL . 'assets/img/go-back.svg'?>"></a>
				<a href="#" target="#<?=$uuid?>" class="iamport-modal-button with-go-back" id="iamport-payment-submit" data-redirect-after="<?=$a['redirect_after']?>">결제하기</a>
			</p>
		<?php else : ?>
			<p class="button-holder" style="text-align:center">
				<a href="#" target="#<?=$uuid?>" class="iamport-modal-button w100" id="iamport-payment-submit" data-redirect-after="<?=$a['redirect_after']?>">결제하기</a>
			</p>
		<?php endif; ?>	
	</div>
<?php
	$html_payment_box = ob_get_clean();
	$trim_html_payment_box = preg_replace($regexNewline, '', $html_payment_box);
?>
	<script type="text/javascript">
		jQuery(function($) {
			if ( $('#<?=$uuid?>').length == 0 ) {
				$('body').append('<?=$trim_html_payment_box?>');
			}
		});
	</script>
