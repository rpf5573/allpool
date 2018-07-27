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
	if ( ! is_user_logged_in() ) {
		ap_template_part( 'message', null, array(
			'type' => 'error', 
			'header' => '잠시만요!',
			'body' => '로그인을 먼저 해주세요',
		) );
		ap_template_part( 'login', 'signup' );
	} else {
		$thing = ap_user_can_ask( false, true );
		if ( is_wp_error( $thing ) ) {
			ap_template_part( 'message', null, array(
				'type' => 'error', 
				'header' => '잠시만요!',
				'body' => $thing->get_error_message(),
			) );
		} else if ( ! $thing ) { ?>
			<div class="ap-no-permission"> <?php 
				ap_template_part( 'message', null, array(
					'type' => 'error', 
					'header' => '잠시만요!',
					'body' => __( 'You do not have permission to ask a question.', 'anspress-question-answer' ),
				) ); ?>
			</div> <?php 
		} else {
			ap_ask_form();
		}
	} ?>
</div>