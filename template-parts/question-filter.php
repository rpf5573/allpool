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
          <i class="search icon"></i>
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

        $checked = ap_isset_post_value( $filters['did_select']['name'], false ); ?>
        <div class="ui checkbox question-filter__did_select">
          <input type="checkbox" name="<?php echo $filters['did_select']['name']; ?>" class="hidden" <?php if ( $checked ) { echo 'checked'; } ?>>
          <label> <?php echo $filters['did_select']['label']; ?> </label>
        </div> <?php
        
        $checked = ap_isset_post_value( $filters['has_answer']['name'], false ); ?>
        <div class="ui checkbox question-filter__has_answer">
          <input type="checkbox" name="<?php echo $filters['has_answer']['name']; ?>" class="hidden" <?php if ( $checked ) { echo 'checked'; } ?>>
          <label> <?php echo $filters['has_answer']['label']; ?> </label>
        </div>
      </div>

    </div>
    <div class="question-filter__btns">
			<button class="ui primary basic fluid button search-btn">검색</button>
    </div>
  </form>
</div>