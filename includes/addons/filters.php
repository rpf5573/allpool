<?php

class AP_Filters {
  /**
	 * Filter main questions query args. Modify and add category args.
	 *
	 * @param  array $args Questions args.
	 * @return array
	 */
	public static function category_filter( $args ) {
    $name_list = ap_opt('filter_name_list');
		$selected_list = ap_isset_post_value( $name_list['category'], false );
		if ( $selected_list ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'question_category',
				'field'    => 'term_id',
				'terms'    => explode( ',', sanitize_comma_delimited( $selected_list ) ),
      );
		}

		return $args;
  }
  
  public static function meta_filter( $sql ) {
    $name_list = ap_opt('filter_name_list');

    $year = ap_isset_post_value( $name_list['year'], array() );
    if ( ! empty( $year ) ) {
      $year = sanitize_comma_delimited( $year );
      $sql['where'] .= " AND qameta.year IN ({$year})";
    }

    $session = ap_isset_post_value( $name_list['session'], array() );
    if ( ! empty( $session ) ) {
      $session = sanitize_comma_delimited( $session );
      $sql['where'] .= " AND qameta.session IN ({$session})";
    }

    $did_select = ap_isset_post_value( $name_list['did_select'], false );
    if ( $did_select ) {
      $sql['where'] .= " AND qameta.selected_id IS NOT NULL";
    }

    $has_answer = ap_isset_post_value( $name_list['has_answer'], false );
    if ( $has_answer ) {
      $sql['where'] .= " AND qameta.answers > 0";
    }

    return $sql;
  }

  public static function form_fields( $form ) {
    $filters = ap_get_filters();

    // add category field
    $form['fields']['category'] = array(
      'label'   => '카테고리',
      'type'    => 'select',
      'desc'    => '',
      'options' => $filters['category']['choices']
    );

    // add question meta fields
    $form['fields'][$filters['year']['name']] = array(
      'label'    => '년도',
      'type'     => 'select',
      'options'  => $filters['year']['choices']
    );
    $form['fields'][$filters['session']['name']] = array(
      'label'    => '회차',
      'type'     => 'select',
      'options'  => $filters['session']['choices']
    );

    // set field orders
    $form['fields']['post_title']['order'] = 1;
    $form['fields']['category']['order'] = 2;
    $form['fields'][$filters['year']['name']]['order'] = 3;
    $form['fields'][$filters['session']['name']]['order'] = 4;
    $form['fields']['post_content']['order'] = 5;

    // set value
    $editing_id = ap_sanitize_unslash( 'id', 'r' );
    if ( ! empty( $editing_id ) ) {
      $categories = get_the_terms( $editing_id, 'question_category' );
      
			if ( $categories ) {
				$form['fields']['category']['value'] = $categories[0]->term_id;
			}

      $qameta = ap_get_qameta( $editing_id );
      if ( $qameta->year > 0 ) {
        $form['fields'][$filters['year']['name']]['value'] = $qameta->year;
      }
      if ( $qameta->session > 0 ) {
        $form['fields'][$filters['session']['name']]['value'] = $qameta->session;
      }
    }

    // remove tag fields
    if ( isset( $form['fields']['tag'] ) ) {
      unset($form['fields']['tag']);
    }

    return $form;
  }

  public static function show_only_categories_of_expert( $form ) {
    if ( isset( $form['fields']['category'] ) ) {
      $user_id = get_current_user_id();
      if ( ap_is_expert( $user_id ) ) {
        $options = array();
        $term_ids = ap_get_expert_categories( $user_id );
        if ( ! empty( $term_ids ) ) {
          foreach( $term_ids as $term_id ) {
            $term = get_term_by( 'id', $term_id, 'question_category' );
            $options[$term->term_id] = $term->name;
          }
        }
        $form['fields']['category']['options'] = $options;
      }
    }
    return $form;
  }

  public static function ap_display_question_metas( $metas, $question_id ) {
    global $post;

    if ( ap_post_have_terms( $question_id ) ) {
			$metas['categories'] = ap_question_categories_html( array( 'label' => '<i class="apicon-category"></i>' ) );
    }
    
    if ( ! is_null( $post->year ) && $post->year > 0 ) {
      $metas['year'] = '<i class="fas fa-calendar-check"></i><i>' . $post->year . '년도</i>';
    }
    if ( ! is_null( $post->session ) && $post->session > 0 ) {
      $metas['session'] = '<i class="fas fa-list-alt"></i><i>' . $post->session . '회차</i>';
    }

		return $metas;
  }

  /**
	 * Things to do after creating a question.
	 *
	 * @param   integer $post_id    Questions ID.
	 * @param   object  $post       Question post object.
	 * @return  void
	 * @since   1.0
	 */
	public static function save_category( $post_id, $post ) {
		$values = anspress()->get_form( 'question' )->get_values();

		if ( isset( $values['category']['value'] ) ) {
			wp_set_post_terms( $post_id, $values['category']['value'], 'question_category' );
		}
  }

  public static function save_meta( $qameta, $post, $updated ) {
    // insert question meta at admin
    $acf = ap_isset_post_value( 'acf', false );
    if ( $acf ) {
      if ( isset( $acf['year'] ) ) {
        $qameta['year'] = (int) $acf['year'];
      }
      if ( isset( $acf['session'] ) ) {
        $qameta['session'] = (int) $acf['session'];
      }
    }

    // insert question meta at front
    $name_list = ap_opt( 'filter_name_list' );
    $values = anspress()->get_form( 'question' )->get_values();
    if ( isset( $values[$name_list['year']] ) && $values[$name_list['year']]['value'] ) {
      $qameta['year'] = $values[$name_list['year']]['value'];
    }
    if ( isset( $values[$name_list['session']] ) && $values[$name_list['session']]['value'] ) {
      $qameta['session'] = $values[$name_list['session']]['value'];
    }

    return $qameta;
  }
  
}

