<?php

/*  Question
/* --------------------------------------------------- */

function ap_get_questions( $args = [] ) {

  if ( is_front_page() ) {
    $paged = ( isset( $_GET['ap_paged'] ) ) ? (int) $_GET['ap_paged'] : 1;
  } else {
    $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
  }

  if ( ! isset( $args['post_parent'] ) ) {
    $args['post_parent'] = get_query_var( 'parent' ) ? get_query_var( 'parent' ) : false;
  }

  $args = wp_parse_args(
    $args, array(
      'showposts' => ap_opt( 'question_per_page' ),
      'paged'     => $paged,
      'ap_query'  => 'featured_post',
    )
  );

  return new Question_Query( $args );
}

/**
 * Output questions page pagination.
 *
 * @param integer|false $paged Current paged value.
 *
 * @return void
 * @since 4.1.0 Added new argument `$paged`.
 */
function ap_questions_the_pagination( $paged = false ) {
	if ( is_front_page() ) {
		$paged = get_query_var( 'page' );
	} elseif ( get_query_var( 'ap_paged' ) ) {
		$paged = get_query_var( 'ap_paged' );
	} elseif ( get_query_var( 'paged' ) ) {
		$paged = get_query_var( 'paged' );
	}

	ap_pagination( $paged, anspress()->questions->max_num_pages );
}

/**
 * Check if there is post in loop
 *
 * @return boolean
 */
function ap_have_questions() {
	if ( anspress()->questions ) {
		return anspress()->questions->have_posts();
	}
}

/**
 * Question meta to display.
 *
 * @param false|integer $question_id question id.
 * @since unknown
 * @since 4.1.2 Use @see ap_recent_activity() for showing activity.
 * @since 4.1.8 Show short views count.
 */
function ap_question_metas( $question_id = false ) {
	if ( false === $question_id ) {
		$question_id = get_the_ID();
	}

	$metas = array();

	if ( ap_have_answer_selected() ) {
		$metas['solved'] = '<i class="apicon-check"></i><i>' . __( 'Solved', 'anspress-question-answer' ) . '</i>';
	}

	$view_count     = ap_get_post_field( 'views' );
	$metas['views'] = '<i class="apicon-eye"></i><i>' . sprintf( __( '%s views', 'anspress-question-answer' ),  ap_short_num( $view_count ) ) . '</i>';

	if ( is_question() ) {
		$last_active     = ap_get_last_active( get_question_id() );
		$metas['active'] = '<i class="apicon-pulse"></i><i><time class="published updated" itemprop="dateModified" datetime="' . mysql2date( 'c', $last_active ) . '">' . $last_active . '</time></i>';
	}

	if ( ap_post_have_terms( $question_id ) ) {
		$metas['categories'] = ap_question_categories_html( array( 'label' => '<i class="apicon-category"></i>' ) );
	}

	/**
   * Used to filter question display meta.
	 *
	 * @param array $metas
	 */
	$metas = apply_filters( 'ap_display_question_metas', $metas, $question_id );

	$output = '';
	if ( ! empty( $metas ) && is_array( $metas ) ) {
		foreach ( $metas as $meta => $display ) {
			$output .= "<span class='ap-display-meta-item {$meta}'>{$display}</span>";
		}
	}

	echo $output; // xss ok.
}

/**
 * Set current question in loop.
 *
 * @return Object
 */
function ap_the_question() {
	if ( anspress()->questions ) {
		return anspress()->questions->the_question();
	}
}

/**
 * Echo post status of a question.
 *
 * @param  mixed $_post Post ID, Object or null.
 */
function ap_question_status( $_post = null ) {
	$_post = ap_get_post( $_post );

	if ( 'publish' === $_post->post_status ) {
		return;
	}

	$status_obj = get_post_status_object( $_post->post_status );
	echo '<span class="ap-post-status ' . esc_attr( $_post->post_status ) . '">' . esc_attr( $status_obj->label ) . '</span>';
}

/**
 * Reset original question query.
 *
 * @return boolean
 * @since unknown
 * @since 4.1.0 Check if global `$questions` exists.
 */
function ap_reset_question_query() {
	if ( anspress()->questions ) {
		return anspress()->questions->reset_questions_data();
	}
}

function ap_get_question_price( $question_id = null ) {
	$_post = ap_get_post( $question_id );
	return $_post->price;
}


/*  Answer
/* --------------------------------------------------- */

/**
 * Return total published answer count.
 *
 * @param  mixed $_post Post ID, Object or null.
 * @return integer
 */
function ap_get_answers_count( $_post = null ) {
	$_post = ap_get_post( $_post );
	
	return $_post->answers;
}

/**
 * Check if question have answer selected.
 *
 * @param  mixed $question Post object or ID.
 * @return boolean
 */
function ap_have_answer_selected( $question = null ) {
	$question = ap_get_post( $question );

	return ! empty( $question->selected_id );
}

/**
 * Return the ID of selected answer from a question.
 *
 * @param object|null|integer $_post Post object, ID or null.
 * @return integer
 */
