<?php
/**
 * Display question list
 *
 * This template is used in base page, category, tag , etc
 *
 * @link https://anspress.io
 * @since unknown
 *
 * @package AnsPress
 */
?> <?php 

ap_template_part( 'list-head' );

if ( ap_have_questions() ) : ?>

	<div class="ap-questions">
		<?php
			/* Start the Loop */
		while ( ap_have_questions() ) :
			ap_the_question();
			ap_template_part( 'question', 'list-item' );
			endwhile;
		?>
	</div>
	<?php ap_questions_the_pagination(); ?>

<?php else : ?>

	<p class="ap-no-questions">
		<?php esc_attr_e( 'There are no questions matching your query or you do not have permission to read them.', 'anspress-question-answer' ); ?>
	</p>

	<?php ap_template_part( 'login-signup' ); ?>
<?php endif; ?>