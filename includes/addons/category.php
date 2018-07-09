<?php

class AP_Category {

	public static function register_categories_page() {
		ap_register_page( 'categories', __( 'Categories', 'anspress-question-answer' ), array( __CLASS__, 'categories_page' ) );
	}

	public static function categories_page() {
    ap_template_part( 'categories' );
	}

  public static function register_question_taxonomy() {
   /**
    * Labels for category taxonomy.
    *
    * @var array
    */
   $categories_labels = array(
     'name'               => __( 'Question Categories', 'anspress-question-answer' ),
     'singular_name'      => __( 'Category', 'anspress-question-answer' ),
     'all_items'          => __( 'All Categories', 'anspress-question-answer' ),
     'add_new_item'       => __( 'Add New Category', 'anspress-question-answer' ),
     'edit_item'          => __( 'Edit Category', 'anspress-question-answer' ),
     'new_item'           => __( 'New Category', 'anspress-question-answer' ),
     'view_item'          => __( 'View Category', 'anspress-question-answer' ),
     'search_items'       => __( 'Search Category', 'anspress-question-answer' ),
     'not_found'          => __( 'Nothing Found', 'anspress-question-answer' ),
     'not_found_in_trash' => __( 'Nothing found in Trash', 'anspress-question-answer' ),
     'parent_item_colon'  => '',
   );

   /**
    * FILTER: ap_question_category_labels
    * Filter ic called before registering question_category taxonomy
    */
   $categories_labels = apply_filters( 'ap_question_category_labels', $categories_labels );

   /**
    * Arguments for category taxonomy
    *
    * @var array
    * @since 2.0
    */
   $category_args = array(
     'hierarchical'       => true,
     'labels'             => $categories_labels,
     'rewrite'            => false,
     'publicly_queryable' => true,
   );

   /**
    * Filter is called before registering question_category taxonomy.
    *
    * @param array $category_args Category arguments.
    */
   $category_args = apply_filters( 'ap_question_category_args', $category_args );

   /**
    * Now let WordPress know about our taxonomy
    */
   register_taxonomy( 'question_category', [ 'question' ], $category_args );
  }

  public static function admin_category_menu() {
    add_submenu_page( 'anspress', __( 'Questions Category', 'anspress-question-answer' ), __( 'Category', 'anspress-question-answer' ), 'manage_options', 'edit-tags.php?taxonomy=question_category' );
  }
  
  /**
	 * Filter category permalink.
	 *
	 * @param  string $url      Default taxonomy url.
	 * @param  object $term     WordPress term object.
	 * @param  string $taxonomy Current taxonomy slug.
	 * @return string
	 */
	public static function term_link_filter( $url, $term, $taxonomy ) {
		if ( 'question_category' === $taxonomy ) {
			return ap_question_category_link( $term->term_id );
		}

		return $url;
	}
	
	/**
	 * Modify current page to show category archive.
	 *
	 * @param string $query_var Current page.
	 * @return string
	 * @since 4.1.0
	 */
	public static function ap_current_page( $query_var ) {
		if ( 'categories' === $query_var && 'category' === get_query_var( 'ap_page' ) ) {
			return 'category';
		}

		return $query_var;
  }

}

/**
 * Check if current page is question category.
 *
 * @return boolean
 * @since 4.0.0
 */
function is_question_category() {
	if ( 'category' === ap_current_page() ) {
		return true;
	}

	return false;
}

/**
 * Output question categories
 *
 * @param  array $args Arguments.
 * @return string
 */
function ap_question_categories_html( $args = [] ) {
	$defaults = array(
		'question_id' => get_the_ID(),
		'list'        => false,
		'tag'         => 'span',
		'class'       => 'question-categories',
		'label'       => __( 'Categories', 'categories-for-anspress' ),
		'echo'        => false,
	);

	if ( ! is_array( $args ) ) {
		$defaults['question_id'] = $args;
		$args                    = $defaults;
	} else {
		$args = wp_parse_args( $args, $defaults );
	}

	$cats = get_the_terms( $args['question_id'], 'question_category' );

	if ( $cats ) {
		$o = '';
		if ( $args['list'] ) {
			$o .= '<ul class="' . $args['class'] . '">';
			foreach ( $cats as $c ) {
				$o .= '<li><a href="' . esc_url( get_term_link( $c ) ) . '" data-catid="' . $c->term_id . '" title="' . $c->description . '">' . $c->name . '</a></li>';
			}
			$o .= '</ul>';

		} else {
			$o .= $args['label'];
			$o .= '<' . $args['tag'] . ' class="' . $args['class'] . '">';
			foreach ( $cats as $c ) {
				$o .= '<a data-catid="' . $c->term_id . '" href="' . esc_url( get_term_link( $c ) ) . '" title="' . $c->description . '">' . $c->name . '</a>';
			}
			$o .= '</' . $args['tag'] . '>';
		}

		if ( $args['echo'] ) {
			echo $o; // WPCS: xss okay.
		}

		return $o;
	}

}

