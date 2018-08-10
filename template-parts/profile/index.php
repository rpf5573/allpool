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

$user_id     = ap_current_user_id(); // this is not current logged in user id
$logged_in_user_id = get_current_user_id();
$is_mypage = ( $user_id == $logged_in_user_id ) ? true : false;
$current_tab = ap_sanitize_unslash( 'tab', 'r', 'questions' );
?>

<div id="ap-user" class="ap-user <?php echo is_active_sidebar( 'ap-user' ) && is_anspress() ? 'ap-col-9' : 'ap-col-12'; ?>">
	<?php if ( '0' == $user_id && ! is_user_logged_in() ) : ?>

	<h1><?php _e( 'Please login to view your profile', 'anspress-question-answer' ); ?></h1>
	<?php else : $user_data = get_userdata( $user_id ); ?>
		<div class="ap-user-bio">
			<div class="l-row">
				<div class="l-row">
					<div class="ap-user-avatar">
						<?php echo get_avatar( $user_id, 80 ); ?>
					</div>
				</div>
				<div class="l-row">
					<div class="l-left">
						<div class="ap-user-info">
							<div class="ap-user-nickname"> <?php
								echo ap_user_display_name(
									[
										'user_id' => $user_id,
										'html'    => true,
										'is_profile' => true
									]
								);
								ap_user_info_edit_btn_with_modal( $user_data, 'nickname' ); ?>
							</div>
							<div class="ap-user-id">
								ID : <span> <?php echo $user_data->user_login; ?> </span>
							</div> <?php
							if ( $user_data->data->user_email ) { ?>
								<div class="ap-user-email">
									<span>E-mail : </span> <?=$user_data->data->user_email?>
								</div> <?php
							}
							// this is shown only in 'my' page not others
							if ( $user_id == $logged_in_user_id ) { ?>
								<div class="ap-user-password">
									<span>Password : </span> <div class="pw-secret"><span>*********</span></div> <?php 
									ap_user_info_edit_btn_with_modal( $user_data, 'password' ); ?>
								</div> <?php
							} ?>
						</div>
					</div>
					<div class="l-right">
						<div class="ap-user-desktop-buttons">
							<div class="user-mycred-creds">
								<?php do_action( 'ap_user_mycred_creds', $user_id ); ?>
							</div> <?php
							if ( $is_mypage ) { ?>
								<div class="point-charge"> <?php 
									AP_Point::point_charge_button( $user_id ); ?>
								</div> <?php
							} ?>
						</div>
					</div>
				</div>
			</div>
			<div class="l-row"> 
				<div class="ap-user-mobile-buttons">
					<div class="user-mycred-creds"> <?php
						AP_Reputation::mycred_creds( $user_id );
						if ( $is_mypage ) {
							AP_Point::mycred_creds( $user_id );
						} ?>
					</div> <?php
					if ( $is_mypage ) { ?>
						<div class="point-charge"> <?php
							AP_Point::point_charge_button( $user_id ); ?>
						</div> <?php
					} ?>
				</div> <?php
				if ( $is_mypage && ap_isset_post_value( 'confirm_email', false ) ) {
					ap_template_part( 'message', null, array(
						'body' => '메일을 확인해 주세요'
					) );
				} ?>
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