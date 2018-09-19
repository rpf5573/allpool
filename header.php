<!DOCTYPE html>
<html>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<link rel="profile" href="http://gmpg.org/xfn/11">
<?php wp_head(); ?>
</head>

<body id="ap-responsive" <?php body_class(); ?>>

<div id="page" class="site <?php ap_page_class(); ?>">
  <header id="masthead" class="site-header" role="banner"> 
    <div class="wrapper max-box"> <?php
      // mobile menu trigger --- logo --- user icon
      ap_template_part( 'mobile', 'menu-trigger' ); ?>
      <div class="l-left"> <?php
        ap_template_part( 'site', 'logo' ); ?>
      </div>
      <div class="l-right"> <?php
        ap_template_part( 'site', 'nav' ); ?>
      </div>
    </div>
  </header> <?php 
  ap_template_part( 'page', 'banner' ); ?>
  <div id="content" class="site-content">