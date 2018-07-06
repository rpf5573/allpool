<?php
$just_be_here = '';
if ( ap_is_login_related_page() ) {
  $just_be_here = ' href="#" ';
} 
?>
<div class="login-trigger">
  <a class="login-trigger__btn no-style" <?php echo $just_be_here; ?>>
    Login or Register
  </a>
</div>