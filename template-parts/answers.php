<?php
/**
 * Answers content
 * Control the output of answers.
 *
 * @link https://anspress.io/anspress
 * @since 2.0.1
 * @author Rahul Aryan <support@anspress.io>
 * @package AnsPress
 */
?>
<apanswersw style="<?php echo ! ap_have_answers() ? 'display:none' : ''; ?>">

	<div id="ap-answers-c" class="ap-answers">
		<div class="ap-sorting-tab clearfix">
			<div class="slash-pattern"></div>
			<h3 class="ap-answers-label" ap="answers_count_t">
				<span class="inner">
					<?php $count = ap_get_answers_count(); ?>
					<span itemprop="answerCount"><?php echo (int) $count; ?></span>
					<?php echo _n( 'Answer', 'Answers', $count, 'anspress-question-answer' ); ?>
				</span>
			</h3>

			<?php ap_answers_tab( get_the_permalink() ); ?>
		</div>

		<div id="answers">
			<apanswers>
				<?php if ( ap_have_answers() ) : ?>

					<?php
					while ( ap_have_answers() ) :
						ap_the_answer(); ?>
						<?php ap_template_part( 'answer' ); ?>
					<?php endwhile; ?>

				<?php endif; ?>
			</apanswers>

		</div>

		<?php if ( ap_have_answers() ) : ?>
			<?php ap_answers_the_pagination(); ?>
		<?php endif; ?>
	</div>
	
</apanswersw>

<style>
	#answers-order {
		display: none !important;
	}
</style>