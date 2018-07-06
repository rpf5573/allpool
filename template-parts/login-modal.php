<div class="ui tiny modal login-modal">
  <i class="close icon"></i>
  <div class="header">
    로그인
  </div>
  <div class="content"> <?php
    /**
     * Show wordpress social login buttons.
     */
    if ( do_action( 'wordpress_social_login' ) ) : ?>
      <div class="social-login clearfix">
        <h4><?php _e( 'Log in using', 'ab' ); ?></h4>
        <?php do_action( 'wordpress_social_login' ); ?>
      </div> <?php
    endif; ?>
    <div class="login-form ui form"> <?php
      $form = wp_login_form( array( 'echo' => false ) );
      $form = str_replace( 'login-username', 'login-username field', $form );
      $form = str_replace( 'login-password', 'login-password field', $form );
      $form = str_replace( 'button-primary', 'ui primary button', $form );
      echo $form; ?>
    </div>
  </div>
  <div class="actions">
    <a href="<?php echo wp_lostpassword_url(); ?>" class="forget-btn"><?php _e( 'Forgot password', 'anspress-question-answer' ); ?></a>
    <a href="<?php echo wp_registration_url(); ?>" class="signup-btn btn btn-primary"><?php _e( 'Register', 'anspress-question-answer' ); ?></a>
  </div>
</div>