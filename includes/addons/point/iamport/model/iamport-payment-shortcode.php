<?php

require_once('iamport.php');
require_once('iamport-order.php');
require_once('iamport-payment-button.php');
require_once('iamport-payment-info.php');

class IamportPaymentShortcode {
  private $user_code;
  private $api_key;
  private $api_secret;
  private $shortcode;
  private $payment_info;

  public function __construct() {
    if( is_admin() ) {
      add_action('admin_menu', array($this, 'iamport_admin_menu') );
    }
    add_action( 'init', array($this, 'init') );
    add_action( 'wp_enqueue_scripts', array($this, 'iamport_script_enqueue'), 99 );
    add_filter( 'query_vars', array($this, 'add_query_vars'), 0 );

    add_action( 'wp_ajax_get_order_uid', array($this, 'ajax_get_order_uid') );
    add_action( 'wp_ajax_nopriv_get_order_uid', array($this, 'ajax_get_order_uid') );
    
    add_action( 'add_meta_boxes', array($this, 'iamport_order_metabox') );
		add_action( 'save_post', array($this, 'save_iamport_order_metabox') );
  }

  public function init() {
    $settings = get_option('iamport_setting');
    if ( empty($settings) ) {
      /* -------------------- 설정파일 백업으로부터 복원 -------------------- */
      $iamportSetting['user_code'] = get_option('iamport_user_code');
      $iamportSetting['rest_key'] = get_option('iamport_rest_key');
      $iamportSetting['rest_secret'] = get_option('iamport_rest_secret');
      $iamportSetting['login_required'] = get_option('iamport_login_required');
      $iamportSetting['pg_for_payment'] = get_option('iamport_pg_for_payment');	
      $iamportSetting['pg_etc'] = get_option('iamport_pg_etc');

      update_option('iamport_setting', $iamportSetting);
    }
    
    $iamportSetting = get_option('iamport_setting');
    $this->user_code 		= $iamportSetting['user_code'];
    $this->api_key 			= $iamportSetting['rest_key'];
    $this->api_secret 		= $iamportSetting['rest_secret'];
    $this->login_required 	= $iamportSetting['login_required'];
    $this->pg_for_payment 	= $iamportSetting['pg_for_payment'];
    $this->etc 				= $iamportSetting['pg_etc'];

    $configuration 					        = new stdClass();
    $configuration->login_required 	= $this->login_required === 'Y';
    $configuration->pg_for_payment 	= $this->pgForPayment();
    $configuration->etc 			      = $this->etc;

    $this->shortcode 	= new IamportPaymentButton($this->user_code, $this->api_key, $this->api_secret, $configuration);
    $this->payment_info = new IamportPaymentInfo($this->user_code, $this->api_key, $this->api_secret, $configuration);

    $this->create_iamport_post_type();

    wp_enqueue_style('iamport-payment-shortcode-css', IamportPaymentPlugin::$URL . 'assets/css/iamport-payment-shortcode.css', array(), "20180730");
    
    // wp_enqueue_script('iamport-bundle-js', IamportPaymentPlugin::$URL . 'dist/main-babel.js', array('jquery'), '20171228');
  }

  public function iamport_admin_menu() {
    add_submenu_page(
      'edit.php?post_type=iamport_payment',
      '아임포트 설정', 
      '아임포트 설정', 
      'administrator',
      'iamport-config', 
      array( $this, 'setting_page' )
    );
    
  }

  public function add_query_vars($vars) {
    $vars[] = 'iamport-order-view';
    $vars[] = 'iamport-order-received';
    $vars[] = 'redirect-after';

    return $vars;
  }

  public function setting_page() {
  }

  private function create_iamport_post_type() {
    register_post_type( 'iamport_payment',
      array(
        'labels'		 		=> array(
          'name' 				=> '아임포트 결제목록',
          'singular_name' 	=> '아임포트 결제목록'
        ),
        'menu_icon' 			=> IamportPaymentPlugin::$URL . 'assets/img/iamport.jpg',
        'show_ui' 				=> true,
        'show_in_nav_menus' 	=> false,
        'show_in_admin_bar' 	=> true,
        'public' 				=> true,
        'has_archive' 			=> false,
        'rewrite' 				=> array('slug' => 'iamport_payment'),
        'map_meta_cap' 			=> true,
        'capabilities' 			=> array(
          'edit_post' 		=> true,
          'create_posts' 		=> false
        )
      )
    );

    remove_post_type_support( 'iamport_payment', 'editor' );

    add_filter( 'manage_iamport_payment_posts_columns', array($this, 'iamport_payment_columns') );
    add_action( 'manage_iamport_payment_posts_custom_column' , array($this, 'iamport_payment_custom_columns'), 10, 2 );
  }

