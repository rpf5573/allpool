<?php

class IamportPaymentButton {
  private $user_code;
  private $api_key;
  private $api_secret;
  private $configuration;
  
  public $method_names = array(
    'card' 		=> '신용카드',
    'trans' 	=> '실시간계좌이체',
    'vbank' 	=> '가상계좌',
    'phone' 	=> '휴대폰소액결제',
  );
  public $method_name_to_en = array(
    '신용카드' 		=> 'card',
    '실시간계좌이체' 	=> 'trans',
    '가상계좌' 		=> 'vbank',
    '휴대폰소액결제' 	=> 'phone',
  );

  private $uuidList = array(); // front 에서 필요한 것 같아 살려둠
  private $buttonContext = null; //IamportPaymentButton은 객체가 1개 뿐임. [iamport_payment_button_field]는 항상 [iamport_payment_button] 의 child이므로 [iamport_payment_button]를 처리할 때 $this->buttonContext를 생성하고, [iamport_payment_button_field] 를 처리할 때 관련 정보를 append  
  
  public function __construct($user_code, $api_key, $api_secret, $configuration) {
    $this->user_code = $user_code;
    $this->api_key = $api_key;
    $this->api_secret = $api_secret;
    $this->configuration = $configuration;

    $this->hook();
  }

  private function hook() {
    add_shortcode( 'iamport_payment_button', array($this, 'hook_payment_box') );
    add_shortcode( 'iamport_payment_button_field', array($this, 'hook_payment_field') );

    // <head></head> 안에서 해당 action이 trigger된다
    add_action( 'wp_head', array($this, 'enqueue_inline_style') );
    add_action( 'wp_head', array($this, 'enqueue_inline_script') );
  }

  public function enqueue_inline_style() {
    wp_enqueue_style('iamport-payment-css', IamportPaymentPlugin::$URL . 'assets/css/iamport-payment.css', array(), '20180730');
  }

  public function enqueue_inline_script() {
    // wp_register_script('daum-postcode-for-https', 'https://ssl.daumcdn.net/dmaps/map_js_init/postcode.v2.js');
    // wp_enqueue_script('daum-postcode-for-https');
    wp_register_script('iamport-bundle-js', IamportPaymentPlugin::$URL . 'dist/main-babel.js', array('jquery'), '20180419');
  }