function ap_selected_answer( $_post = null ) {
	$_post = ap_get_post( $_post );

	if ( ! $_post ) {
		return false;
	}

	return $_post->selected_id;
}

/**
 * Output answers of current question.
 *
 * @since 2.1
 * @since 4.1.0 Removed calling function @see `ap_reset_question_query`.
 */
function ap_answers() {
	global $answers;
	$answers = ap_get_answers();
	
	ap_template_part( 'answers' );
	ap_reset_question_query();
}

/**
 * Echo total votes count of a post.
 *
 * @param  mixed $_post Post ID, Object or null.
 */
function ap_answers_count( $_post = null ) {
	echo ap_get_answers_count( $_post ); // xss ok.
}

/**
 * Display answers of a question
 *
 * @param  array $args Answers query arguments.
 * @return Answers_Query
 * @since  2.0
 */
function ap_get_answers( $args = array() ) { 

	if ( empty( $args['question_id'] ) ) {
		$args['question_id'] = get_question_id();
	}

	if ( ! isset( $args['ap_order_by'] ) ) {
		$args['ap_order_by'] = isset( $_GET['order_by'] ) ? ap_sanitize_unslash( 'order_by', 'g' ) : ap_opt( 'answers_sort' );
	}

	$answer_query = new Answers_Query( $args );

	return $answer_query;
}

/**
 * Check if there are posts in the loop
 *
 * @return boolean
 */
function ap_have_answers() {
	global $answers;

	if ( $answers ) {
		return $answers->have_posts();
	}
}

function ap_the_answer() {
	global $answers;
	if ( $answers ) {
		return $answers->the_post();
	}
}

/**
 * Return numbers of published answers.
 *
 * @param  integer $question_id Question ID.
 * @return integer
 */
function ap_count_published_answers( $question_id ) {
	global $wpdb;
	$query = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts where post_parent = %d AND post_status = %s AND post_type = %s", $question_id, 'publish', 'answer' );
	$count = $wpdb->get_var( $query );
	return $count;
}

function ap_total_answers_found() {
	global $answers;
	
	return $answers->found_posts;
}

/**
 * Output answers pagination. Should be used inside a loop.
 *
 * @return void.
 */
function ap_answers_the_pagination() {
	global $answers;
	$paged = ( get_query_var( 'ap_paged' ) ) ? get_query_var( 'ap_paged' ) : 1;
	ap_pagination( $paged, $answers->max_num_pages, '?ap_paged=%#%', get_permalink( get_question_id() ) . 'page/%#%/' );
}

/**
 * Echo post status of a answer.
 *
 * @param  object|integer|null $_post Post ID, Object or null.
 */
function ap_answer_status( $_post = null ) {
	ap_question_status( $_post );
}

/*  Uncategorized
/* --------------------------------------------------- */

/**
 * Return count of net vote of a question.
 *
 * @param  mixed $_post Post ID, Object or null.
 * @return integer
 */
function ap_get_votes_net( $_post = null ) {
	$_post = ap_get_post( $_post );
	return $_post->votes_net;
}

/**
 * Get posts with qameta fields.
 *
 * @param  mixed $post Post object.
 * @return object
 */
function ap_get_post( $post = null ) {

	if ( empty( $post ) && isset( $GLOBALS['post'] ) ) {
		$post = $GLOBALS['post']; // override ok.
	}

	if ( $post instanceof WP_Post || is_object( $post ) ) {
		$_post = $post;
	} else {
		$_post = WP_Post::get_instance( $post );
	}

	if ( $_post && ! isset( $_post->ap_qameta_wrapped ) ) {
		$_post = ap_append_qameta( $_post );
	}

	return $_post;
}

/**
 * Return question author avatar.
 *
 * @param  integer $size Avatar size.
 * @param  mixed   $_post Post.
 * @return string
 */
function ap_get_author_avatar( $size = 45, $_post = null ) {
	$_post = ap_get_post( $_post );

	if ( ! $_post ) {
		return;
	}

	$author = $_post->post_author;

	return get_avatar( $author, $size );
}

/**
 * Echo question author avatar.
 *
 * @param  integer $size Avatar size.
 * @param  mixed   $_post Post.
 */
function ap_author_avatar( $size = 45, $_post = null ) {
	echo ap_get_author_avatar( $size, $_post ); // xss ok.
}

/**
 * Check if question or answer have attachemnts.
 *
 * @param  mixed $_post Post.
 * @return boolean
 */
function ap_have_attach( $_post = null ) {
	$_post = ap_get_post( $_post );
	if ( ! empty( $_post->attach ) ) {
		return true;
	}
	return false;
}

/**
 * Return link of user profile page
 *
 * @return string
 */
function ap_get_profile_link() {
	global $post;

	if ( ! $post ) {
		return false;
	}

	return ap_user_link( $post->post_author );
}

/**
 * Echo user profile link
 */
function ap_profile_link() {
	echo ap_get_profile_link(); // xss ok.
}

/**
 * Get last active time in human readable format.
 *
 * @param  mixed $post_id Post ID/Object.
 * @return string
 * @since  2.4.8 Convert mysql date to GMT.
 */
