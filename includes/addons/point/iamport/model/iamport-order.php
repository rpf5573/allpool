<?php

class IamportOrder {
  private $post;
  private $status;

  public function __construct($post) {
    $this->post = $post;
    $this->status = get_post_meta( $this->post->ID, 'order_status', true);
  }

  public function get_order_title() {
    return $this->post->post_title;
  }

  public function get_order_uid() {
    return get_post_meta( $this->post->ID, 'order_uid', true);
  }

  public function get_order_amount() {
    return intval(get_post_meta( $this->post->ID, 'order_amount', true));
  }

  public function get_amount_label() {
    $label = get_post_meta($this->post->ID, 'amount_label', true);
    if ( isset($label) && $label != 'null' && $this->status != "awaiting-vbank" ) {
      return $label;
    }
  }

  public function get_paid_amount() {
    // 가상계좌 입금대기중일때는 결제금액을 표시하지 않는다
    if ( $this->status != "awaiting-vbank" )	{
      return intval(get_post_meta( $this->post->ID, 'paid_amount', true));
    }
  }
  
  public function get_pay_method($return_native=false) {
    $method = get_post_meta( $this->post->ID, 'pay_method', true);
    if ( $return_native )		return $method;

    switch($method) {
      case 'card' :
      return '신용카드';

      case 'trans' :
      return '실시간계좌이체';

      case 'vbank' :
      return '가상계좌';

      case 'phone' :
      return '휴대폰소액결제';
    }

    return $method;
  }

  public function get_buyer_name() {
    return get_post_meta( $this->post->ID, 'buyer_name', true);
  }

  public function get_buyer_email() {
    return get_post_meta( $this->post->ID, 'buyer_email', true);
  }

  public function get_buyer_tel() {
    return get_post_meta( $this->post->ID, 'buyer_tel', true);
  }

  public function get_shipping_addr() {
    return get_post_meta( $this->post->ID, 'shipping_addr', true);
  } 

  public function get_order_status($return_native=false) {
    if ( $return_native )	return $this->status;

    if ( !empty($this->status) )	{
      switch($this->status) {
        case 'ready' :
        return '미결제';

        case 'paid' :
        return '결제완료';

        case 'cancelled' :
        return '환불됨';

        case 'failed' :
        return '결제실패';

        case 'awaiting-vbank' :
        return '가상계좌 입금대기중';
      }
    }

    return '미결제';
  }

  public function get_paid_date() {
    $paid_date = get_post_meta( $this->post->ID, 'paid_date', true);
    if ( !empty($paid_date) ) {
      if ( is_numeric($paid_date) ) { // unix timestamp(gmt). since 0.5.5
        return date('Y-m-d H:i:s', $paid_date + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ));
      }

      return $paid_date;
    }

    return '-';
  }

  public function get_receipt_url() {
    return get_post_meta( $this->post->ID, 'receipt_url', true);
  }

  public function get_vbank_info() {
    return get_post_meta( $this->post->ID, 'vbank_info', true);
  }

  public function set_order_amount($amount) {
    update_post_meta( $this->post->ID, 'order_amount', true);
  }

  public function set_paid_amount($amount) {
    update_post_meta( $this->post->ID, 'paid_amount', $amount, true);
  }

  public function set_pay_method($method) {
    update_post_meta( $this->post->ID, 'pay_method', $method, true);
  }

  public function set_paid_date($date) {
    update_post_meta( $this->post->ID, 'paid_date', $date );
  }

  public function set_order_status($status, $method=null) {
    if ( $method == 'vbank' && $status == 'ready' )		$status = 'awaiting-vbank';
    update_post_meta( $this->post->ID, 'order_status', $status );
  }

  public function set_receipt_url($url) {
    update_post_meta( $this->post->ID, 'receipt_url', $url);
  }

  public function set_vbank_info($vbank_info) {
    update_post_meta( $this->post->ID, 'vbank_info', $vbank_info);
  }

  public function add_failed_history($date, $reason=null) {
    add_post_meta( $this->post->ID, 'failed_history', array('date'=>$date, 'reason'=>$reason) );
  }

  public function get_failed_history() {
    return array_reverse( get_post_meta( $this->post->ID, 'failed_history' ) );
  }
  
  //static
  public static function find_by_order_uid($order_uid) {
    $args = array(
      'meta_key' => 'order_uid',
      'meta_value' => $order_uid,
      'posts_per_page' => 1,
      'post_type' => 'iamport_payment',
      'post_status' => 'any',
      'orderby' => 'ID',
      'order' => 'DESC'
    );

    if ( !current_user_can('administrator') && !current_user_can('editor') ) {
      $args['author'] = get_current_user_id();
    }

    $posts = get_posts( $args );
    if ( !empty($posts) )		return new IamportOrder($posts[0]);

    return null;
  }

  public static function find_by_id($id) {
    $post = get_post($id);
    $author_id = get_current_user_id();

    if ( !current_user_can('administrator') && !current_user_can('editor') && $post->post_author != $author_id )	return null;
    if ( !empty($post) )		return new IamportOrder($post);

    return null;
  }
}