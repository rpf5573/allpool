<?php

class AP_Hooks {

  public static function init() {
    anspress()->add_action( 'registered_taxonomy', __CLASS__, 'add_ap_tables' );
    anspress()->add_action( 'ap_processed_new_question', __CLASS__, 'after_new_question', 1, 2 );
    anspress()->add_action( 'ap_processed_new_answer', __CLASS__, 'after_new_answer', 1, 2 );
    anspress()->add_action( 'before_delete_post', __CLASS__, 'before_delete' );
    anspress()->add_action( 'wp_trash_post', __CLASS__, 'trash_post_action' );
    anspress()->add_action( 'untrash_post', __CLASS__, 'untrash_posts' );
    anspress()->add_action( 'wp_loaded', __CLASS__, 'flush_rules' );
    anspress()->add_action( 'save_post', __CLASS__, 'base_page_update', 10, 2 );
    anspress()->add_action( 'save_post_question', __CLASS__, 'save_question_hooks', 1, 3 );
		anspress()->add_action( 'save_post_answer', __CLASS__, 'save_answer_hooks', 1, 3 );

		// Fix TinyMCE Editor
		anspress()->add_filter( 'tiny_mce_before_init', __CLASS__, 'disable_autop_in_editor' );
		anspress()->add_filter( 'after_setup_theme', __CLASS__, 'disable_autop_in_content' );
		
		// Register acf fields
		anspress()->add_action( 'after_setup_theme', 'AP_ACF', 'add_question_filter' );
		anspress()->add_action( 'after_setup_theme', 'AP_ACF', 'add_page_banner' );
		anspress()->add_action( 'after_setup_theme', 'AP_ACF', 'add_expert_categories' );
		anspress()->add_action( 'after_setup_theme', 'AP_ACF', 'add_question_choices_answer' );
		anspress()->add_action( 'after_setup_theme', 'AP_ACF', 'add_inspection_check' );
		anspress()->add_action( 'after_setup_theme', 'AP_ACF', 'add_price' );

    // Register pages
    anspress()->add_action( 'init', 'AP_Common_Pages', 'register_common_pages', 0 );
		anspress()->add_action( 'init', 'AP_Category', 'register_categories_page', 0 );
		anspress()->add_action( 'init', 'AP_Profile', 'register_profile_page', 0 );
		anspress()->add_filter( 'template_include', 'AP_Theme', 'anspress_basepage_template', 9999 );
		
    // Register custom post types & custom taxonomy
    anspress()->add_action( 'init', 'AP_PostTypes', 'register_question_cpt', 0 );
    anspress()->add_action( 'init', 'AP_PostTypes', 'register_answer_cpt', 0 );
		anspress()->add_action( 'init', 'AP_Category', 'register_question_taxonomy', 0 );
		anspress()->add_action( 'init', 'AP_Tag', 'register_question_tag', 0 );
		anspress()->add_action( 'init', 'AP_Analysis_Keyword', 'register_question_analysis_keyword_taxonomy', 0 );

    // Register shortcode
    anspress()->add_action( 'init', 'AP_Theme', 'add_shortcode' );

    // Register custom rewrite rules
    anspress()->add_filter( 'request', 'AP_Rewrite', 'alter_the_query' );
    anspress()->add_filter( 'query_vars', 'AP_Rewrite', 'query_var' );
    anspress()->add_action( 'generate_rewrite_rules', 'AP_Rewrite', 'rewrites', 1 );
    anspress()->add_filter( 'parse_request', 'AP_Rewrite', 'add_query_var' );
    anspress()->add_action( 'template_redirect', 'AP_Rewrite', 'shortlink' );

    // Query hooks
    anspress()->add_filter( 'pre_get_posts', 'AP_QA_Query_Hooks', 'pre_get_posts' );
    anspress()->add_filter( 'posts_clauses', 'AP_QA_Query_Hooks', 'sql_filter', 1, 2 );
    anspress()->add_filter( 'posts_pre_query', 'AP_QA_Query_Hooks', 'modify_main_posts', 999999, 2 );
    anspress()->add_filter( 'posts_results', 'AP_QA_Query_Hooks', 'posts_results', 1, 2 );
    anspress()->add_action( 'loop_start', 'AP_QA_Query_Hooks', 'loop_start' );
    
    // Post type
		anspress()->add_action( 'post_type_link', 'AP_PostTypes', 'post_type_link', 10, 2 );
    anspress()->add_filter( 'post_type_archive_link', 'AP_PostTypes', 'post_type_archive_link', 10, 2 );
    
    // Category
    anspress()->add_filter( 'term_link', 'AP_Category', 'term_link_filter', 10, 3 );
		anspress()->add_filter( 'ap_current_page', 'AP_Category', 'ap_current_page' );

		// Filter
		anspress()->add_action( 'ap_question_form_fields', 'AP_Filters', 'form_fields', 10, 2 );
		anspress()->add_action( 'ap_question_form_fields', 'AP_Filters', 'show_only_categories_of_expert', 20, 2 );
		anspress()->add_action( 'ap_display_question_metas', 'AP_Filters', 'ap_display_question_metas', 10, 2 );
		anspress()->add_filter( 'ap_main_questions_args', 'AP_Filters', 'category_filter', 10, 3 );
    anspress()->add_action( 'ap_qa_sql', 'AP_Filters', 'meta_filter', 12, 1 );
    anspress()->add_action( 'ap_processed_new_question', 'AP_Filters', 'save_category', 0, 2 );
    anspress()->add_action( 'ap_processed_update_question', 'AP_Filters', 'save_category', 0, 2 );
		anspress()->add_filter( 'ap_insert_question_qameta', 'AP_Filters', 'save_meta_from_front', 10, 3 );
		// anspress()->add_filter( 'ap_insert_question_qameta', 'AP_Filters', 'save_price', 10, 3 );
	

    // Form hooks
    anspress()->add_action( 'ap_form_question', 'AP_Form_Hooks', 'question_form', 11 );
    anspress()->add_action( 'ap_form_answer', 'AP_Form_Hooks', 'answer_form', 11 );
    anspress()->add_action( 'ap_form_image_upload', 'AP_Form_Hooks', 'image_upload_form', 11 );
		anspress()->add_action( 'ap_form_contents_filter', 'AP_Form_Hooks', 'sanitize_description' );
		

		// Theme	hooks.
		anspress()->add_action( 'after_setup_theme', 'AP_Theme', 'add_theme_supports' );
		anspress()->add_action( 'after_setup_theme', 'AP_Theme', 'register_nav_menus' );
		anspress()->add_action( 'after_setup_theme', 'AP_Theme', 'remove_admin_bar_on_front' );
		anspress()->add_action( 'after_setup_theme', 'AP_Theme', 'deregister_social_login_btns_in_register_form' );
		anspress()->add_action( 'init', 'AP_Theme', 'design_tml' );
		anspress()->add_action( 'init', 'AP_Theme', 'redirect_after_login' );
		anspress()->add_action( 'wp_enqueue_scripts', 'AP_Theme', 'enqueue_scripts' );
    anspress()->add_filter( 'post_class', 'AP_Theme', 'question_answer_post_class' );
    anspress()->add_filter( 'body_class', 'AP_Theme', 'body_class' );
    anspress()->add_filter( 'wp_title', 'AP_Theme', 'ap_title', 0 );
    anspress()->add_action( 'ap_before', 'AP_Theme', 'ap_before_html_body' );
    anspress()->add_filter( 'nav_menu_css_class', 'AP_Theme', 'fix_nav_current_class', 10, 2 );
		anspress()->add_filter( 'human_time_diff', 'AP_Theme', 'human_time_diff' );
		anspress()->add_filter( 'mce_buttons', 'AP_Theme', 'set_mce_btns' );
		anspress()->add_action( 'ap_after_question_content', 'AP_Theme', 'question_choices_answer' );

    // Upload hooks.
    anspress()->add_action( 'deleted_post', 'AP_Uploader', 'deleted_attachment' );
    anspress()->add_action( 'init', 'AP_Uploader', 'create_single_schedule' );
    anspress()->add_action( 'ap_delete_temp_attachments', 'AP_Uploader', 'cron_delete_temp_attachments' );
    anspress()->add_action( 'intermediate_image_sizes_advanced', 'AP_Uploader', 'image_sizes_advanced' );

    // Vote hooks.
    anspress()->add_action( 'ap_before_delete_question', 'AP_Vote', 'delete_votes' );
    anspress()->add_action( 'ap_before_delete_answer', 'AP_Vote', 'delete_votes' );
    anspress()->add_action( 'ap_deleted_votes', 'AP_Vote', 'ap_deleted_votes', 10, 2 );

    // View hooks.
    anspress()->add_action( 'shutdown', 'AP_Views', 'insert_views' );
		anspress()->add_action( 'ap_before_delete_question', 'AP_Views', 'delete_views' );
		
		// Avatar hooks.
		anspress()->add_filter( 'pre_get_avatar_data', 'AP_Avatar', 'get_avatar', 1000, 3 );
		anspress()->add_action( 'wp_ajax_ap_clear_avatar_cache', 'AP_Avatar', 'clear_avatar_cache' );

		// Reputation hooks.
		anspress()->add_filter( 'mycred_setup_hooks', 'AP_Reputation', 'unset_useless_hooks', 10, 2 );
		anspress()->add_filter( 'mycred_setup_hooks', 'AP_Reputation', 'register_hooks', 999, 2 );
		anspress()->add_filter( 'ap_user_mycred_creds', 'AP_Reputation', 'mycred_creds', 10 );
		anspress()->add_filter( 'ap_user_mobile_buttons', 'AP_Reputation', 'mycred_creds', 10 );
		anspress()->add_filter( 'ap_user_display_name', 'AP_Reputation', 'display_name', 10, 2 );
		anspress()->add_filter( 'ap_user_pages', 'AP_Reputation', 'ap_user_pages' );

		// Point hooks.
		anspress()->add_filter( 'mycred_setup_hooks', 'AP_Point', 'unset_useless_hooks', 10, 2 );
		anspress()->add_filter( 'mycred_setup_hooks', 'AP_Point', 'register_hooks', 999, 2 );
		anspress()->add_filter( 'ap_user_mycred_creds', 'AP_Point', 'mycred_creds', 11 );
		anspress()->add_filter( 'ap_user_mobile_buttons', 'AP_Point', 'mycred_creds', 10 );
		anspress()->add_filter( 'ap_user_mobile_buttons', 'AP_Point', 'point_charge_button', 10 );
		anspress()->add_filter( 'ap_user_point_charge_button', 'AP_Point', 'point_charge_button' );
		anspress()->add_filter( 'ap_user_pages', 'AP_Point', 'ap_user_pages' );
		anspress()->add_action( 'after_iamport_payment', 'AP_Point', 'after_charge_point' );
		anspress()->add_action( 'ap_vote_up', 'AP_Point', 'after_vote_up', 10, 2 );
		anspress()->add_action( 'ap_select_answer', 'AP_Point', 'after_select_answer', 10, 2 );

		// Wishlist hooks.
		anspress()->add_action( 'ap_display_question_metas', 'AP_Wishlist', 'display_question_metas', 10, 2 );
		anspress()->add_filter( 'ap_user_pages', 'AP_Wishlist', 'ap_user_pages' );

		// Profile hooks.
		anspress()->add_action( 'ap_rewrites', 'AP_Profile', 'rewrite_rules', 10, 3 );
		anspress()->add_action( 'ap_ajax_user_more_answers', 'AP_Profile', 'load_more_answers', 10, 2 );
		anspress()->add_filter( 'wp_title', 'AP_Profile', 'page_title' );
		anspress()->add_action( 'the_post', 'AP_Profile', 'filter_page_title' );
		anspress()->add_filter( 'ap_current_page', 'AP_Profile', 'ap_current_page' );
		anspress()->add_filter( 'posts_pre_query', 'AP_Profile', 'modify_query_archive', 999, 2 );

		// Point hooks
		anspress()->add_action( 'ap_before_answers', 'AP_Point', 'purchase_answers_button_modal' );
		anspress()->add_filter( 'ap_answer_query_args', 'AP_Point', 'answers_query_args' );

    // Ajax hooks - ajax == is_admin
		if ( wp_doing_ajax() ) {
			AP_Ajax_Hooks::init();
			AP_Admin_Ajax::init();
    }
    
    /*  Admin
    /* --------------------------------------------------- */
    if ( is_admin() && ! wp_doing_ajax() ) {
			AP_Admin::init();
			AP_Post_Table_Hooks::init();
		}
  
  }

