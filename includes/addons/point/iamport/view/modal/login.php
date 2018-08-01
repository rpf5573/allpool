<?php
  ob_start();
?>
	<div class="iamport-modal result" id="iamport-login-box" style="display:none">
		<div class="iamport-modal-header">
			<div class="iamport-modal-close"><img src="<?= IamportPaymentPlugin::$URL . 'assets/img/close-button.png' ?>" /></div>
			<div class="main-title">로그인 필요</div>
			<div class="sub-title">결제기능을 이용하기 위해서는 로그인이 필요합니다.</div>
		</div>
		<p class="button-holder" style="text-align:center">
			<a class="iamport-modal-button w100" href="<?=wp_login_url("http" . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}", true)?>">로그인하러 가기</a>
		</p>
	</div>
<?php
	$html_login_box = ob_get_clean();
	$trim_html_login_box = preg_replace($regexNewline, '', $html_login_box);
?>
<script type="text/javascript">
  jQuery(function($) {
    if ( $('#iamport-login-box').length == 0 ) {
      $('body').append('<?=$trim_html_login_box?>');
    }
  });
</script>