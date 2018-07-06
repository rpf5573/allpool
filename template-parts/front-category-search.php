<?php
$category_filter = ap_get_category_filter();
$url = ap_get_questions_page_url(); ?>
<div class="front-category-search">
  <form role="search" action="<?php echo $url; ?>" method="get">
    <select class="ui fluid search dropdown" name="<?php echo $category_filter['name'] . '[]'; ?>" multiple="">
      <option value="" disabled> 카테고리 리스트 </option> 
      <?php
      foreach( $category_filter['choices'] as $term_id => $label ) { ?>
        <option value="<?php echo $term_id; ?>"> <?php echo $label; ?> </option> <?php
      } ?>
    </select>
    <button type="submit" class="ui primary basic button"> <span>검색</span> <i class="fas fa-search"></i> </button>
  </form>
</div>