  /**
	 * Add AnsPress tables in $wpdb.
	 */
	public static function add_ap_tables() {
		ap_append_table_names();
	}

	/**
	 * Things to do after creating a question
	 *
	 * @param   integer $post_id Question id.
	 * @param   object  $post Question post object.
	 * @since   1.0
	 * @since   4.1.2 Removed @see ap_update_post_activity_meta().
	 */
	public static function after_new_question( $post_id, $post ) {

		/**
		 * Action triggered after inserting a question
		 *
		 * @since 0.9
		 */
		do_action( 'ap_after_new_question', $post_id, $post );
	}

	/**
	 * Things to do after creating an answer
	 *
	 * @param   integer $post_id answer id.
	 * @param   object  $post answer post object.
	 * @since 2.0.1
	 * @since 4.1.2  Removed @see ap_update_post_activity_meta().
	 * @since 4.1.11 Removed @see ap_update_answers_count().
	 */
	public static function after_new_answer( $post_id, $post ) {
		// Update answer count.
		ap_update_answers_count( $post->post_parent );

		/**
		 * Action triggered after inserting an answer
		 *
		 * @since 0.9
		 */
		do_action( 'ap_after_new_answer', $post_id, $post );
	}

	/**
	 * This callback handles pre delete question actions.
	 *
	 * Before deleting a question we have to make sure that all answers
	 * and metas are cleared. Some hooks in answer may require question data
	 * so its better to delete all answers before deleting question.
	 *
	 * @param   integer $post_id Question or answer ID.
	 * @since unknown
	 * @since 4.1.6 Delete cache for `ap_is_answered`.
	 * @since 4.1.8 Delete uploaded images and `anspress-images` meta.
	 */
	public static function before_delete( $post_id ) {

		$post = ap_get_post( $post_id );

		if ( ! ap_is_cpt( $post ) ) {
			return;
		}

		if ( 'question' === $post->post_type ) {

			/**
			 * Action triggered before deleting a question form database.
			 *
			 * At this point question are not actually deleted from database hence
			 * it will be easy to perform actions which uses mysql queries.
			 *
			 * @param integer $post_id Question id.
			 * @param WP_Post $post    Question object.
			 * @since unknown
			 */
			do_action( 'ap_before_delete_question', $post->ID, $post );

			ap_delete_uploaded_images( $post->ID );

			$answers = get_posts( [ 'post_parent' => $post->ID, 'post_type' => 'answer', 'post_status' => array( 'publish', 'trash' ), 'numberposts' => -1 ] ); // @codingStandardsIgnoreLine

			foreach ( (array) $answers as $a ) {
				self::delete_answer( $a->ID, $a );
				wp_delete_post( $a->ID, true );
			}

			// Delete qameta.
			ap_delete_qameta( $post->ID );

		} elseif ( 'answer' === $post->post_type ) {
			self::delete_answer( $post_id, $post );
		}
	}

