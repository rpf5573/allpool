<?php
$main_title = get_field('page_banner__main');
if ( $main_title ) { ?>
  <div class="page-banner">
    <h1 class="page-banner__main">
      <?php echo $main_title; ?>
    </h1> <?php
    $sub_title = get_field('page_banner__sub');
    if ( $sub_title ) { ?>
      <h3 class="page-banner__sub">
        <?php echo $sub_title; ?>
      </h3> <?php 
    } ?>
  </div> <?php
} ?>