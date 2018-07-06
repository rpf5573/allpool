<?php
/**
 * Ask question page
 *
 * @link https://anspress.io
 * @since 0.1
 *
 * @package AnsPress
 */
?>

<div class="ask-form-container"> <?php 
	if ( ap_user_can_ask() ) {
		ap_ask_form();
	} else if ( is_user_logged_in() ) { ?>
		<div class="ap-no-permission">
			<?php _e( 'You do not have permission to ask a question.', 'anspress-question-answer' ); ?>
		</div> <?php 
	} else {
		ap_template_part( 'message', null, array(
			'type' => 'error', 
			'header' => '잠시만요!',
			'body' => '로그인을 먼저 해주세요!',
		) );
		ap_template_part( 'login', 'signup' );
	} ?>
</div>