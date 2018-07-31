<?php
/**
 * Register all ajax hooks.
 *
 * @author       Rahul Aryan <support@anspress.io>
 * @license      GPL-2.0+
 * @link         https://anspress.io
 * @copyright    2014 Rahul Aryan
 * @package      AnsPress
 * @subpackage   Ajax Hooks
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register all ajax callback
 */
class AP_Ajax_Hooks {
	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 */
	public static function init() {
    // Ask & Answer
    anspress()->add_action( 'wp_ajax_ap_form_question', 'AP_Form_Hooks', 'submit_question_form', 11, 0 );
		anspress()->add_action( 'wp_ajax_ap_form_answer', 'AP_Form_Hooks', 'submit_answer_form', 11, 0 );

		// Post actions.
		anspress()->add_action( 'ap_ajax_post_actions', 'AP_Theme', 'post_actions' );
		anspress()->add_action( 'ap_ajax_action_close', __CLASS__, 'close_question' );
		anspress()->add_action( 'ap_ajax_action_edit_post', __CLASS__, 'edit_post' );
		anspress()->add_action( 'ap_ajax_action_delete_permanently', __CLASS__, 'permanent_delete_post' );

		// Uploader hooks.
    anspress()->add_action( 'ap_ajax_delete_attachment', 'AP_Uploader', 'delete_attachment' );
    anspress()->add_action( 'wp_ajax_ap_image_upload', 'AP_Uploader', 'image_upload' );
		anspress()->add_action( 'wp_ajax_ap_upload_modal', 'AP_Uploader', 'upload_modal' );
    
    // etc.
    anspress()->add_action( 'ap_ajax_load_tinymce', __CLASS__, 'load_tinymce' );
    anspress()->add_action( 'ap_ajax_vote', 'AP_Vote', 'vote' );
		anspress()->add_action( 'wp_ajax_ap_toggle_best_answer', 'AP_Toggle_Best_Answer', 'init' );
	}

	public static function edit_post() {
		$post_id = (int) ap_sanitize_unslash( 'post_id', 'request' );
		$post = ap_get_post( $post_id );

		$failed_response = array(
			'success'  => false,
			'snackbar' => [ 'message' => __( 'Unable edit this question', 'anspress-question-answer' ) ],
		);

		if ( ! ap_verify_nonce( 'edit_post_' . $post_id ) ) {
			ap_ajax_json( $failed_response );
		}

		$error = ap_user_can_edit_post( $post, false, true );

		// Check if WP_Error object and send error message code.
		if ( is_wp_error( $error ) ) {
			ap_ajax_json(
				[
					'success'  => false,
					'snackbar' => [
						'message' => $error->get_error_message(),
					],
				]
			);
		}

		ap_ajax_json( array(
			'success'  => true,
			'redirect' => ap_post_edit_link( $post ),
			'snackbar' => [ 'message' => __( '잠시후 수정 페이지로 이동됩니다', 'anspress-question-answer' ) ],
		) );

	}

	/**
	 * Handle Ajax callback for permanent delete of post.
	 */
	public static function permanent_delete_post() {
		
		$post_id = (int) ap_sanitize_unslash( 'post_id', 'request' );

		if ( ! ap_verify_nonce( 'delete_post_' . $post_id ) ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => __( 'Sorry, unable to delete post', 'anspress-question-answer' ) ],
			) );
		}

		$post = ap_get_post( $post_id );

		$error = ap_user_can_permanent_delete( $post, false, true );

		if ( is_wp_error( $error ) ) {
			ap_ajax_json(
				[
					'success'  => false,
					'snackbar' => [
						'message' => $error->get_error_message(),
					],
				]
			);
		}

		if ( 'question' === $post->post_type ) {
			/**
			 * Triggered right before deleting question.
			 *
			 * @param  integer $post_id question ID.
			 */
			do_action( 'ap_wp_trash_question', $post_id );
		} else {
			/**
			 * Triggered right before deleting answer.
			 *
			 * @param  integer $post_id answer ID.
			 */
			do_action( 'ap_wp_trash_answer', $post_id );
		}

		wp_delete_post( $post_id, true );

		if ( 'question' === $post->post_type ) {
			ap_ajax_json( array(
				'success'  => true,
				'redirect' => ap_base_page_link(),
				'snackbar' => [ 'message' => __( 'Question is deleted permanently', 'anspress-question-answer' ) ],
			) );
		}

		$current_ans = ap_count_published_answers( $post->post_parent );
		$count_label = sprintf( _n( '%d Answer', '%d Answers', $current_ans, 'anspress-question-answer' ), $current_ans );

		ap_ajax_json( array(
			'success'      => true,
			'snackbar'     => [ 'message' => __( 'Answer is deleted permanently', 'anspress-question-answer' ) ],
			'deletePost'   => $post_id,
			'answersCount' => [ 'text' => $count_label, 'number' => $current_ans ],
		) );
	}

	/**
	 * Close question callback.
	 *
	 * @since unknown
	 * @since 4.1.2 Add activity when question is closed.
	 */
	public static function close_question() {
		$post_id = ap_sanitize_unslash( 'post_id', 'p' );

		// Check permission and nonce.
		if ( ! is_user_logged_in() || ! check_ajax_referer( 'close_' . $post_id, 'nonce', false ) || ! ap_user_can_close_question() ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => __( 'You cannot close a question', 'anspress-question-answer' ) ],
			));
		}

		$_post = ap_get_post( $post_id );
		$toggle = ap_toggle_close_question( $post_id );
		$close_label = $_post->closed ? __( 'Close', 'anspress-question-answer' ) :  __( 'Open', 'anspress-question-answer' );
		$close_title = $_post->closed ? __( 'Close this question for new answer.', 'anspress-question-answer' ) : __( 'Open this question for new answers', 'anspress-question-answer' );

		$message = 1 === $toggle ? __( 'Question closed', 'anspress-question-answer' ) : __( 'Question is opened', 'anspress-question-answer' );

		$results = array(
			'success'     => true,
			'action'      => [ 'label' => $close_label, 'title' => $close_title ],
			'snackbar'    => [ 'message' => $message ],
			'postmessage' => ap_get_post_status_message( $post_id ),
		);

		ap_ajax_json( $results );
	}

	/**
	 * Send JSON response and terminate.
	 *
	 * @param array|string $result Ajax response.
	 */
	public static function send( $result ) {
		ap_send_json( ap_ajax_responce( $result ) );
	}

	/**
	 * Load tinyMCE assets using ajax.
	 *
	 * @since 3.0.0
	 */
	public static function load_tinymce() {
		ap_answer_form( ap_sanitize_unslash( 'question_id', 'r' ) );
		ap_ajax_tinymce_assets();

		wp_die();
	}
  
}