	/**
	 * Delete answer.
	 *
	 * @param   integer $post_id Question or answer ID.
	 * @param   object  $post Post Object.
	 * @since unknown
	 * @since 4.1.2 Removed @see ap_update_post_activity_meta().
	 */
	public static function delete_answer( $post_id, $post ) {
		do_action( 'ap_before_delete_answer', $post->ID, $post );

		ap_delete_uploaded_images( $post->ID );

		if ( ap_is_selected( $post ) ) {
			ap_unset_selected_answer( $post->post_parent );
		}

		// for direct delete by user
		wp_update_post( array( 'ID' =>  $post->ID, 'post_status' => 'trash' ) );
		ap_update_answers_count( $post->post_parent );

		// Delete qameta.
		ap_delete_qameta( $post->ID );
	}
	
	/**
	 * If a question is sent to trash, then move its answers to trash as well
	 *
	 * @param   integer $post_id Post ID.
	 * @since 2.0.0
	 * @since 4.1.2 Removed @see ap_update_post_activity_meta().
	 * @since 4.1.6 Delete cache for `ap_is_answered`.
	 */
	public static function trash_post_action( $post_id ) {

		$post = ap_get_post( $post_id );

		if ( 'question' === $post->post_type ) {
			do_action( 'ap_trash_question', $post->ID, $post );

			// Save current post status so that it can be restored.
			update_post_meta( $post->ID, '_ap_last_post_status', $post->post_status );

			//@codingStandardsIgnoreStart
			$ans = get_posts( array(
				'post_type'   => 'answer',
				'post_status' => 'publish',
				'post_parent' => $post_id,
				'showposts'   => -1,
			));
			//@codingStandardsIgnoreEnd

			foreach ( (array) $ans as $p ) {
				$selected_answer = ap_selected_answer();
				if ( $selected_answer === $p->ID ) {
					ap_unset_selected_answer( $p->post_parent );
				}

				wp_trash_post( $p->ID );
			}
		}

		if ( 'answer' === $post->post_type ) {

			/**
			 * Triggered before trashing an answer.
			 *
			 * @param integer $post_id Answer ID.
			 * @param object $post Post object.
			 */
			do_action( 'ap_trash_answer', $post->ID, $post );

			

			// 지금 지우려는 답변이 채택된 답변이라면, question의 qameta 수정 + answer의 qameta수정
			if ( $post->selected ) {
				ap_unset_selected_answer( $post->post_parent );
			}

			// Save current post status so that it can be restored.
			update_post_meta( $post->ID, '_ap_last_post_status', $post->post_status );

			ap_update_answers_count( $post->post_parent );
		}
	}

