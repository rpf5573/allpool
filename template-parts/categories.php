<?php
$taxonomy = 'question_category';
$terms = get_terms($taxonomy, array(
  "orderby"    => "term_order",
  "hide_empty" => false
));
$term_familly = array();
ap_sort_terms_hierarchically($terms, $term_familly);

// Level 4 or higher shall be deleted
ap_delete_term_children( $term_familly, 4 );


?>

<div class="licence-map"> <?php
  $has_child = true;
  foreach( $term_familly as $term ) {
    if ( ! ap_check_term_has_child( $term ) ) {
      $has_child = false;
    }
  }
  if ( $has_child ) {
    ap_show_licence_tab_tree($term_familly);
  } ?>
</div>