  public function hook_payment_box($atts, $content = null) {
    $uuid = uniqid('iamport_dialog_');
    $this->uuidList[] = $uuid;
    $this->buttonContext = array("uuid"=>$uuid); //field 등 정보 저장공간 확보

    $a = shortcode_atts( array(
      'title' 			=> '결제하기',
      'description' 		=> '아래의 정보를 입력 후 결제버튼을 클릭해주세요',
      'pay_method' 		=> 'card',
      'pay_method_list' 	=> 'card,trans,vbank,phone',
      'field_list' 		=> 'name,email,phone',
      'name' 				=> '아임포트 결제하기',
      'amount' 			=> '',
      'style' 			=> 'display:inline-block;padding:6px 12px;color:#fff;background-color:#2c3e50',
      'class' 			=> null,
      'redirect_after' 	=> null
    ), $atts );

    $trimedAttr = $this->trim_iamport_attr($content);
    $content = $trimedAttr['content'];
    $customFields = $trimedAttr['customFields'];

    // 결제자 이름 및 이메일
    $iamport_current_user = wp_get_current_user();
    if ( !empty($iamport_current_user->user_nicename) ) {
      $iamport_buyer_name = $iamport_current_user->user_nicename;
    } 

    if ( !empty($iamport_current_user->user_email) ) {
      $iamport_buyer_email = $iamport_current_user->user_email;
    }
    $fieldLists = array();
    foreach ( array_unique( explode(',', $a['field_list']) ) as $fieldList )  {
      $field = trim($fieldList);
      $regex = "/^(name|email|phone)\((.+)\)$/";

      if ( preg_match($regex, $field, $matches) ) {
        $fieldName = $matches[1];
        $labels = explode("|", $matches[2]);

        $fieldLabel = $fieldPlaceholder = $labels[0];
        if ( count($labels) > 1 ) {
          $fieldPlaceholder = $labels[1];
        }
      } else { //basic format
        $fieldName = $field;
        $fieldLabel = null;
        $fieldPlaceholder = null;
      }


      switch($fieldName) {
        case "name": {
          $fieldLists['name'] = array(
            "required"		=> "true",
            "value" 		=> $iamport_buyer_name,
            "name"			=> "buyer_name",
            "content" 		=> $fieldLabel ? $fieldLabel : "결제자 이름",
            "placeholder" 	=> $fieldPlaceholder ? $fieldPlaceholder : "결제자 이름" 
          );
        }
        break;

        case "email": {
          $fieldLists['email'] = array(
            "required"		=> "true",
            "value"			=> $iamport_buyer_email,
            "name"			=> "buyer_email",
            "content"		=> $fieldLabel ? $fieldLabel : "결제자 이메일",
            "placeholder" 	=> $fieldPlaceholder ? $fieldPlaceholder : "결제자 이메일" 
          );
        }
        break;

        case "phone": {
          $fieldLists['phone'] = array(
            "required"		=> "true",
            "value"			=> null,
            "name"			=> "buyer_tel",
            "content"		=> $fieldLabel ? $fieldLabel : "결제자 전화번호",
            "placeholder" 	=> $fieldPlaceholder ? $fieldPlaceholder : "결제자 전화번호"
          );
        }
        break;
      }
    }

    // 결제금액
    $this->buttonContext[ "amountArr" ] = array();
    $amountList = array_unique( explode(',', $a['amount']) );
    /* ---------- 라벨형 금액 대비 ---------- */
    if ( $amountList[0] != 'variable' ) {
      foreach ( $amountList as $amount ) {
        preg_match_all( '/\((.*?)\)/', $amount, $amountLabel );
        preg_match_all( '/(\d+)/', $amount, $amountValue );

        $label = null;
        if ( !empty($amountLabel) && $amountLabel[1] ) {
          $label = $amountLabel[1][0];
        }
        
        $this->buttonContext[ "amountArr" ][] = array(
          'label' => trim($label),
          'value' => intval($amountValue[0][0])
        );
      }
    }
    
    // 결제수단
    $rawPayMethods = array_unique( explode(',', $a['pay_method_list']) );

    $payMethods = array();
    foreach ( $rawPayMethods as $rawPayMethod ) {
      $payMethods[] = $this->method_names[trim($rawPayMethod)];
    }
    $this->buttonContext[ "payMethods" ] = $payMethods;
    $this->buttonContext[ "orderTitle" ] = $a["name"];
    $this->buttonContext[ "fieldLists" ] = $fieldLists;

    $device = "";
    if ( wp_is_mobile() ) $device = "mobile";

    /* ---------- CONTROLLER ---------- */
    $iamportButtonFields = array(
      'uuidList'    => $this->uuidList,
      'userCode'		=> $this->user_code,
      'configuration'	=> $this->configuration,
      'isLoggedIn'	=> is_user_logged_in(),
      'adminUrl'		=> admin_url( 'admin-ajax.php' ),
      'device'		=> $device,
      'payMethodsToEn'=> $this->method_name_to_en,
    );

    wp_localize_script('iamport-bundle-js', 'iamportButtonContext_'.$uuid, $this->buttonContext);
    wp_localize_script('iamport-bundle-js', 'iamportButtonFields', $iamportButtonFields); //숏코드 개수만큼 반복호출. 매번 overwrite
    wp_enqueue_script('iamport-bundle-js');
    
    /* ---------- VIEW ---------- */
    $iamportPaymentModal = array(
      'attr'			      => $a,
      'hasCustomFields'	=> !empty($buttonContext["customFields"]),
      'uuid'			      => $uuid,
      'methodNames'	    => $this->method_names,
      'regexNewline'	  => '/(\s*?\n\s*?)/',
      'device'		      => $device
    );

    extract($iamportPaymentModal);

    require(dirname(__FILE__).'/../view/modal/payment.php');
    require(dirname(__FILE__).'/../view/modal/result.php');
    require(dirname(__FILE__).'/../view/modal/login.php');
    require(dirname(__FILE__).'/../view/modal/background.php');

    /* ---------- 아임포트 결제버튼 ---------- */
    ob_start();
    ?>
      <a href="#<?=$uuid?>" id="<?=$uuid?>-popup" class="<?=$a['class']?>" style="<?=(empty($a['class']) && !empty($a['style'])) ? $a['style'] : ''?>"><?=$content?></a>
    <?php 

    $this->buttonContext = null;
    return ob_get_clean();
  }

  public function trim_iamport_attr($content) {
    /* ---------- TRIM CONTENT ---------- */
    if ( empty($content) )	$content = '결제하기';
    
    // markup remove
    $content = preg_replace('/<\s*\/?[a-zA-Z0-9]+[^>]*>/s', '', $content);

    // &nbsp; &amp;nbsp; remove
    $content = htmlentities($content, null, 'utf-8');
    $content = preg_replace('/nbsp;|&nbsp;|&amp;/', '', $content);
    $content = html_entity_decode($content);

    $fieldRegex = get_shortcode_regex(array('iamport_payment_button_field'));
    $matchCount = preg_match_all("/$fieldRegex/s", $content, $fieldMatchs);

    $content = trim(preg_replace("/$fieldRegex/s", '', $content));

    $customFields = '';

    /* ---------- TRIM CUSTOMFIELDS ---------- */
    if ( $matchCount > 0 ) {
      $customFields = array();
      foreach ($fieldMatchs[0] as $f) {
        $html = do_shortcode($f);
        error_log($html);
        if ( !empty($html) ) $customFields[] = $html;
      }
    }

    return array(
      'content' 		=> $content,
      'customFields' 	=> $customFields
    );
  }
  
  public function hook_payment_field($atts, $content = null) {
    if ( is_null($this->buttonContext) )	return; //[iamport_payment_button] 없이 [iamport_payment_button_field] 단독으로 사용된 경우. buttonContext 가 없으므로 처리하지 않음

    $a = shortcode_atts( array(
      'type' 			=> 'text',
      'required' 		=> false,
      'options' 		=> array(),
      'content'		=> null,
      'placeholder' 	=> null,
      'data-for'		=> null,
    ), $atts );
    
    if ( empty($content) ) return null;
    else $a['content'] = $content;
    
    if ( !empty($a['options']) ) $a['options'] = explode(',', $a['options']);

    if ( !isset($this->buttonContext["customFields"]) )	$this->buttonContext["customFields"] = array();

    $this->buttonContext["customFields"][] = $a;
  }

}