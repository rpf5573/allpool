<div class="site-info">
  <div class="l-left">
    <div class="site-logo">
      <a href="<?php echo home_url(); ?>">
        <img src="<?php echo ANSPRESS_URL . 'assets/images/logo_footer.png'; ?>" alt="">
      </a>
    </div>
  </div>
  <div class="empty-space"></div>
  <div class="l-right">
    <div class="site-info__links"> <?php
      $args = array(
        'theme_location'  => 'footer',
        'menu_id'         => '',
        'menu_class'		  => 'footer-menu no-style',
        'container_class' => 'footer-menu-container'
      );
      wp_nav_menu( $args ); ?>
    </div>
    <div class="site-info__etc">
      알풀사업부 사업자등록번호: 784-86-00650 / 관리자 이메일 : alpool.co.kr@gmail.com <br>
      주소: 인천광역시 연수구 송도과학로 32 (송도동, 송도테크노파크IT센터) S동 2004호 / 전화번호: 032-209-7079 <br>
      Copyright 2018 www.alpool.co.kr . All Rights Reserved. Created by <a href="https://presscat.co.kr/" title="홈페이지 제작 업체 프레스캣">PRESSCAT</a>.
    </div>
  </div>
</div>