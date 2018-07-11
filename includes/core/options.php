<?php

/**
 * Default options for AnsPress
 *
 * @return array
 * @since 2.0.1
 */
function ap_default_options() {
	$cache = wp_cache_get( 'ap_default_options', 'ap' );

	if ( false !== $cache ) {
		return $cache;
	}

	$defaults = array(
		'question_page_slug'            => 'question',
		'question_per_page'             => '20',
		'question_order_by'             => 'active',
		'answers_sort'                  => 'active',
		'answers_per_page'              => '5',
		'max_upload_size'               => 1000000,
		'read_question_per'             => 'anyone',
		'read_answer_per'               => 'logged_in',
		'post_question_per'             => 'logged_in',
		'post_answer_per'               => 'logged_in',
		'close_selected'                => true,
		'disallow_op_to_answer'         => true,
		'multiple_answers'              => false,
		'uploads_per_post'              => 4,
		'minimum_qtitle_length'					=> 1,
		'minimum_question_length'       => 1,
		'minimum_ans_length'						=> 1,
		'avatar_size_list'              => 45,
		'allow_upload'                  => true,
		'avatar_size_qquestion'         => 50,
		'author_credits'								=> false,
	);

	// Set custom options. Because I can't insert by using filter
	$current_year = (int) date("Y");
	$year_filter_range = array();
	for( $i = 2011; $i <= $current_year; $i++ ) {
		$year_filter_range[] = $i;
	}
	$session_filter_range = array(1, 2, 3, 4);
	
	$defaults['year_filter_range'] = $year_filter_range;
	$defaults['session_filter_range'] = $session_filter_range;

	$filter_name_list = array(
    'title'         =>  'ap_s',
    'category'		  =>	'ap_category',
    'year'				  =>	'ap_year',
    'session'			  =>	'ap_session',
    'did_select'		=>	'ap_did_select',
    'has_answer'		=>	'ap_has_answer'
	);
	
	$defaults['filter_name_list'] = $filter_name_list;

	/**
	 * Filter to be used by extensions for including their default options.
	 *
	 * @param array $defaults Default options.
	 * @since 0.1
	 */
	$defaults = apply_filters( 'ap_default_options', $defaults );

	wp_cache_set( 'ap_default_options', $defaults, 'ap' );

	return $defaults;
}

/**
 * Add default AnsPress options.
 *
 * @param array $defaults Default options to append.
 * @since 4.0.0
 */
function ap_add_default_options( $defaults ) {
	$old_default = ap_default_options();

	// Delete existing cache.
	wp_cache_delete( 'ap_default_options', 'ap' );
	wp_cache_delete( 'anspress_opt', 'ap' );

	$new_default = $old_default + (array) $defaults;
	wp_cache_set( 'ap_default_options', $new_default, 'ap' );
}