<?php 
	wp_register_style('iamport-shortcode-page-css', IamportPaymentPlugin::$URL . 'assets/css/iamport-shortcode-page.css', array(), "20180730");
	wp_enqueue_style('iamport-shortcode-page-css');

	/* ---------- 아임포트 설정에서 '저장하기' 버튼 눌렀을때 ---------- */
	if ( isset($_POST['action']) && $_POST['action'] == "update_iamport_settings" ) {
		if ( wp_verify_nonce($_POST['iamport-settings'], 'iamport-options') ) {
			$iamportSetting = get_option('iamport_setting');

			$iamportSetting['user_code'] = $_POST['user_code'];
			$iamportSetting['rest_key'] = $_POST['rest_key'];
			$iamportSetting['rest_secret'] = $_POST['rest_secret'];
			$iamportSetting['login_required'] = $_POST['login_required'];
			$iamportSetting['pg_for_payment'] = array(
				'card' 	=> $_POST['pg_for_card'],
				'trans' => $_POST['pg_for_trans'],
				'vbank' => $_POST['pg_for_vbank'],
				'phone' => $_POST['pg_for_phone']
			);
			$iamportSetting['pg_etc'] = array(
				'danal.biz_num' => $_POST['danal_biz_num']
			);
			$iamportSetting['vbank_day_limit'] = $_POST['vbank_day_limit'];

			update_option('iamport_setting', $iamportSetting);

		} else {
			?><div class="error">update failed</div><?php
		}
	}

	ob_start();

	$settings = get_option('iamport_setting');
	if ( empty($settings) ) {
		/* -------------------- 설정파일 백업으로부터 복원 -------------------- */
		$iamportSetting['user_code'] = get_option('iamport_user_code');
		$iamportSetting['rest_key'] = get_option('iamport_rest_key');
		$iamportSetting['rest_secret'] = get_option('iamport_rest_secret');
		$iamportSetting['login_required'] = get_option('iamport_login_required');
		$iamportSetting['pg_for_payment'] = get_option('iamport_pg_for_payment');	
		$iamportSetting['pg_etc'] = get_option('iamport_pg_etc');
		$iamportSetting['vbank_day_limit'] = "none";

		update_option('iamport_setting', $iamportSetting);
	}
	$iamportSetting = get_option('iamport_setting');

	$pgList = array(
		'default' => '- 기본값 사용 -',
		'html5_inicis' => 'KG이니시스',
	);

	$vbankDueOptions = array("none" => "지정안함(PG사 계약시 설정값을 그대로 적용합니다)");
	for ($i=0; $i < 14; $i++) {
		$vbankDueOptions[ "{$i}d" ] = $i == 0 ? "당일 자정까지" : "+ {$i}일 자정까지";
	}
