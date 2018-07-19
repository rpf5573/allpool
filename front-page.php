<?php
get_header(); ?>

<style>
  html, body {
    width: 100%;
    height: 100%;
  }
</style>

<div class="content-area" style="display:none;">
  <main id="main" class="site-main" role="main">
    <div class="m-center-box"> <?php
      ap_template_part( 'front', 'message' );
      ap_template_part( 'front', 'category-search' ); ?>
    </div>
  </main> <!-- #main -->
</div> <!-- content-area -->

<button type="button" id="imp_card"> 신용카드 결제 </button>
<button type="button" id="imp_phone"> 핸드폰 결제 </button>
<button type="button" id="imp_vbank"> 가상계좌 </button>
<script src="https://cdn.iamport.kr/js/iamport.payment-1.1.5.js" type="text/javascript"></script>

<script>
  jQuery('#imp_card').on( 'click', function(){
    IMP.init( 'imp07107339' );
    IMP.request_pay({
      pg : 'html5_inicis',
      pay_method : 'card',
      merchant_uid : 'merchant_' + new Date().getTime(),
      name : '결제테스트',
      amount : 14000,
      buyer_email : 'rpf5573@gmail.com',
      buyer_name : '윤병인',
      buyer_tel : '010-9619-0918',
      buyer_addr : '서울특별시 강남구 삼성동',
      buyer_postcode : '123-456'
    }, function(rsp){
      if ( rsp.success ) {
        var msg = '결제가 완료되었습니다';
        msg += '고유ID : ' + rsp.imp_uid;
        msg += '상점 거래ID' + rsp.merchant_uid;
        msg += '결제 금액 : ' + rsp.paid_amount;
        msg += '카드 승인번호 : ' + rsp.apply_num;
      } else {
        var msg = '결제에 실패하였습니다.';
        msg += '에러내용 : ' + rsp.error_msg;
      }

      alert( msg );
    });
  } );
  jQuery('#imp_vbank').on( 'click', function(){
    IMP.init( 'imp07107339' );
    IMP.request_pay({
      pg : 'html5_inicis',
      pay_method : 'vbank',
      merchant_uid : 'merchant_' + new Date().getTime(),
      name : '결제테스트',
      amount : 12000,
      buyer_email : 'rpf5573@gmail.com',
      buyer_name : '윤병인',
      buyer_tel : '010-9619-0918',
      buyer_addr : '서울특별시 강남구 삼성동',
      buyer_postcode : '123-456'
    }, function(rsp){
      if ( rsp.success ) {
        console.dir( rsp );
        var msg = '결제가 완료되었습니다';
        msg += '고유ID : ' + rsp.imp_uid;
        msg += '상점 거래ID' + rsp.merchant_uid;
        msg += '결제 금액 : ' + rsp.paid_amount;
        msg += '카드 승인번호 : ' + rsp.apply_num;
      } else {
        var msg = '결제에 실패하였습니다.';
        msg += '에러내용 : ' + rsp.error_msg;
      }

      alert( msg );
    });
  } );
  jQuery('#imp_phone').on( 'click', function(){
    IMP.init( 'imp07107339' );
    IMP.request_pay({
      pg : 'html5_inicis',
      pay_method : 'phone',
      merchant_uid : 'merchant_' + new Date().getTime(),
      name : '결제테스트',
      amount : 1000,
      buyer_email : 'rpf5573@gmail.com',
      buyer_name : '윤병인',
      buyer_tel : '010-9619-0918',
      buyer_addr : '서울특별시 강남구 삼성동',
      buyer_postcode : '123-456'
    }, function(rsp){
      if ( rsp.success ) {
        console.dir( rsp );
        var msg = '결제가 완료되었습니다';
        msg += '고유ID : ' + rsp.imp_uid;
        msg += '상점 거래ID' + rsp.merchant_uid;
        msg += '결제 금액 : ' + rsp.paid_amount;
        msg += '카드 승인번호 : ' + rsp.apply_num;
      } else {
        var msg = '결제에 실패하였습니다.';
        msg += '에러내용 : ' + rsp.error_msg;
      }

      alert( msg );
    });
  } );
</script>

<?php 
get_footer(); ?>