function ap_get_filters() {
  $name_list = ap_opt( 'filter_name_list' );
  $filters = array(
    'title' => ap_get_title_filter( $name_list['title'] ),
    'category' => ap_get_category_filter( $name_list['category'] ),
    'year' => ap_get_year_filter( $name_list['year'] ),
    'session' => ap_get_session_filter( $name_list['session'] ),
    'did_select' => ap_get_did_select_filter( $name_list['did_select'] ),
    'has_answer' => ap_get_has_answer_filter( $name_list['has_answer'] ),
  );
  return $filters;
}

function ap_get_title_filter( $name = null ) {
  if ( is_null( $name ) ) {
    $name_list = ap_opt( 'filter_name_list' );
    $name = $name_list['title'];
  }
  $filter = array(
    'title'   => __('카테고리', 'anspress-question-answer'),
    'type'    => 'select',
    'name'    => $name,
  );
  return $filter;
}

function ap_get_category_filter( $name = null ) {
  if ( is_null( $name ) ) {
    $name_list = ap_opt( 'filter_name_list' );
    $name = $name_list['category'];
  }
  $choices = ap_get_hierarchical_inline_terms();
  $filter = array(
    'title'             => __('카테고리', 'anspress-question-answer'),
    'type'              => 'select',
    'name'              => $name,
    'choices'           => (empty( $choices ) ? array() : $choices ),
  );
  return $filter;
}

function ap_get_year_filter( $name = null ) {
  if ( is_null( $name ) ) {
    $name_list = ap_opt( 'filter_name_list' );
    $name = $name_list['year'];
  }
  $range = ap_opt('year_filter_range');
  $choices = array();
  foreach( $range as $value ) {
    $choices[ "{$value}" ] = $value;
  }

  $filter = array(
    'title'             => __('년도', 'anspress-question-answer'),
    'type'              => 'select',
    'name'              => $name,
    'choices'           => $choices,
  );
  return $filter;
}

function ap_get_session_filter( $name = null ) {
  if ( is_null( $name ) ) {
    $name_list = ap_opt( 'filter_name_list' );
    $name = $name_list['session'];
  }
  $range = ap_opt('session_filter_range');
  $choices = array();
  foreach( $range as $value ) {
    $choices[ "{$value}" ] = $value;
  }
  $filter = array(
    'title'           => __('회차', 'anspress-question-answer'),
    'type'            => 'select',
    'name'            => $name,
    'choices'         => $choices,
  );
  return $filter;
}

function ap_get_did_select_filter( $name = null ) {
  if ( is_null( $name ) ) {
    $name_list = ap_opt( 'filter_name_list' );
    $name = $name_list['did_select'];
  }
  $filter = array(
    'label'     => __('채택여부', 'anspress-question-answer'),
    'type'      => 'checkbox',
    'name'      => $name,
  );
  return $filter;
}

function ap_get_has_answer_filter( $name = null ) {
  if ( is_null( $name ) ) {
    $name_list = ap_opt( 'filter_name_list' );
    $name = $name_list['has_answer'];
  }
  $filter = array(
    'label' => __('답변여부', 'anspress-question-answer'),
    'type'  => 'checkbox',
    'name'  => $name,
  );
  return $filter;
}