?>
	<div class="wrap">
		<h2>아임포트 결제설정 페이지</h2>
		<p>
			<h3>1. 아임포트 결제정보 설정</h3>
			<form method="post" action="">
				<table class="form-table shortcode-box">
					<tbody>
						<tr valign="top">
							<th scope="row" style="width:160px;padding-left:10px;"><label for="iamport_user_code">[아임포트] 가맹점 식별코드</label></th>
							<td>
								<input class="regular-text" name="user_code" type="text" id="iamport_user_code" value="<?=$iamportSetting['user_code']?>" /><br>
								<a target="_blank" href="https://admin.iamport.kr">https://admin.iamport.kr</a> 에서 회원가입 후, "시스템설정" > "내정보"에서 확인하실 수 있습니다.
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" style="width:160px;padding-left:10px;"><label for="iamport_rest_key">[아임포트] REST API 키</label></th>
							<td>
								<input class="regular-text" name="rest_key" type="text" id="iamport_rest_key" value="<?=$iamportSetting['rest_key']?>" /><br>
								<a target="_blank" href="https://admin.iamport.kr">https://admin.iamport.kr</a> 에서 회원가입 후, "시스템설정" > "내정보"에서 확인하실 수 있습니다.
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" style="width:160px;padding-left:10px;"><label for="iamport_rest_secret">[아임포트] REST API Secret</label></th>
							<td>
								<input class="regular-text" name="rest_secret" type="text" id="iamport_rest_secret" value="<?=$iamportSetting['rest_secret']?>" /><br>
								<a target="_blank" href="https://admin.iamport.kr">https://admin.iamport.kr</a> 에서 회원가입 후, "시스템설정" > "내정보"에서 확인하실 수 있습니다.
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" style="width:160px;padding-left:10px;"><label for="iamport_login_required">로그인 필요</label></th>
							<td>
								<label><input name="login_required" type="checkbox" id="iamport_login_required" value="Y" <?=$iamportSetting['login_required'] == 'Y' ? 'checked' : ''?>/>로그인 된 사용자에게만 구매 허용하시려면 체크하세요</label>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" style="width:160px;padding-left:10px;"><label for="payment_pg">결제수단별 PG설정<br>(복수PG사용자만 설정)</label></th>
							<td>
								아임포트에서 복수PG를 설정해 사용 중이시면 결제수단별로 원하시는 PG사를 설정해주세요.<br>설정되지 않으면 아임포트 > 시스템 설정의 "기본 PG"사로 모두 연동됩니다.<br>
								<table class="form-table">
									<tr>
										<th style="text-align:center;padding:2px">신용카드</th>
										<th style="text-align:center;padding:2px">계좌이체</th>
										<th style="text-align:center;padding:2px">가상계좌</th>
										<th style="text-align:center;padding:2px">휴대폰소액결제</th>
									</tr>
									<tr>
										<td>
											<select name="pg_for_card" style="width:100%">
												<?php foreach($pgList as $key=>$val) : ?>
												<option value="<?=$key?>" <?=$iamportSetting['pg_for_payment']['card'] == $key ? 'selected':'' ?>><?=$val?></option>
												<?php endforeach; ?>
											</select>
										</td>
										<td>
											<select name="pg_for_trans" style="width:100%">
												<?php foreach($pgList as $key=>$val) : ?>
												<option value="<?=$key?>" <?=$iamportSetting['pg_for_payment']['trans'] == $key ? 'selected':'' ?>><?=$val?></option>
												<?php endforeach; ?>
											</select>
										</td>
										<td>
											<select name="pg_for_vbank" style="width:100%">
												<?php foreach($pgList as $key=>$val) : ?>
												<option value="<?=$key?>" <?=$iamportSetting['pg_for_payment']['vbank'] == $key ? 'selected':'' ?>><?=$val?></option>
												<?php endforeach; ?>
											</select>
										</td>
										<td>
											<select name="pg_for_phone" style="width:100%">
												<?php foreach($pgList as $key=>$val) : ?>
												<option value="<?=$key?>" <?=$iamportSetting['pg_for_payment']['phone'] == $key ? 'selected':'' ?>><?=$val?></option>
												<?php endforeach; ?>
											</select>
										</td>
									</tr>
								</table>
							</td>
						</tr>

						<!-- 가상계좌 입금기한 -->
						<tr valign="top">
							<th scope="row" style="width:160px;padding-left:10px;"><label for="iamport_login_required">가상계좌 입금기한</label></th>
							<td>
								<select name="vbank_day_limit">
									<?php foreach ($vbankDueOptions as $key => $val) : ?>
									<option value="<?=$key?>" <?=$iamportSetting['vbank_day_limit'] == $key ? 'selected':'' ?>><?=$val?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>

					</tbody>
				</table>
				
				<?php wp_nonce_field('iamport-options', 'iamport-settings'); ?>
				<input type="hidden" name="action" value="update_iamport_settings" />
				<input class="button-primary" type="submit" name="iamport-options" value="저장하기" />
			</form>
		</p>
	</div>
<?php

$iamport_admin_html = ob_get_clean();

return $iamport_admin_html;