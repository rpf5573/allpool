<?php
require_once 'model/iamport-payment-shortcode.php';

class IamportPaymentPlugin {

  public static $URL = ANSPRESS_URL . 'includes/addons/point/iamport/';

  public static function iamport_activated() {
    self::create_history_page();
    self::create_thankyou_page();
    self::add_endpoints();
  }

  public static function create_history_page() {
    $slug = 'iamport_history';	
    $history_page = self::get_page_by_slug($slug);
    if( empty($history_page) ) {
      $page_data = array(
        'post_status'		=> 'publish',
        'post_type'			=> 'page',
        'post_author'		=> 1,
        'post_name'			=> $slug,
        'post_title'		=> '결제내역 - 아임포트',
        'post_content'		=> '[iamport_history_page]',
        'post_parent'		=> 0,
        'comment_status'	=> 'closed'
      );

      $page_id = wp_insert_post( $page_data );
    }
  }

  public static function create_thankyou_page() {
    $slug = 'iamport_thankyou';	
    $thankyou_page = self::get_page_by_slug($slug);
    if( empty($thankyou_page) ) {
      $page_data = array(
        'post_status'		=> 'publish',
        'post_type'			=> 'page',
        'post_author'		=> 1,
        'post_name'			=> $slug,
        'post_title'		=> '결제완료 - 아임포트',
        'post_content'		=> '[iamport_thankyou_page]',
        'post_parent'		=> 0,
        'comment_status'	=> 'closed'
      );

      $page_id = wp_insert_post( $page_data );
    }
  }

  public static function get_page_by_slug($slug) {
    $args = array(
      'name'        => $slug,
      'post_type'   => 'page',
      'post_status' => 'publish',
      'numberposts' => 1
    );
    return get_posts($args);
  }

  public static function add_endpoints() {
    add_rewrite_endpoint( 'iamport-order-view', EP_PAGES );
	  add_rewrite_endpoint( 'iamport-order-received', EP_PERMALINK | EP_PAGES );
	  flush_rewrite_rules();
  }

}

new IamportPaymentShortcode();