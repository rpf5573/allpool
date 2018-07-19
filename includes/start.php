<?php

/**
 * Include warning page template.
 *
 * @param string $template Current template.
 * @return string
 */
function ap_warning_template_inc( $template ) {
  $template = locate_template( [ 'warning.php' ] );
  return $template;
}

if ( ! function_exists('acf_add_local_field_group') || ! class_exists( 'Theme_My_Login' ) || ! defined( 'myCRED_VERSION' ) ) {
	add_filter( 'template_include', 'ap_warning_template_inc', 99 );
	return;
}

show_admin_bar(false);

function ap_load_textdomain() {
  $directory = get_template_directory();
  
  load_theme_textdomain( 'anspress-question-answer', get_template_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'ap_load_textdomain' );

require_once 'core/anspress.php';