	/**
	 * If questions is restored then restore its answers too.
	 *
	 * @param   integer $post_id Post ID.
	 * @since 2.0.0
	 * @since 4.1.2 Removed @see ap_update_post_activity_meta().
	 * @since 4.1.11 Renamed method from `untrash_ans_on_question_untrash` to `untrash_posts`.
	 */
	public static function untrash_posts( $post_id ) {
		$_post = ap_get_post( $post_id );

		if ( 'question' === $_post->post_type ) {
			do_action( 'ap_untrash_question', $_post->ID, $_post );
			//@codingStandardsIgnoreStart
			$ans = get_posts( array(
				'post_type'   => 'answer',
				'post_status' => 'trash',
				'post_parent' => $post_id,
				'showposts'   => -1,
			));
			//@codingStandardsIgnoreStart

			foreach ( (array) $ans as $p ) {
				//do_action( 'ap_untrash_answer', $p->ID, $p );
				wp_untrash_post( $p->ID );
			}
		}

		if ( 'answer' === $_post->post_type ) {
			$ans = ap_count_published_answers( $_post->post_parent );
			do_action( 'ap_untrash_answer', $_post->ID, $_post );

			// Update answer count.
			ap_update_answers_count( $_post->post_parent, $ans + 1 );
		}
	}

