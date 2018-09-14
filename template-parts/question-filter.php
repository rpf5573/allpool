<?php
$field_objects = get_field_objects();
$filters = ap_get_filters();
$pages = ap_main_pages();
$archive_url = ap_get_questions_page_url();
?>

<div class="question-filter">
	<form action="<?php echo esc_url( $archive_url ); ?>" method="GET">
		<input type="hidden" name="search" value="true">
    <div class="question-filter__list"> <?php

      $value = ap_isset_post_value( $filters['title']['name'], '' ); ?>
      <div class="l-row">
        <div class="ui fluid input icon question-filter__title">
          <input name="<?php echo $filters['title']['name']; ?>" type="text" placeholder="<?php esc_attr_e( 'Search questions...', 'anspress-question-answer' ); ?>" value="<?php echo $value; ?>" />
        </div>
      </div> <?php

      $selected_list = ap_isset_post_value( $filters['category']['name'], array() ); ?>
      <div class="l-row">
        <div class="question-filter__category">
          <select name="<?php echo $filters['category']['name'] . '[]'; ?>" multiple="multiple"> <?php
            foreach( $filters['category']['choices'] as $term_id => $label ) { ?>
              <option value="<?php echo $term_id; ?>" <?php if ( in_array($term_id, $selected_list) ) { echo 'selected'; } ?>> <?php echo $label; ?> </option> <?php
            } ?>
          </select>
        </div> <?php
        
        $selected_list = ap_isset_post_value( $filters['year']['name'], array() ); ?>
        <div class="question-filter__year">
          <select name="<?php echo $filters['year']['name'] . '[]'; ?>" multiple="multiple"> <?php
            foreach( $filters['year']['choices'] as $option ) { ?>
              <option value="<?php echo $option; ?>" <?php if ( in_array((int)$option, $selected_list) ) { echo 'selected'; } ?>> <?php echo $option; ?> </option> <?php
            } ?>
          </select>
        </div> <?php
        
        $selected_list = ap_isset_post_value( $filters['session']['name'], array() ); ?>
        <div class="question-filter__session">
          <select name="<?php echo $filters['session']['name'] . '[]'; ?>" multiple="multiple"> <?php
            foreach( $filters['session']['choices'] as $option ) { ?>
              <option value="<?php echo $option; ?>" <?php if ( in_array((int)$option, $selected_list) ) { echo 'selected'; } ?>> <?php echo $option; ?> </option> <?php
            } ?>
          </select>
        </div> <?php

        $selected_list = ap_isset_post_value( $filters['did_select']['name'], array() ); ?>
        <div class="question-filter__did_select">
          <select name="<?php echo $filters['did_select']['name'] . '[]'; ?>" multiple="multiple" > <?php
            foreach( $filters['did_select']['choices'] as $key => $value ) { ?>
              <option value="<?php echo $key; ?>" <?php if ( in_array($key, $selected_list) ) { echo 'selected'; } ?>> <?php echo $value; ?> </option> <?php
            } ?>
          </select>
        </div> <?php
        
        $selected_list = ap_isset_post_value( $filters['has_answer']['name'], array() ); ?>
        <div class="question-filter__has_answer">
          <select name="<?php echo $filters['has_answer']['name']. '[]'; ?>" multiple="multiple"> <?php
            foreach( $filters['has_answer']['choices'] as $key => $value ) { ?>
              <option value="<?php echo $key; ?>" <?php if ( in_array($key, $selected_list) ) { echo 'selected'; } ?>> <?php echo $value; ?> </option> <?php
            } ?>
          </select>
        </div>

        <div class="question-filter__btn">
			    <button class="ui fluid button search-btn">
            <i class="search icon"></i>
            질문 검색
          </button>
        </div>
        
      </div>

    </div>
  </form>
</div>