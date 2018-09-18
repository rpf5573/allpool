<?php
/**
 * Template used for generating single answer item.
 *
 * @author Rahul Aryan <support@anspress.io>
 * @link https://anspress.io/anspress
 * @package AnsPress
 * @subpackage Templates
 * @since 0.1
 * @since 4.1.2 Removed @see ap_recent_post_activity().
 */

if ( ap_user_can_read_answer() ) :
?>
<div id="post-<?php the_ID(); ?>" <?php post_class(); ?> apid="<?php the_ID(); ?>" ap="answer">
	<div class="ap-content" itemprop="suggestedAnswer<?php echo ap_is_selected() ? ' acceptedAnswer' : ''; ?>" itemscope itemtype="https://schema.org/Answer">
		<div class="ap-single-vote"><?php ap_vote_btn(); ?></div>
		<div class="ap-avatar">
			<a href="<?php ap_profile_link(); ?>">
				<?php ap_author_avatar( ap_opt( 'avatar_size_qanswer' ) ); ?>
			</a>
		</div>
		<div class="ap-cell clearfix">
			<div class="ap-cell-inner">
				<div class="ap-cell-head">
					<div class="ap-cell-metas">
						<span class="ap-author" itemprop="author" itemscope itemtype="http://schema.org/Person">
							<?php echo '<label>작성자</label>' . ap_user_display_name( [ 'html' => true ] ); ?>
						</span>
						<span class="ap-author-meta">
							<?php echo ap_user_display_meta(); ?>
						</span>
						<a href="<?php the_permalink(); ?>" class="ap-posted">
							<?php
							$posted = 'future' === get_post_status() ? __( 'Scheduled for', 'anspress-question-answer' ) : __( 'Published', 'anspress-question-answer' );

							$time = ap_get_time( get_the_ID(), 'U' );

							if ( 'future' !== get_post_status() ) {
								$time = ap_human_time( $time );
							}

							printf( '<label>작성일</label> <time itemprop="datePublished" datetime="%1$s">%2$s</time>', ap_get_time( get_the_ID(), 'c' ), $time );
							?>
						</a>
					</div>
				</div>

				<div class="ap-cell-content-wrapper">
					<?php
					/**
					 * Action triggered before answer content.
					 *
					 * @since   3.0.0
					 */
					do_action( 'ap_before_answer_content' );
					?>

					<div class="ap-answer-content ap-cell-content clearfix" itemprop="text" ap-content>
							<?php the_content(); ?>
					</div>

					<?php
					/**
					 * Action triggered after answer content.
					 *
					 * @since   3.0.0
					 */
					do_action( 'ap_after_answer_content' );
					?>

				</div>

				<div class="ap-post-footer clearfix">
					<?php echo ap_select_answer_btn_html(); // xss okay ?>
					<?php ap_post_actions_buttons(); ?>
					<?php do_action( 'ap_answer_footer' ); ?>
				</div>

			</div>
		</div>

	</div>
</div>

<style>
  .ap-avatar img {
    width: 50px;
    height: 50px;
  }
</style>

<?php
endif;