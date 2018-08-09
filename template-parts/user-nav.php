<?php
$user = wp_get_current_user();
$user_link = ap_user_link( $user->ID ); ?>
<div class="user-nav">
  <div class="ui pointing dropdown">
    <i class="fas fa-user-circle fa-2x"></i>
    <span class="user-nav__name"> <?php
      echo $user->display_name; ?>
    </span>
    <i class="fas fa-angle-down"></i>
    <div class="user-nav__menu menu">
      <a class="item" href="<?php echo $user_link; ?>"><i class="database icon"></i> 마이페이지 </a>
      <a class="item" href="<?php echo wp_logout_url(); ?>"><i class="power off icon"></i>로그아웃</a>
    </div>
  </div>
</div>