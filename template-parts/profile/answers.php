<?php
/**
 * Display answers list
 *
 * This template is used in base page, category, tag , etc
 *
 * @link https://anspress.io
 * @since 4.0.0
 *
 * @package AnsPress
 */

global $answers;
?>

<?php if ( ap_have_answers() ) : ?>
	<div id="ap-bp-answers">
	<?php
		/* Start the Loop */
	while ( ap_have_answers() ) :
		ap_the_answer();
		ap_template_part( 'profile/answer-item' );
		endwhile;
	?>
	</div> <?php
	ap_answers_the_pagination_in_profile(); ?>
	<?php
	else :
		_e( 'No answer posted by this user.', 'anspress-question-answer' );
	endif;
?>