  public function iamport_script_enqueue($hook) {
    wp_deregister_script( 'iamport-payment-sdk' );
    wp_register_script( 'iamport-payment-sdk', 'https://service.iamport.kr/js/iamport.payment-1.1.5.js', array( 'jquery', 'jquery-ui-dialog' ) );
    wp_enqueue_script( 'iamport-payment-sdk' );
  }

  private function pgForPayment() {
    if ( $this->pg_for_payment ) 	return $this->pg_for_payment;

    return array(
      'card' => 'default',
      'trans' => 'default',
      'vbank' => 'default',
      'phone' => 'default'
    );
  }

  public function ajax_get_order_uid() {
    $order_title 	= $_POST['order_title'];
    $pay_method 	= $_POST['pay_method']; //카카오페이는 kakao로 일단 올라오고, 그 후 front에서 card로 변경되어야 함
    $buyer_name 	= $_POST['buyer_name'];
    $buyer_email 	= $_POST['buyer_email'];
    $buyer_tel 		= $_POST['buyer_tel'];
    $order_amount 	= $_POST['order_amount'];
    $amount_label 	= $_POST['amount_label'];
    $redirect_after	= $_POST['redirect_after'];

    $order_data = array(
      'post_status'		=> 'publish',
      'post_type'			=> 'iamport_payment',
      'post_name'			=> $slug,
      'post_title'		=> $order_title,
      'post_parent'		=> 0,
      'comment_status'	=> 'closed'
    );

    $order_id = wp_insert_post( $order_data );
    
    $order_uid = $this->get_order_uid();
    add_post_meta( $order_id, 'order_uid', $order_uid, true);
    add_post_meta( $order_id, 'pay_method', $pay_method, true);
    add_post_meta( $order_id, 'buyer_name', $buyer_name, true);
    add_post_meta( $order_id, 'buyer_email', $buyer_email, true);
    add_post_meta( $order_id, 'buyer_tel', $buyer_tel, true);
    add_post_meta( $order_id, 'order_amount', $order_amount, true);
    add_post_meta( $order_id, 'amount_label', $amount_label, true);
    add_post_meta( $order_id, 'order_status', 'ready', true);

    $thankyou_url = '';
    $thankyou_page = get_page_by_slug('iamport_thankyou');
    if ( !empty($thankyou_page) ) {
      $thankyou_url = add_query_arg( array(
        'iamport-order-received' => $order_uid,
        'redirect-after' => $redirect_after
      ), get_page_link($thankyou_page[0]->ID) );
    }

    //가상계좌 입금기한
    $iamportSetting = get_option('iamport_setting');
    $vbank_due = null;


    if ( preg_match('/[0-9]+d/', $iamportSetting['vbank_day_limit']) ) {
      $vbank_due = date('Ymd', strtotime(sprintf("+%s days", intval($iamportSetting['vbank_day_limit']))) + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ));
    }
    
