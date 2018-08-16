<?php

/**
 * Default qameta table fields with values.
 *
 * @return array
 */
function ap_qameta_fields() {
	return array(
		'post_id'      => '',
		'selected'     => false,
		'selected_id'  => 0,
		'answers'      => 0,
		'ptype'        => 'question',
		'closed'       => 0,
		'views'        => 0,
		'votes_up'     => 0,
		'votes_down'   => 0,
		'terms'        => '',
		'attach'       => '',
		'activities'   => '',
		'fields'       => '',
		'roles'        => '',
		'last_updated' => '',
		'year'				 => 0,
		'session'			 => 0,
		'is_new'       => false,
		'inspection_check' => 0,
		'duplication_check'	=> 0,
		'price'				 => 0
	);
}

/**
 * Append post object with apmeta feilds.
 *
 * @param  object $post Post Object.
 * @return object
 */
function ap_append_qameta( $post ) {
	// Convert object as array to prevent using __isset of WP_Post.
	$post_arr = (array) $post;
	if ( ! in_array( $post_arr['post_type'], [ 'question', 'answer' ], true ) || isset( $post_arr['ap_qameta_wrapped'] ) ) {
		return $post;
	}

	$exist = true;
	foreach ( ap_qameta_fields() as $fields_name => $val ) {
		if ( ! isset( $post_arr[ $fields_name ] ) ) {
			$exist = false;
			break;
		}
	}

	if ( ! $exist ) {
		$defaults = ap_get_qameta( $post->ID );
		if ( ! empty( $defaults ) ) {
			foreach ( $defaults as $pkey => $value ) {
				if ( ! isset( $post_arr[ $pkey ] ) || empty( $post_arr[ $pkey ] ) ) {
					$post->$pkey = $value;
				}
			}
		}

		$post->terms      = maybe_unserialize( $post->terms );
		$post->activities = maybe_unserialize( $post->activities );

		$post->votes_net = $post->votes_up - $post->votes_down;
	}

	return $post;
}

/**
 * Insert post meta
 *
 * @param array   $post_id Post ID.
 * @param boolean $args Arguments.
 * @param boolean $wp_error Return wp_error on fail.
 * @return boolean|integer qameta id on success else false.
 * @since   4.0.0
 */
function ap_insert_qameta( $post_id, $args, $wp_error = false ) {

	if ( empty( $post_id ) ) {
		return $wp_error ? new WP_Error( 'Post ID is required' ) : false;
	}

	$_post  = get_post( $post_id );
	$exists = ap_get_qameta( $post_id );

	if ( ! is_object( $_post ) || ! isset( $_post->post_type ) || ! in_array( $_post->post_type, [ 'question', 'answer' ], true ) ) {
		return false;
	}

	$args = wp_unslash(
		wp_parse_args(
			$args, [
				'ptype' => $_post->post_type,
			]
		)
	);

	$sanitized_values = [];
	$formats          = [];

	

	// Include and sanitize valid fields.
	foreach ( (array) ap_qameta_fields() as $field => $type ) {
		if ( isset( $args[ $field ] ) ) {
			$value = $args[ $field ];

			if ( 'fields' === $field ) {
				$value     = maybe_serialize( array_merge( (array) $exists->$field, (array) $value ) );
				$formats[] = '%s';
			} elseif ( 'activities' === $field ) {
				$value     = maybe_serialize( $value );
				$formats[] = '%s';
			} elseif ( 'attach' === $field ) {
				$value     = is_array( $value ) ? sanitize_comma_delimited( $value ) : (int) $value;
				$formats[] = '%s';
			} elseif ( in_array( $field, [ 'selected', 'closed', 'inspection_check', 'duplication_check' ], true ) ) {
				$value     = (bool) $value;
				$formats[] = '%d';
			} elseif ( in_array( $field, [ 'selected_id', 'answers', 'views', 'votes_up', 'votes_down', 'year', 'session', 'price' ], true ) ) {
				$value     = (int) $value;
				$formats[] = '%d';
			} else {
				$value     = sanitize_text_field( $value );
				$formats[] = '%s';
			}

			$sanitized_values[ $field ] = $value;
		}
	}

	global $wpdb;

	// Dont insert or update if not AnsPress CPT.
	// This check will also prevent inserting qameta for deleetd post.
	if ( ! isset( $exists->ptype ) || ! in_array( $exists->ptype, [ 'question', 'answer' ], true ) ) {
		return $wp_error ? new WP_Error( 'Not question or answer CPT' ) : false;
	}

	if ( $exists->is_new ) {
		$sanitized_values['post_id'] = (int) $post_id;

		if ( ! empty( $_post->post_author ) ) {
			$sanitized_values['roles'] = $_post->post_author;
		}

		$inserted = $wpdb->insert( $wpdb->ap_qameta, $sanitized_values, $formats ); // db call ok.
	} else {
		$inserted = $wpdb->update( $wpdb->ap_qameta, $sanitized_values, [ 'post_id' => $post_id ], $formats ); // db call ok.
	}

	if ( false !== $inserted ) {
		$cache = wp_cache_get( $post_id, 'posts' );

		if ( false !== $cache ) {
			foreach ( (array) $sanitized_values as $key => $val ) {
				$cache->$key = $val;
			}

			wp_cache_set( $post_id, $cache, 'posts' );
		}

		wp_cache_delete( $post_id, 'ap_qameta' );
		return $post_id;
	}

	return $wp_error ? new WP_Error( 'Unable to insert AnsPress qameta' ) : false;
}

