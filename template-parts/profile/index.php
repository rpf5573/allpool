<?php
/**
 * User profile template.
 * User profile index template.
 *
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author     Rahul Aryan <support@anspress.io>
 *
 * @link       https://anspress.io
 * @since      4.0.0
 * @package    AnsPress
 * @subpackage Templates
 */

$user_id     = ap_current_user_id();
$current_tab = ap_sanitize_unslash( 'tab', 'r', 'questions' );
?>

<div id="ap-user" class="ap-user <?php echo is_active_sidebar( 'ap-user' ) && is_anspress() ? 'ap-col-9' : 'ap-col-12'; ?>">

	<?php if ( '0' == $user_id && ! is_user_logged_in() ) : ?>

		<h1><?php _e( 'Please login to view your profile', 'anspress-question-answer' ); ?></h1>

	<?php else : ?>

		<div class="ap-user-bio">
			<div class="ap-user-avatar ap-pull-left">
				<?php echo get_avatar( $user_id, 80 ); ?>
			</div>
			<div class="no-overflow">
				<div class="ap-user-name">
					<?php
					echo ap_user_display_name(
						[
							'user_id' => $user_id,
							'html'    => true,
							'is_profile' => true
						]
					); ?>
				</div>
				<div class="ap-user-about">
					<?php echo get_user_meta( $user_id, 'description', true ); ?>
				</div>
			</div>
			<div class="ap-point-charge">
				<?php // echo do_shortcode( '[iamport_payment_button title="포인트 충전" description="아래 정보를 기입 후 결제진행해주세요." name="알풀 포인트 충전" amount="1000,3000,5000,10000" pay_method_list="card,trans,vbank,phone" field_list="name,email,phone"]결제하기[/iamport_payment_button]', true ); ?>
			</div>
		</div>
		<?php AP_Profile::user_menu(); ?>
		<?php AP_Profile::sub_page_template(); ?>

	<?php endif; ?>

</div>

<?php if ( is_active_sidebar( 'ap-user' ) && is_anspress() ) : ?>
	<div class="ap-question-right ap-col-3">
		<?php dynamic_sidebar( 'ap-user' ); ?>
	</div>
<?php endif; ?>