    wp_send_json(array('order_id'=>$order_id, 'order_uid'=>$order_uid, 'thankyou_url'=>$thankyou_url, 'vbank_due'=>$vbank_due));
  }

  private function get_order_uid() {
    return uniqid(date('mdis_'));
  } 

  public function iamport_payment_columns($columns) {
    $columns['title_uid'] 			= '주문명<br>주문번호';
    $columns['order_status'] 		= '주문상태';
    $columns['order_paid_amount']	= '요청금액<br>결제금액';
    $columns['pay_method_date'] 	= '결제수단<br>결제시각';
    $columns['buyer_info'] 			= '이름<br>이메일<br>전화번호<br>배송주소';
    
    unset($columns['title']);
    unset($columns['date']);

    return $columns;
  }

  public function iamport_payment_custom_columns( $column, $post_id ) {
    $iamport_order = IamportOrder::find_by_id($post_id);
    if ( $iamport_order == null )	return;

    switch ( $column ) {
      case 'title_uid': 
        echo $iamport_order->get_order_title(). '<br>' . $iamport_order->get_order_uid();
        break;

      case 'order_status':
        echo $iamport_order->get_order_status();
        break;

      case 'pay_method_date':
        echo $iamport_order->get_pay_method() . '<br>' . $iamport_order->get_paid_date();
        break;

      case 'order_paid_amount' : {
        $amount = $iamport_order->get_paid_amount();

        $paidAmount = '';
        if ( isset($amount) ) {
          $paidAmount = '<b>' . number_format($iamport_order->get_paid_amount()) . ' 원</b>';
        }

        echo number_format($iamport_order->get_order_amount()) . ' 원<br>'. $paidAmount ;
        break;
      }

      case 'buyer_info':
        echo $iamport_order->get_buyer_name() . '<br>' .
             $iamport_order->get_buyer_email() . '<br>' .
             $iamport_order->get_buyer_tel() . '<br>' .
             $iamport_order->get_shipping_addr();
        break;
    }
  }

  public function iamport_order_metabox() {
    remove_meta_box( 'submitdiv', 'iamport_payment', 'side' );

    add_meta_box( 'iamport-order-info', '아임포트 결제상세정보', array($this, 'iamport_order_metabox_callback'), 'iamport_payment', 'normal' );
    add_meta_box( 'iamport-order-action', '결제상태 변경', array($this, 'iamport_order_action_metabox_callback'), 'iamport_payment', 'side', 'high' );
    add_meta_box( 'iamport-order-fail-history', '결제 히스토리', array($this, 'iamport_order_history_metabox_callback'), 'iamport_payment', 'side', 'low');
  }

  public function iamport_order_metabox_callback($post) {
    $iamport_order = new IamportOrder($post);
    echo $this->payment_info->get_order_view( $iamport_order->get_order_uid() );
  }

  public function iamport_order_action_metabox_callback($post) {
    wp_nonce_field( 'iamport_metabox_nonce', 'iamport_metabox_nonce' );

    $iamport_order = new IamportOrder($post);
    $order_status = $iamport_order->get_order_status(true);

    echo require_once(dirname(__FILE__).'/../view/admin/edit-order.php');
  }

  public function iamport_order_history_metabox_callback($post) {
    $iamport_order = new IamportOrder($post);
    $history = $iamport_order->get_failed_history();

    echo '<div id="minor-publishing">';
    foreach($history as $h) {
      echo '<div class="misc-pub-section">[' . $h['date'] . '] ' . $h['reason'] . '</div>';
    }
    echo '</div>';
  }

  public function save_iamport_order_metabox($post_id) {
    $iamport_order = IamportOrder::find_by_id($post_id);

    if ( empty($iamport_order) )															return;
    if ( !isset( $_POST['iamport_metabox_nonce'] ) )										return;
    if ( !wp_verify_nonce( $_POST['iamport_metabox_nonce'], 'iamport_metabox_nonce' ) )		return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )									return;

    if ( isset( $_POST['post_type'] ) && 'iamport_payment' == $_POST['post_type'] ) {
      if ( !current_user_can('administrator') && !current_user_can('editor') )		return;
    } else {
      return;
    }

    if ( !isset($_POST['new_iamport_order_status']) )	return;

    $new_iamport_order_status = sanitize_text_field($_POST['new_iamport_order_status']);
    if ( $new_iamport_order_status == 'cancelled' ) {
      $iamport = new Iamport($this->api_key, $this->api_secret);
      $iamport_result = $iamport->cancel(array(
        'merchant_uid' 	=> $iamport_order->get_order_uid(),
        'amount' 		=> $iamport_order->get_paid_amount(),
        'reason' 		=> '워드프레스 관리자 결제취소'
      ));

      $iamport_order->add_failed_history(date_i18n('Y-m-d H:i:s'), $iamport_result->error['message']);

      if ( !$iamport_result->success ) return; // 결제실패가 이루어지지 못했으므로 상태업데이트 해주면 안됨
    }

    $iamport_order->set_order_status($new_iamport_order_status);
  } 

}