/**
 * Get a qameta by post_id
 *
 * @param  integer $post_id Post ID.
 * @return object|false
 * @since  3.1.0
 */
function ap_get_qameta( $post_id ) {
	global $wpdb;
	$qameta = wp_cache_get( $post_id, 'ap_qameta' );

	if ( false === $qameta ) {
		$qameta = $wpdb->get_row( $wpdb->prepare( "SELECT qm.*, p.post_type as ptype FROM {$wpdb->posts} p LEFT JOIN {$wpdb->ap_qameta} qm ON qm.post_id = p.ID WHERE p.ID = %d", $post_id ), ARRAY_A ); // db call ok.

		// If null then append is_new.
		if ( empty( $qameta['post_id'] ) ) {
			$qameta = [ 'is_new' => true ];
		}

		$qameta = wp_parse_args( $qameta, ap_qameta_fields() );

		$qameta['votes_net']  = $qameta['votes_up'] + $qameta['votes_down'];
		$qameta['activities'] = maybe_unserialize( $qameta['activities'] );

		if ( empty( $qameta['activities'] ) ) {
			$qameta['activities'] = [];
		}

		$qameta['fields'] = maybe_unserialize( $qameta['fields'] );
		$qameta           = (object) $qameta;
		wp_cache_set( $post_id, $qameta, 'ap_qameta' );
	}

	return $qameta;
}

/**
 * Get a specific post field.
 *
 * @param  string $field Post field name.
 * @param  mixed  $_post Post ID, Object or null.
 * @return mixed
 * @since  4.0.0
 * @since  4.1.5 Serialize field value if column is `fields`.
 */
function ap_get_post_field( $field, $_post = null ) {
	$_post = ap_get_post( $_post );

	if ( isset( $_post->$field ) ) {
		// Serialize if fields column.
		if ( 'fields' === $field ) {
			return maybe_unserialize( $_post->$field );
		}

		return $_post->$field;
	}

	return '';
}

/**
 * Update qameta votes count
 *
 * @param  integer $post_id Post ID.
 * @return boolean|integer
 * @since  3.1.0
 */
function ap_update_votes_count( $post_id ) {
	$count = ap_count_post_votes_by( 'post_id', $post_id );
	ap_insert_qameta( $post_id, $count );
	return $count;
}

/**
 * Update count of answers in post meta.
 *
 * @param  integer $question_id Question ID.
 * @param  integer $counts Custom count value to update.
 * @return boolean|false
 * @since  3.1.0
 */
function ap_update_answers_count( $question_id, $counts = false, $update_time = true ) {
	if ( false === $counts ) {
		$counts = ap_count_published_answers( $question_id );
	}

	$args = [ 'answers' => $counts ];

	if ( $update_time ) {
		$args['last_updated'] = current_time( 'mysql' );
	}

	return ap_insert_qameta( $question_id, $args );
}

/**
 * Clear selected answer from a question.
 *
 * @param  integer $question_id Question ID.
 * @return integer|false
 * @since  3.1.0
 * @since  4.1.2 Insert activity to `ap_activity` table.
 * @since  4.1.8 Reopen question after unselecting.
 */
function ap_unset_selected_answer( $question_id ) {
	$qameta = ap_get_qameta( $question_id );

	// Clear selected column from answer qameta.
	ap_insert_qameta(
		$qameta->selected_id, [
			'selected'     => 0,
			'last_updated' => current_time( 'mysql' ),
		]
	);

	$ret = ap_insert_qameta( $question_id, array(
		'selected_id'  => '',
		'last_updated' => current_time( 'mysql' ),
		'closed'       => 0,
	));

	$_post = ap_get_post( $qameta->selected_id );

	/**
	 * Action triggered after an answer is unselected as best.
	 *
	 * @param WP_Post $_post       Answer post object.
	 * @param WP_Post $question_id Question id.
	 *
	 * @since unknown
	 * @since 4.1.8 Moved from ajax-hooks.php.
	 */
	do_action( 'ap_unselect_answer', $_post, $question_id );

	return $ret;
}

