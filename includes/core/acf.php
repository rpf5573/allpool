<?php
class AP_ACF {
  public static function add_question_filter() {
    $year_filter_range = ap_opt('year_filter_range');
    $session_filter_range = ap_opt('session_filter_range');

    $year_filter_choices = array();
    foreach( $year_filter_range as $value ) {
      $year_filter_choices[ "{$value}" ] = $value;
    }

    $session_filter_choices = array();
    foreach( $session_filter_range as $value ) {
      $session_filter_choices[ "{$value}" ] = $value;
    }

    acf_add_local_field_group(array(
      'key' => 'question_filter_group',
      'title' => '질문 필터',
      'fields' => array(
        array(
          'key' => 'year',
          'label' => '년도',
          'name' => 'year',
          'type' => 'select',
          'instructions' => '이 질문과 관련된 문제가 출제된 년도를 선택합니다',
          'required' => 0,
          'conditional_logic' => 0,
          'wrapper' => array(
            'width' => '50',
            'class' => '',
            'id' => '',
          ),
          'choices' => $year_filter_choices,
          'default_value' => array(
          ),
          'allow_null' => 1,
          'multiple' => 0,
          'ui' => 1,
          'ajax' => 0,
          'return_format' => 'value',
          'placeholder' => '',
        ),
        array(
          'key' => 'session',
          'label' => '회차',
          'name' => 'session',
          'type' => 'select',
          'instructions' => '이 질문과 관련된 문제의 회차를 입력합니다',
          'required' => 0,
          'conditional_logic' => 0,
          'wrapper' => array(
            'width' => '50',
            'class' => '',
            'id' => '',
          ),
          'choices' => $session_filter_choices,
          'default_value' => array(
          ),
          'allow_null' => 1,
          'multiple' => 0,
          'ui' => 1,
          'ajax' => 0,
          'return_format' => 'value',
          'placeholder' => '',
        ),
      ),
      'location' => array(
        array(
          array(
            'param' => 'post_type',
            'operator' => '==',
            'value' => 'question',
          ),
        ),
      ),
      'menu_order' => 0,
      'position' => 'normal',
      'style' => 'default',
      'label_placement' => 'top',
      'instruction_placement' => 'label',
      'hide_on_screen' => '',
      'active' => 1,
      'description' => '',
    ));
  }

  public static function add_page_banner() {
    acf_add_local_field_group(array(
      'key' => 'page_banner',
      'title' => '배너',
      'fields' => array(
        array(
          'key' => 'page_banner__main',
          'label' => '메인 타이틀',
          'name' => 'page_banner__main',
          'type' => 'text',
          'instructions' => '베너의 메인 타이틀을 설정합니다',
          'required' => 0,
          'conditional_logic' => 0,
          'wrapper' => array(
            'width' => '',
            'class' => '',
            'id' => '',
          ),
          'default_value' => '',
          'placeholder' => '',
          'prepend' => '',
          'append' => '',
          'maxlength' => '',
        ),
        array(
          'key' => 'page_banner__sub',
          'label' => '서브 타이틀',
          'name' => 'page_banner__sub',
          'type' => 'text',
          'instructions' => '베너의 서브 타이틀을 설정합니다',
          'required' => 0,
          'conditional_logic' => 0,
          'wrapper' => array(
            'width' => '',
            'class' => '',
            'id' => '',
          ),
          'default_value' => '',
          'placeholder' => '',
          'prepend' => '',
          'append' => '',
          'maxlength' => '',
        ),
      ),
      'location' => array(
        array(
          array(
            'param' => 'post_type',
            'operator' => '==',
            'value' => 'page',
          ),
        ),
      ),
      'menu_order' => 0,
      'position' => 'normal',
      'style' => 'default',
      'label_placement' => 'top',
      'instruction_placement' => 'label',
      'hide_on_screen' => '',
      'active' => 1,
      'description' => '',
    ));
  }