	/**
	 * Check if flushing rewrite rule is needed
	 *
	 * @return void
	 */
	public static function flush_rules() {
		if ( ap_opt( 'ap_flush' ) != 'false' ) {
			flush_rewrite_rules( true );
			ap_opt( 'ap_flush', 'false' );
		}
	}

	/**
	 * Add translations for AnsPress's tinymce plugins.
	 *
	 * @param array $translations Translations for external TinyMCE plugins.
	 * @since 4.1.5
	 */
	public static function mce_plugins_languages( $translations ) {
		$translations['anspress'] = ANSPRESS_DIR . 'includes/mce-languages.php';
		return $translations;
	}

	/**
	 * Flush rewrite rule if base page is updated.
	 *
	 * @param	integer $post_id Base page ID.
	 * @param	object	$post		Post object.
	 * @since 4.1.0   Update respective page slug in options.
	 */
	public static function base_page_update( $post_id, $post ) {
		if ( wp_is_post_revision( $post ) ) {
			return;
		}

		$main_pages = array_keys( ap_main_pages() );
		$page_ids = [];

		foreach ( $main_pages as $slug ) {
			$page_ids[ ap_opt( $slug ) ] = $slug;
		}

		if ( in_array( $post_id, array_keys( $page_ids ) ) ) {
			$current_opt = $page_ids[ $post_id ];

			ap_opt( $current_opt, $post_id );
			ap_opt( $current_opt . '_id', $post->post_name );

			ap_opt( 'ap_flush', 'true' );
		}
	}