/**
 * Recursively sort an array of taxonomy terms hierarchically. Child categories will be
 * placed under a 'children' member of their parent term.
 * @param Array   $cats     taxonomy term objects to sort
 * @param Array   $into     result array to put them in
 * @param integer $parentId the current parent ID to put them in
 */
function ap_sort_terms_hierarchically(Array &$cats, Array &$into, $parentId = 0, $depth_level = -1) {
  $depth_level++;
  foreach ($cats as $i => $cat) {
    if ($cat->parent == $parentId) {
      $into[$cat->term_id] = $cat;
      unset($cats[$i]);
    }
  }
  foreach ($into as $topCat) {
    // count depth level
    $topCat->depth_level = $depth_level;
    $topCat->children = array();
    ap_sort_terms_hierarchically($cats, $topCat->children, $topCat->term_id, $depth_level);

    $topCat->descendant_count = count( $topCat->children );
    foreach( $topCat->children as $child ) {
      if ( isset( $child->descendant_count ) ) {
        $topCat->descendant_count += $child->descendant_count;
      }
    }
  }
}

function ap_sort_terms_hierarchical_inline( Array &$term_familly, Array &$inline ) {
  foreach( $term_familly as $term ) {
    $inline[] = $term;
    if ( count($term->children) > 0 ) {
      ap_sort_terms_hierarchical_inline( $term->children, $inline );
    }
  }
}

function ap_get_hierarchical_inline_terms( $taxonomy = 'question_category' ) {
  $terms = get_terms( array(
    'taxonomy'    => $taxonomy,
    'orderby'     => 'term_order',
    'hide_empty'  => false,
  ) );

  $term_familly = array();
  ap_sort_terms_hierarchically( $terms, $term_familly );
  $inline = array();
  ap_sort_terms_hierarchical_inline( $term_familly, $inline );

  // add spacebar to express power relationship
  $terms = array();
  foreach( $inline as $term ) {
    $level_mark = '';
    for( $i = 0; $i < (int)($term->depth_level); $i++ ){
      // &zwj;
      $level_mark .= '&zwj;&emsp;';
    }
    $term_id = '"'.$term->term_id.'"';
    $terms[$term->term_id] = ($level_mark.$term->name);
  }

  return $terms;
}

/**
 * Delete term children for limit level of term hierarchy
 *
 * @param [type] $term_familly
 * @param [type] $level
 * @return void
 */
function ap_delete_term_children( &$term_familly, $level ) {
  switch($level) {
    case 4:
      foreach( $term_familly as $term ) { // Level 1
        if ( !is_empty($term->children) ) {
          foreach( $term->children as $term_level_2 ) { // Level 2
            if ( !is_empty($term_level_2->children) ) {
              foreach( $term_level_2->children as $term_level_3 ) { // Level 3
                if ( !is_empty($term_level_3->children) ) {
                  $term_level_3->children = false; // Delete Level 4
                }
              }
            }
          }
        }
      }
    case 5:
    break;
  }
}

/**
 * Check the term has grandchild
 *
 * @param [WP_Term] $term
 * @return boolean
 */
function ap_check_term_has_grandchild( &$term ) {
  $has_grandchild = true;
  if ( is_empty($term->children) ) { 
    $has_grandchild = false; 
  }
  foreach( $term->children as $child_term ) {
    if ( is_empty($child_term->children) ) { 
      $has_grandchild = false;
    }
  }
  return $has_grandchild;
}

/**
 * Check the term has child
 *
 * @param [type] $term
 * @return void
 */
function ap_check_term_has_child( &$term ) {
  $has_children = true;
  if ( is_empty($term->children) ) {
    $has_children = false;
  }
  return $has_children;
}

/**
 * Show licence tab tree of terms
 *
 * @param [type] $terms
 * @param int $level deep level
 * @return void
 */
function ap_show_licence_tab_tree($terms, $level = 0) {
  $level++;
  $first_pointing = true; ?>
  <div class="ui pointing menu <?php if( $level > 1 ){ echo 'secondary '; } echo "level-{$level}"; ?>"> <?php
    foreach( $terms as $term ) { $i = -1; $i++; ?>
      <a class="item <?php if ($first_pointing) { echo 'active'; } ?>" data-tab="<?php echo $term->name; ?>"> <?php echo $term->name; ?> </a><?php
      $first_pointing = false;
    } ?>
  </div> <?php
  $first_pointing = true;
  foreach( $terms as $term ) { ?>
    <div class="ui tab segment <?php if ($first_pointing) { echo 'active'; } ?>" data-tab="<?php echo $term->name; ?>"> <?php
      $first_pointing = false;
      if ( ap_check_term_has_grandchild($term) ) {
        ap_show_licence_tab_tree( $term->children , $level);
      } else { ?>
        <ul class="no-style subject-list"> <?php
          foreach( $term->children as $child_term ) { ?>
            <li class="subject-item">
              <a href="<?php echo get_term_link( $child_term ); ?>"> <?php echo $child_term->name; ?> </a>
            </li> <?php
          } ?>
        </ul> <?php
      } ?>
    </div> <?php
  }
}