function ap_get_last_active( $post_id = null ) {
	$p    = ap_get_post( $post_id );
	$date = ! empty( $p->last_updated ) ? $p->last_updated : $p->post_modified_gmt;
	return ap_human_time( get_gmt_from_date( $date ), false );
}

/**
 * Check if post have terms of a taxonomy.
 *
 * @param  boolean|integer $post_id  Post ID.
 * @param  string          $taxonomy Taxonomy name.
 * @return boolean
 */
function ap_post_have_terms( $post_id = false, $taxonomy = 'question_category' ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$terms = get_the_terms( $post_id, $taxonomy );

	if ( ! empty( $terms ) ) {
		return true;
	}

	return false;
}

/**
 * Return post time.
 *
 * @param  mixed  $_post   Post ID, Object or null.
 * @param  string $format Date format.
 * @return String
 */
function ap_get_time( $_post = null, $format = '' ) {
	$_post = ap_get_post( $_post );

	if ( ! $_post ) {
		return;
	}

	return get_post_time( $format, true, $_post->ID, true );
}

/**
 * Echo count of net vote of a question.
 *
 * @param  mixed $_post Post ID, Object or null.
 */
function ap_votes_net( $_post = null ) {
	echo ap_get_votes_net( $_post ); // xss ok.
}

/**
 * Check if current answer is selected as a best
 *
 * @param mixed $_post Post.
 * @return boolean
 * @since 2.1
 */
function ap_is_selected( $_post = null ) {
	$_post = ap_get_post( $_post );

	if ( ! $_post ) {
		return false;
	}

	return (bool) $_post->selected;
}

/**
 * Echo recent activity of a post.
 */
function ap_recent_post_activity() {
	echo ap_get_recent_post_activity(); // xss ok.
}

/**
 * Get recent activity of a post.
 *
 * @param  mixed $_post Post ID, Object or null.
 * @return string
 */
function ap_get_recent_post_activity( $_post = null ) {
	$_post = ap_get_post( $_post );
	return ap_latest_post_activity_html( $_post->ID );
}

/**
 * Get latest activity of question or answer.
 *
 * @param  mixed           $post_id Question or answer ID.
 * @param  integer|boolean $answer_activities Show answers activities as well.
 * @return string
 */
function ap_latest_post_activity_html( $post_id = false, $answer_activities = false ) {
	if ( false === $post_id ) {
		$post_id = get_the_ID();
	}

	$_post    = ap_get_post( $post_id );
	$activity = $_post->activities;

	if ( false !== $answer_activities && ! empty( $_post->activities['child'] ) ) {
		$activity = $_post->activities['child'];
	}

	if ( ! empty( $activity ) && ! empty( $activity['date'] ) ) {
		$activity['date'] = get_gmt_from_date( $activity['date'] );
	}

	if ( false === $answer_activities && ( ! isset( $activity['type'] ) || in_array( $activity['type'], [ 'new_answer', 'new_question' ], true ) ) ) {
		return;
	}

	$html = '';

	if ( $activity ) {
		$user_id       = ! empty( $activity['user_id'] ) ? $activity['user_id'] : 0;
		$activity_type = ! empty( $activity['type'] ) ? $activity['type'] : '';
		$html         .= '<span class="ap-post-history">';
		$html         .= '<a href="' . ap_user_link( $user_id ) . '" itemprop="author" itemscope itemtype="http://schema.org/Person"><span itemprop="name">' . ap_user_display_name( $user_id ) . '</span></a>';
		$html         .= ' ' . ap_activity_short_title( $activity_type );

		if ( ! empty( $activity['date'] ) ) {
			$html .= ' <a href="' . get_permalink( $_post ) . '">';
			$html .= '<time itemprop="dateModified" datetime="' . mysql2date( 'c', $activity['date'] ) . '">' . ap_human_time( $activity['date'], false ) . '</time>';
			$html .= '</a>';
		}

		$html .= '</span>';
	}

	if ( $html ) {
		return apply_filters( 'ap_latest_post_activity_html', $html );
	}

	return false;
}

// the reason why we should use this is that wp_ap_qameta -> answer has no year & session
function ap_get_question_ids( $year, $session, $term_family ) {
	global $wpdb;
	$prefix = $wpdb->prefix;

	// 또 조인하는게 빠를지, 그냥 가져오는게 빠를지 잘 모르겠다...
	$sql = "SELECT `ID` 
					FROM {$prefix}posts as posts
					LEFT JOIN {$prefix}term_relationships as term_relationships
					ON ( posts.ID = term_relationships.object_id )
					LEFT JOIN {$prefix}ap_qameta as qameta
					ON posts.ID = qameta.post_id						
					WHERE ( term_relationships.term_taxonomy_id IN ({$term_family}) )
					AND posts.post_type = 'question'
					AND `year` = {$year}
					AND `session` = {$session}
					AND posts.post_status = 'publish' ";

	$ids = array();
	$results = $wpdb->get_results( $sql );
	foreach( $results as $result ) {
		$ids[] = $result->ID;
	}

	return $ids;
}