	/**
	 * Trigger posts hooks right after saving question.
	 *
	 * @param	integer $post_id Post ID.
	 * @param	object	$post		Post Object
	 * @param	boolean $updated Is updating post
	 * @since 4.1.0
	 * @since 4.1.2 Do not process if form not submitted. Insert updated to activity table.
	 * @since 4.1.8 Add `ap_delete_images_not_in_content`.
	 */
	public static function save_question_hooks( $post_id, $post, $updated ) {
		if ( wp_is_post_autosave( $post ) || wp_is_post_revision( $post ) ) {
			return;
		}

		// Deleted unused images from meta.
		ap_delete_images_not_in_content( $post_id );

		$form = anspress()->get_form( 'question' );
		$values = $form->get_values();

		$qameta = array(
			'last_updated' => current_time( 'mysql' ),
			'answers'      => ap_count_published_answers( $post_id ),
		);

		/**
		 * Modify qameta args which will be inserted after inserting
		 * or updating question.
		 *
		 * @param array   $qameta  Qameta arguments.
		 * @param object  $post    Post object.
		 * @param boolean $updated Is updated.
		 * @since 4.1.0
		 */
		$qameta = apply_filters( 'ap_insert_question_qameta', $qameta, $post, $updated );
		ap_insert_qameta( $post_id, $qameta );

		if ( $updated ) {
			/**
			 * Action triggered right after updating question.
			 *
			 * @param integer $post_id Inserted post ID.
			 * @param object	$post		Inserted post object.
			 * @since 0.9
			 * @since 4.1.0 Removed `$post->post_type` variable.
			 */
			do_action( 'ap_processed_update_question' , $post_id, $post );

		} else {
			/**
			 * Action triggered right after inserting new question.
			 *
			 * @param integer $post_id Inserted post ID.
			 * @param object	$post		Inserted post object.
			 * @since 0.9
			 * @since 4.1.0 Removed `$post->post_type` variable.
			 */
			do_action( 'ap_processed_new_question', $post_id, $post );
		}
	}

	/**
	 * Trigger posts hooks right after saving answer.
	 *
	 * @param	integer $post_id Post ID.
	 * @param	object	$post		Post Object
	 * @param	boolean $updated Is updating post
	 * @since 4.1.0
	 * @since 4.1.2 Do not process if form not submitted. Insert updated to activity table.
	 * @since 4.1.8 Add `ap_delete_images_not_in_content`.
	 */
	public static function save_answer_hooks( $post_id, $post, $updated ) {
		if ( wp_is_post_autosave( $post ) || wp_is_post_revision( $post ) ) {
			return;
		}

		// Deleted unused images from meta.
		ap_delete_images_not_in_content( $post_id );

		$form = anspress()->get_form( 'answer' );

		$values = $form->get_values();

		// Update parent question's answer count.
		ap_update_answers_count( $post->post_parent );

		$qameta = array(
			'last_updated' => current_time( 'mysql' ),
		);

		/**
		 * Modify qameta args which will be inserted after inserting
		 * or updating answer.
		 *
		 * @param array   $qameta  Qameta arguments.
		 * @param object  $post    Post object.
		 * @param boolean $updated Is updated.
		 * @since 4.1.0
		 */
		$qameta = apply_filters( 'ap_insert_answer_qameta', $qameta, $post, $updated );
		ap_insert_qameta( $post_id, $qameta );

		if ( $updated ) {
			/**
			 * Action triggered right after updating answer.
			 *
			 * @param integer $post_id Inserted post ID.
			 * @param object	$post		Inserted post object.
			 * @since 0.9
			 * @since 4.1.0 Removed `$post->post_type` variable.
			 */
			do_action( 'ap_processed_update_answer' , $post_id, $post );

		} else {
			/**
			 * Action triggered right after inserting new answer.
			 *
			 * @param integer $post_id Inserted post ID.
			 * @param object	$post		Inserted post object.
			 * @since 0.9
			 * @since 4.1.0 Removed `$post->post_type` variable.
			 */
			do_action( 'ap_processed_new_answer', $post_id, $post );
		}
	}

	/**
	 * Append variable to post Object.
	 *
	 * @param Object $post Post object.
	 * @deprecated 4.1.1
	 */
	public static function filter_page_title( $post ) {
		if ( ap_opt( 'base_page' ) == $post->ID && ! is_admin() ) {
			$post->post_title = ap_page_title();
		}
	}

	public static function change_mce_options($init) {
    $init["forced_root_block"] = false;
    $init["force_br_newlines"] = true;
    $init["force_p_newlines"] = false;
		$init["convert_newlines_to_brs"] = true;
		
    return $init;
	}

	public static function disable_autop_in_editor( $init ) {
		$init['wpautop'] = false;
		return $init;
	}

	public static function disable_autop_in_content() {
		remove_filter( 'the_content', 'wpautop' );
	}

}