  public static function add_expert_categories() {
    acf_add_local_field_group(array(
      'key' => 'group_5b3dbb04c1ce7',
      'title' => '유저 페이지',
      'fields' => array(
        array(
          'key' => 'field_5b3dbb19dd023',
          'label' => '카테고리별 관리자 지정',
          'name' => 'expert_categories',
          'type' => 'taxonomy',
          'instructions' => '',
          'required' => 0,
          'conditional_logic' => 0,
          'wrapper' => array(
            'width' => '',
            'class' => '',
            'id' => '',
          ),
          'taxonomy' => 'question_category',
          'field_type' => 'multi_select',
          'allow_null' => 0,
          'add_term' => 0,
          'save_terms' => 0,
          'load_terms' => 0,
          'return_format' => 'id',
          'multiple' => 0,
        ),
      ),
      'location' => array(
        array(
          array(
            'param' => 'user_role',
            'operator' => '==',
            'value' => 'administrator',
          ),
        ),
        array(
          array(
            'param' => 'user_form',
            'operator' => '==',
            'value' => 'edit',
          ),
        ),
        array(
          array(
            'param' => 'current_user_role',
            'operator' => '==',
            'value' => 'ap_expert',
          ),
        ),
      ),
      'menu_order' => 0,
      'position' => 'normal',
      'style' => 'default',
      'label_placement' => 'top',
      'instruction_placement' => 'label',
      'hide_on_screen' => '',
      'active' => 1,
      'description' => '',
    ));    
  }

  public static function add_question_choices_answer() {
    acf_add_local_field_group(array(
      'key' => 'group_5b3f1f0af36cc',
      'title' => '보기 및 정답',
      'fields' => array(
        array(
          'key' => 'field_5b3f20e2275c2',
          'label' => '보기 및 정답',
          'name' => 'question_choices_answer',
          'type' => 'group',
          'instructions' => '보기와 정답을 입력해 주세요',
          'required' => 0,
          'conditional_logic' => 0,
          'wrapper' => array(
            'width' => '',
            'class' => '',
            'id' => '',
          ),
          'layout' => 'row',
          'sub_fields' => array(
            array(
              'key' => 'field_5b3f2101275c3',
              'label' => '보기',
              'name' => 'choices',
              'type' => 'wysiwyg',
              'instructions' => '',
              'required' => 0,
              'conditional_logic' => 0,
              'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
              ),
              'default_value' => '',
              'tabs' => 'all',
              'toolbar' => 'full',
              'media_upload' => 0,
              'delay' => 0,
            ),
            array(
              'key' => 'field_5b3f2137275c4',
              'label' => '정답',
              'name' => 'answer',
              'type' => 'text',
              'instructions' => '',
              'required' => 0,
              'conditional_logic' => 0,
              'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
              ),
              'default_value' => '',
              'placeholder' => '',
              'prepend' => '',
              'append' => '',
              'maxlength' => '',
            ),
          ),
        ),
      ),
      'location' => array(
        array(
          array(
            'param' => 'post_type',
            'operator' => '==',
            'value' => 'question',
          ),
        ),
      ),
      'menu_order' => 0,
      'position' => 'normal',
      'style' => 'default',
      'label_placement' => 'top',
      'instruction_placement' => 'label',
      'hide_on_screen' => '',
      'active' => 1,
      'description' => '',
    ));
  }

  /**
   * Prevent update wp_postmeta table
   * 
   * ACF automatically insert custom field data to wp_postmeta
   * but, I will user only wp_ap_qameta table for performance
   * so, prevent that
   *
   * @param [type] $value
   * @param [type] $post_id
   * @param [type] $field
   * @return void
   */
  public static function prevent_update_wp_postmeta( $value, $post_id, $field ) {
    if ( isset( $field['parent'] ) && $field['parent'] == 'question_filter_group' ) {
      return;
    }
    return $value;
  }

  public static function load_qameta( $value, $post_id, $field ) {
    if ( isset( $field['parent'] ) && $field['parent'] == 'question_filter_group' ) {
      $meta = ap_get_qameta( $post_id );
      $key = $field['key'];
      if ( $key ) {
        return (int) $meta->$key;
      }
    }
    return $value;
  }

}