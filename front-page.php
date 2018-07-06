<?php
get_header(); ?>

<style>
  html, body {
    width: 100%;
    height: 100%;
  }
</style>

<div class="content-area">
  <main id="main" class="site-main" role="main">
    <div class="m-center-box"> <?php
      ap_template_part( 'front', 'message' );
      ap_template_part( 'front', 'category-search' ); ?>
    </div>
  </main> <!-- #main -->
</div> <!-- content-area -->

<?php 
get_footer(); ?>