/**
 * Update activities of a qameta.
 *
 * @param  integer $post_id    Post ID.
 * @param  array   $activities Activities.
 * @return boolean|integer
 */
function ap_update_post_activities( $post_id, $activities = array() ) {
	return ap_insert_qameta(
		$post_id, [
			'activities'   => $activities,
			'last_updated' => current_time( 'mysql' ),
		]
	);
}

/**
 * Update post activity meta.
 *
 * @param  object|integer $post                 Question or answer.
 * @param  string         $type                 Activity type.
 * @param  integer        $user_id              ID of user doing activity.
 * @param  boolean        $append_to_question   Append activity to question.
 * @param  boolean|string $date                 Activity date in mysql timestamp format.
 * @return boolean
 * @since  2.4.7
 * @deprecated 4.1.2  Use @see ap_activity_add(). Activities are inserted in `ap_activity` table.
 */
function ap_update_post_activity_meta( $post, $type, $user_id, $append_to_question = false, $date = false ) {
	if ( empty( $post ) ) {
		return false;
	}

	if ( false === $date ) {
		$date = current_time( 'mysql' );
	}

	$post_o   = ap_get_post( $post );
	$meta_val = compact( 'type', 'user_id', 'date' );

	// Append to question activity meta. So that it can shown in question list.
	if ( 'answer' === $post_o->post_type && $append_to_question ) {
		$_post         = ap_get_post( $post_o->post_parent );
		$meta          = $_post->activities;
		$meta['child'] = $meta_val;
		ap_update_post_activities( $post_o->post_parent, $meta );
	}

	return ap_update_post_activities( $post_o->ID, $meta_val );
}

/**
 * Update post attachment IDs.
 *
 * @param  integer $post_id Post ID.
 * @return array
 */
function ap_update_post_attach_ids( $post_id ) {
	global $wpdb;

	$ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} where post_type = 'attachment' AND post_parent = %d", $post_id ) );

	$insert = ap_insert_qameta( (int) $post_id, [ 'attach' => $ids ] );
	return $ids;
}

/**
 * Set selected answer for a question
 *
 * @param  integer $question_id Question ID.
 * @param  integer $answer_id   Answer ID.
 * @return integer|false
 * @since  3.1.0
 * @since  4.1.2 Insert activity to log.
 * @since  4.1.8 Close question after selecting an answer.
 */
function ap_set_selected_answer( $question_id, $answer_id ) {

	ap_insert_qameta(
		$answer_id, [
			'selected'     => 1,
			'last_updated' => current_time( 'mysql' ),
		]
	);

	$q_args = array(
		'selected_id'  => $answer_id,
		'last_updated' => current_time( 'mysql' ),
	);

	// Close question if enabled in option.
	if ( ap_opt( 'close_selected' ) ) {
		$q_args['closed'] = 1;
	}

	ap_insert_qameta( $question_id, $q_args );
	$ret = ap_update_answer_selected( $answer_id );

	$_post = ap_get_post( $answer_id );

	/**
	 * Trigger right after selecting an answer.
	 *
	 * @param WP_Post $_post       WordPress post object.
	 * @param object  $answer_id   Answer ID.
	 *
	 * @since 4.1.8 Moved from ajax-hooks.php.
	 */
	do_action( 'ap_select_answer', $_post, $question_id );

	return $ret;
}

/**
 * Updates selected field of qameta.
 *
 * @param  integer $answer_id Answer ID.
 * @param  boolean $selected Is selected.
 * @return integer|false
 * @since  3.1.0
 */
function ap_update_answer_selected( $answer_id, $selected = true ) {
	return ap_insert_qameta( $answer_id, [ 'selected' => (bool) $selected ] );
}

/**
 * Toggle closed
 *
 * @param  integer $post_id Question ID.
 * @return boolean
 */
function ap_toggle_close_question( $post_id ) {
	$qameta = ap_get_qameta( $post_id );
	$toggle = $qameta->closed ? 0 : 1;
	ap_insert_qameta( $post_id, [ 'closed' => $toggle ] );

	return $toggle;
}

/**
 * Update views count of qameta.
 *
 * @param  integer|false $post_id Question ID.
 * @param  integer|false $views   Passing view will replace existing value else increment existing.
 * @return integer
 * @since  3.1.0
 */
function ap_update_views_count( $post_id, $views = false ) {
	if ( false === $views ) {
		$qameta = ap_get_qameta( $post_id );
		$views  = (int) $qameta->views + 1;
	}

	ap_insert_qameta( $post_id, [ 'views' => $views ] );
	return $views;
}

/**
 * Delete qameta row.
 *
 * @param  integer $post_id Post ID.
 * @return integer|false
 */
function ap_delete_qameta( $post_id ) {
	global $wpdb;
	return $wpdb->delete( $wpdb->ap_qameta, [ 'post_id' => $post_id ], [ '%d' ] ); // db call ok, db cache ok.
}