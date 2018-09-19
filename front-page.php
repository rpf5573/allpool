<?php
get_header(); 
$total_question_count = ap_total_posts_count( 'question' );
$total_answer_count = ap_total_posts_count( 'answer' ); 
$total_price_used_to_open_answers = ap_get_total_price_used_to_open_answers(); ?>

<div class="content-area">
  <main id="main" class="site-main" role="main">
    <div class="front-header">
      <div class="wrapper max-box">
        <div class="front-message">
          <h1 class="front-message__main">
            알풀에서 <span class="orange">답변</span> 달고 <span class="orange">점심값</span> 벌어가세요!
          </h1>
          <h6 class="front-message__sub">
            알풀은 18년간 업계 1위를 지켜온 지식거래 사이트입니다.
          </h6>
        </div>
        <div class="front-category-search"> <?php 
          ap_template_part( 'front', 'category-search' ); ?>
        </div>
        <div class="go-to-bottom-btn-container">
          <span class="go-to-bottom-btn">
            <img src="<?php echo ANSPRESS_URL . 'assets/images/icon_scroll_down.png'; ?>" alt="">
          </span>
        </div>
      </div>
    </div>
    <div class="front-widgets">
      <div class="max-box">
        <ul class="widget-list no-style">
          <li class="widget-list-item">
          <div class="l-left">
              <img src="<?php echo ANSPRESS_URL; ?>assets/images/icon_question_mark.png" alt="">
            </div>
            <div class="l-right">
              <span>등록된 질문</span>
              <span class="blue"><?php echo $total_question_count->publish; ?>개</span>
            </div>
          </li>
          <li class="widget-list-item">
            <div class="l-left">
              <img src="<?php echo ANSPRESS_URL; ?>assets/images/icon_bulb.png" alt="">
            </div>
            <div class="l-right">
              <span>답변한 개수</span>
              <span class="blue"><?php echo $total_answer_count->publish; ?>개</span>
            </div>
          </li>
          <li class="widget-list-item">
            <div class="l-left">
              <img src="<?php echo ANSPRESS_URL; ?>assets/images/icon_won.png" alt="">
            </div>
            <div class="l-right">
              <span>답변에 사용된 금액</span>
              <span class="blue"><?php echo number_format($total_price_used_to_open_answers); ?>원</span>
            </div>
          </li>
        </ul>
      </div>
    </div> <?php
    ap_template_part( 'guide' ); ?>
  </main> <!-- #main -->
</div> <!-- content-area -->

<?php 
get_footer(); ?>