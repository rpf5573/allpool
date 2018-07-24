<?php
/**
 * Roles and Capabilities
 *
 * @package      AnsPress
 * @subpackage   Roles and Capabilities
 * @copyright    Copyright (c) 2013, Rahul Aryan
 * @author       Rahul Aryan <support@anspress.io>
 * @license      GPL-3.0+
 * @since        0.8
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AnsPress user role helper
 */
class AP_Roles {
	/**
	 * Base user capabilities.
	 *
	 * @var array
	 */
	public $base_caps = array();

	/**
	 * Moderator level permissions.
	 *
	 * @var array
	 */
	public $mod_caps = array();

	/**
	 * Initialize the class
	 */
	public function __construct() {
		/**
		 * Base user caps.
		 *
		 * @var array
		 */
		$this->base_caps = ap_role_caps( 'participant' );

		$this->expert_caps = ap_role_caps( 'expert' );

		/**
		 * Admin level caps.
		 *
		 * @var array
		 */
		$this->mod_caps = ap_role_caps( 'moderator' );
	}

	/**
	 * Add roles and cap, called on plugin activation
	 *
	 * @since 2.0.1
	 */
	public function add_roles() {
		add_role( 'ap_moderator', __( 'AnsPress Moderator', 'anspress-question-answer' ), array( 'read' => true ) );

		add_role( 'ap_expert', __( 'AnsPress Expert', 'anspress-question-answer' ), array( 'read' => true ) );

		add_role( 'ap_participant', __( 'AnsPress Participants', 'anspress-question-answer' ), array( 'read' => true ) );

		// set ap_participant to default role after registration
		update_option('default_role', 'ap_participant');
	}

	/**
	 * Add new capabilities
	 *
	 * @access public
	 * @since  2.0
	 * @global WP_Roles $wp_roles
	 * @return void
	 */
	public function add_capabilities() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles(); // Override okay.
		}

		if ( is_object( $wp_roles ) ) {
			$roles = [ 'administrator', 'ap_moderator', 'ap_expert', 'ap_participant' ];

			foreach ( $roles as $role_name ) {
				// Add base cpas to all roles.
				foreach ( $this->base_caps as $k => $grant ) {
					$wp_roles->add_cap( $role_name, $k );
				}

				if ( $role_name == 'ap_expert' ) {
					foreach( $this->expert_caps as $k => $grant ) {
						$wp_roles->add_cap( $role_name, $k );
					}
				}

				if ( in_array( $role_name, [ 'administrator', 'ap_moderator' ], true ) ) {
					foreach ( $this->mod_caps as $k => $grant ) {
						$wp_roles->add_cap( $role_name, $k );
					}
				}
			}
		}
	}

	/**
	 * Remove an AnsPress role
	 */
	public static function remove_roles() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles(); // override okay.
		}

		$wp_roles->remove_role( 'ap_participant' );
		$wp_roles->remove_role( 'ap_expert' );
		$wp_roles->remove_role( 'ap_moderator' );
	}
}

/**
 * Get AnsPress role capabilities by role key.
 *
 * @param  string $role Role key.
 * @return array|false
 * @since 2.4.6
 */
function ap_role_caps( $role ) {
	$roles = array(
		'participant' => array(
			'ap_read_question'   => true,
			'ap_read_answer'     => true,
			'ap_new_question'    => true,
			'ap_new_answer'      => true,
			'ap_edit_question'   => true,
			'ap_edit_answer'     => true,
			'ap_delete_question' => true,
			'ap_delete_answer'   => true,
			'ap_vote_up'         => true,
			'ap_vote_down'       => true,
			'ap_vote_close'      => true,
			'ap_change_status'   => true,
			'unfiltered_html'		 => true,
			'unfiltered_upload'	 => true,
			'view_query_monitor' => true,
		),
		'expert'	=> array(
			'delete_posts' 							=> true,
			'delete_published_posts'		=> true,
			'edit_posts'								=> true,
			'edit_published_posts'			=> true,
			'publish_posts'							=> true,
			'edit_others_posts'					=> true,
		),
		'moderator'   => array(
			'ap_edit_others_question'   => true,
			'ap_edit_others_answer'     => true,
			'ap_delete_others_question' => true,
			'ap_delete_others_answer'   => true,
			'ap_delete_post_permanent'  => true,
			'ap_change_status_other'    => true,
			'ap_restore_posts'          => true,
			'ap_toggle_best_answer'     => true,
			'ap_other_category_qa'			=> true,
			'manage_categories'					=> true,
			'delete_others_posts'				=> true,
		),
	);

	$roles = apply_filters( 'ap_role_caps', $roles );

	if ( isset( $roles[ $role ] ) ) {
		return $roles[ $role ];
	}

	return false;
}

/**
 * Check if a user can ask a question.
 *
 * @param  integer|boolean $user_id User_id.
 * @return boolean
 * @since  2.4.6 Added new argument `$user_id`.
 * @since  4.1.0 Updated to use new option post_question_per.
 */
function ap_user_can_ask( $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) || ap_is_moderator( $user_id ) || ap_is_participant( $user_id ) || ap_is_expert( $user_id ) ) {
		return true;
	}

	return false;
}

/**
 * Check if a user can answer on a question.
 *
 * @param  mixed           $question_id    Question id or object.
 * @param  boolean|integer $user_id        User ID.
 * @return boolean
 * @since  2.4.6 Added new argument `$user_id`.
 * @since  4.1.0 Check if `$question_id` argument is a valid question CPT ID. Updated to use new option post_answer_per. Also removed checking of option only_admin_can_answer. Fixed: anonymous cannot answer if allow op to answer option is unchecked.
 */
function ap_user_can_answer( $question_id, $user_id = false ) {

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$question = ap_get_post( $question_id );

	// Return false if not a question.
	if ( ! $question || 'question' !== $question->post_type ) {
		return false;
	}

	if ( is_super_admin( $user_id ) || ap_is_moderator( $user_id ) ) {
		return true;
	}

	if ( ap_is_expert( $user_id ) ) {
		if ( ap_is_in_expert_categories( $question, $user_id ) ) {
			return true;
		}
		return false;
	}

	// Do not allow to answer if best answer is selected.
	if ( ap_opt( 'close_selected' ) && ap_have_answer_selected( $question->ID ) ) {
		return false;
	}

	// Bail out if question is closed.
	if ( ap_is_post_closed( $question ) ) {
		return false;
	}

	// Check if user is original poster and dont allow them to answer their own question.
	if ( is_user_logged_in() && ! ap_opt( 'allow_op_to_answer' ) && ! empty( $question->post_author ) && $question->post_author == $user_id ) { // loose comparison ok.
		return false;
	}

	// Check if user already answered and if multiple answer disabled then don't allow them to answer.
	if ( is_user_logged_in() && ! ap_opt( 'multiple_answers' ) && ap_is_user_answered( $question->ID, $user_id ) ) {
		return false;
	}

	if ( is_user_logged_in() ) {
		return true;
	}

	return false;
}

/**
 * Check if user can select an answer.
 *
 * @param  mixed         $_post    Post.
 * @param  integer|false $user_id    user id.
 * @return boolean
 * @since unknown
 * @since 4.1.6 Allow moderators to toggle best answer.
 */
function ap_user_can_select_answer( $_post = null, $user_id = false ) {

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	// Allow moderators to toggle best answer.
	if ( is_super_admin( $user_id ) || ap_is_moderator( $user_id ) ) {
		return true;
	}

	$answer = ap_get_post( $_post );

	// If not answer then return false.
	if ( 'answer' !== $answer->post_type ) {
		return false;
	}

	$question = ap_get_post( $answer->post_parent );

	if ( is_user_logged_in() && ( ! empty( $question->post_author ) ) && ( $user_id == $question->post_author ) ) {
		return true;
	}

	return false;
}

/**
 * Check if a user can edit answer on a question.
 *
 * @param  mixed           $post    Post.
 * @param  boolean|integer $user_id User id.
 * @return boolean
 * @since  4.0.0
 */
function ap_user_can_edit_post( $post = null, $user_id = false, $wp_error = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$_post = ap_get_post( $post );
	$type = $_post->post_type;

	if ( is_super_admin( $user_id ) || ap_is_moderator( $user_id ) ) {
		return true;
	}

	if ( ( ! empty( $_post->post_author ) ) && ( $user_id == $_post->post_author ) ) { // loose comparison ok.
		// select best answer or got votes
		if ( $type == 'question' && ( $_post->selected_id > 0 || $_post->votes_net > 0 ) ) {
			if ( $wp_error ) {
				return new WP_Error( 'you_cannot_edit_question', __( '베스트 답변을 선택했거나 좋아요를 받은 경우 질문을 수정할 수 없습니다', 'anspress-question-answer' ) );
			}
			return false;
		}
		// selected as best answer or got votes
		if ( $type == 'answer' && ( $_post->selected || $_post->votes_net > 0 ) ) {
			if ( $wp_error ) {
				return new WP_Error( 'you_cannot_edit_answer', __( '베스트 답변으로 선택되었거나 좋아요를 받은 경우 답변을 수정할 수 없습니다', 'anspress-question-answer' ) );
			}
			return false;
		}

		return true;
	}	

	if ( $wp_error ) {
		return new WP_Error( 'no_permission', __( 'You do not have permission to edit question.', 'anspress-question-answer' ) );
	}

	return false;
}

/**
 * Check if a user can edit answer on a question.
 *
 * @param  integer         $post_id Answer id.
 * @param  boolean|integer $user_id User id.
 * @return boolean
 * @since  2.4.7 Renamed function from `ap_user_can_edit_ans` to `ap_user_can_edit_answer`.
 */
function ap_user_can_edit_answer( $post_id, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) || ap_is_moderator( $user_id ) ) {
		return true;
	}

	$answer = ap_get_post( $post_id );

	if ( ( ! empty( $answer->post_author ) ) && ( $user_id == $answer->post_author ) ) { // loose comparison ok.
		// select best answer or got votes
		if ( $answer->selected_id > 0 || $answer->votes_net > 0 ) {
			if ( $wp_error ) {
				return new WP_Error( 'you_cannot_edit_answer', __( '베스트 답변을 선택했거나 좋아요를 받은 경우 답변을 수정할 수 없습니다', 'anspress-question-answer' ) );
			}
			return false;
		}

		return true;
	}

	if ( $wp_error ) {
		return new WP_Error( 'no_permission', __( 'You do not have permission to edit question.', 'anspress-question-answer' ) );
	}

	return false;
}

/**
 * Check if user can edit a question.
 *
 * @param  boolean|integer $post_id Question ID.
 * @param  boolean|integer $user_id User ID.
 * @return boolean
 * @since  2.4.7 Added new argument `$user_id`.
 * @since  2.4.7 Added new filter `ap_user_can_edit_question`.
 * @since  4.1.5 Check if valid post type.
 * @since  4.1.8 Fixed: user is not able to edit their own question.
 */
function ap_user_can_edit_question( $post_id = false, $user_id = false, $wp_error = false ) {

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) || ap_is_moderator( $user_id ) ) {
		return true;
	}

	if ( false !== $post_id ) {
		$question = ap_get_post( $post_id );
	} else {
		global $post;
		$question = $post;
	}

	if ( ( ! empty( $question->post_author ) ) && ( $user_id == $question->post_author ) ) { // loose comparison ok.
		// select best answer or got votes
		if ( $question->selected_id > 0 || $question->votes_net > 0 ) {
			if ( $wp_error ) {
				return new WP_Error( 'you_cannot_edit_question', __( '베스트 답변을 선택했거나 좋아요를 받은 경우 질문을 수정할 수 없습니다', 'anspress-question-answer' ) );
			}
			return false;
		}

		return true;
	}

	if ( $wp_error ) {
		return new WP_Error( 'no_permission', __( 'You do not have permission to edit question.', 'anspress-question-answer' ) );
	}

	return false;
}

/**
 * Check if user can delete AnsPress posts.
 *
 * @param  mixed         $post_id    Question or answer ID.
 * @param  integer|false $user_id    User ID.
 * @return boolean
 * @since  2.4.7 Renamed function name from `ap_user_can_delete`.
 * @since  2.4.7 Added filter `ap_user_can_delete_post`.
 */
function ap_user_can_delete_post( $post_id, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) || ap_is_moderator( $user_id ) ) {
		return true;
	}

	$post_o = ap_get_post( $post_id );
	$type   = $post_o->post_type;

	// Return if not question or answer post type.
	if ( ! in_array( $type, [ 'question', 'answer' ], true ) ) {
		return false;
	}

	return false;
}

/**
 * Check if user can delete a question.
 *
 * @param  object|integer $question   Question ID or object.
 * @param  boolean        $user_id    User ID.
 * @return boolean
 * @since  2.4.7
 * @uses   ap_user_can_delete_post
 */
function ap_user_can_delete_question( $question, $user_id = false ) {
	return ap_user_can_delete_post( $question, $user_id );
}

/**
 * Check if user can delete a answer.
 *
 * @param  object|integer $answer   Answer ID or object.
 * @param  boolean        $user_id  User ID.
 * @return boolean
 * @since  2.4.7
 * @uses   ap_user_can_delete_post
 */
function ap_user_can_delete_answer( $answer, $user_id = false ) {
	return ap_user_can_delete_post( $answer, $user_id );
}

/**
 * Check if user can permanently delete a AnsPress posts
 *
 * @return boolean
 */
function ap_user_can_permanent_delete( $post = null, $user_id = false, $wp_error = false ) {

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$_post = ap_get_post( $post );
	$type = $_post->post_type;

	if ( is_super_admin( $user_id ) || ap_is_moderator( $user_id ) ) {
		return true;
	}

	if ( ( ! empty( $_post->post_author ) ) && ( $user_id == $_post->post_author ) ) { // loose comparison ok.
		// select best answer or got votes
		if ( $type == 'question' && ( $_post->selected_id > 0 || $_post->votes_net > 0 ) ) {
			if ( $wp_error ) {
				return new WP_Error( 'you_cannot_edit_question', __( '베스트 답변을 선택했거나 좋아요를 받은 경우 질문을 삭제할 수 없습니다', 'anspress-question-answer' ) );
			}
			return false;
		}
		// selected as best answer or got votes
		if ( $type == 'answer' && ( $_post->selected || $_post->votes_net > 0 ) ) {
			if ( $wp_error ) {
				return new WP_Error( 'you_cannot_edit_answer', __( '베스트 답변으로 채택되었거나 좋아요를 받은 경우 답변을 삭제할 수 없습니다', 'anspress-question-answer' ) );
			}
			return false;
		}

		return true;
	}	

	if ( $wp_error ) {
		return new WP_Error( 'no_permission', __( 'You do not have permission to delete post.', 'anspress-question-answer' ) );
	}

	return false;
}

/**
 * Check if user can restore question or answer.
 *
 * @param  boolean|integer $user_id  User ID.
 * @return boolean
 * @since  3.0.0
 */
function ap_user_can_restore( $_post = null, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	// only admin
	if ( is_super_admin( $user_id ) || ap_is_moderator( $user_id ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can view post
 *
 * @param  integer|false $post_id Question or answer ID.
 * @param  integer|false $user_id User ID.
 * @return boolean
 */
function ap_user_can_view_post( $post_id = false, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) || ap_is_moderator( $user_id ) ) {
		return true;
	}

	$post_o = is_object( $post_id ) ? $post_id : ap_get_post( $post_id );

	if ( 'publish' === $post_o->post_status ) {
		return true;
	}

	return false;
}

/**
 * Check if current user can change post status i.e. private_post, moderate, closed.
 *
 * @param  integer|object  $post_id    Question or Answer id.
 * @param  integer|boolean $user_id    User id.
 * @return boolean
 **/
function ap_user_can_change_status( $post_id, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) || ap_is_moderator( $user_id ) ) {
		return true;
	}

	$post_o = ap_get_post( $post_id );
	$type = $post_o->post_type;

	if ( ! in_array( $post_o->post_type, [ 'question', 'answer' ], true ) ) {
		return false;
	}

	if ( ap_user_can_control_mine( $post_o, $user_id ) ) {
		return true;
	}

	return false;
}

/**
 * Check if a user can read post.
 *
 * @param  integer|object  $_post   Post ID.
 * @param  boolean|integer $user_id   User ID.
 * @param  string|integer  $post_type Post type.
 * @return boolean
 * @since  2.4.6
 * @since  4.1.0 Check for options `read_question_per` and `read_answer_per`.
 */
function ap_user_can_read_post( $_post = null, $user_id = false, $post_type = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$post_o    = ap_get_post( $_post );
	$post_type = $post_o->post_type;

	if ( ! $post_o ) {
		return false;
	}

	// If not question or answer then return true.
	if ( ! in_array( $post_type, [ 'question', 'answer' ], true ) ) {
		return true;
	}

	/**
	 * Allow overriding of ap_user_can_read_post.
	 *
	 * @param  boolean|string   $apply_filter Default is empty string.
	 * @param  integer          $post_id      Question ID.
	 * @param  integer          $user_id      User ID.
	 * @param  string           $post_type    Post type.
	 * @return boolean
	 * @since  2.4.6
	 */
	$filter = apply_filters( 'ap_user_can_read_post', '', $post_o->ID, $user_id, $post_type );

	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	// Also return true if user have capability to edit others question.
	if ( user_can( $user_id, 'ap_edit_others_' . $post_type ) ) {
		return true;
	}

	// Do not allow to read trash post.
	if ( 'trash' === $post_o->post_status ) {
		return false;
	}

	$option = ap_opt( 'read_' . $post_type . '_per' );

	if ( 'have_cap' === $option && is_user_logged_in() && user_can( $user_id, 'ap_read_' . $post_type ) ) {
		return true;
	} elseif ( 'logged_in' === $option && is_user_logged_in() ) {
		return true;
	} elseif ( 'anyone' === $option ) {
		return true;
	}

	// Finally return false. And break the heart :p.
	return false;
}

/**
 * Check if a user can read question.
 *
 * @param  mixed           $question_id   Question ID.
 * @param  boolean|integer $user_id     User ID.
 * @return boolean
 * @uses   ap_user_can_read_post
 * @since  2.4.6
 */
function ap_user_can_read_question( $question_id, $user_id = false ) {
	return ap_user_can_read_post( $question_id, $user_id, 'question' );
}

/**
 * Check if a user can read answer.
 *
 * @param  integer|object  $answer_id   Answer ID.
 * @param  boolean|integer $user_id     User ID.
 * @return boolean
 * @uses   ap_user_can_read_post
 * @since  2.4.6
 */
function ap_user_can_read_answer( $post = null, $user_id = false ) {
	return ap_user_can_read_post( $post, $user_id, 'answer' );
}

/**
 * Check if user is allowed to cast a vote on post.
 *
 * @param  integer|object  $post_id     Post ID or Object.
 * @param  string          $type        Vote type. vote_up or vote_down.
 * @param  boolean|integer $user_id     User ID.
 * @param  boolean         $wp_error    Return WP_Error object.
 * @return boolean
 * @since  2.4.6
 */
function ap_user_can_vote_on_post( $post_id, $type, $user_id = false, $wp_error = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	// Return true if super admin.
	if ( is_super_admin( $user_id ) || ap_is_moderator( $user_id ) ) {
		return true;
	}

	$type   = 'vote_up' === $type ? 'vote_up' : 'vote_down';
	$post_o = ap_get_post( $post_id );

	// Do not allow post author to vote on self posts.
	if ( ( ! empty( $post_o->post_author ) ) && ( $post_o->post_author == $user_id ) ) { // loose comparison okay.
		if ( $wp_error ) {
			return new WP_Error( 'cannot_vote_own_post', __( 'Voting on own post is not allowed', 'anspress-question-answer' ) );
		}
		return false;
	}

	if ( user_can( $user_id, 'ap_' . $type ) ) {
		return true;
	}

	if ( $wp_error ) {
		return new WP_Error( 'no_permission', __( 'You do not have permission to vote.', 'anspress-question-answer' ) );
	}

	return false;
}

/**
 * Check if user can delete an attachment.
 *
 * @param  integer         $attacment_id Attachment ID.
 * @param  boolean|integer $user_id      User ID.
 * @return boolean
 * @since  3.0.0
 */
function ap_user_can_delete_attachment( $attacment_id, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) || ap_is_moderator( $user_id ) ) {
		return true;
	}

	$attachment = ap_get_post( $attacment_id );

	if ( ! $attachment ) {
		return false;
	}

	// Check if attachment post author matches `$user_id`.
	if ( $user_id == $attachment->post_author ) { // loose comparison ok.
		return true;
	}

	return false;
}

/**
 * Check if user can upload an image.
 *
 * @return boolean
 */
function ap_user_can_upload() {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	if ( is_super_admin() ) {
		return true;
	}

	if ( ap_opt( 'allow_upload' ) ) {
		return true;
	}

	return false;
}

function ap_user_can_edit_other_category_qa( $post_id, $user_id = false ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) || ap_is_moderator( $user_id ) ) {
		return true;
	}

	$post  = ap_get_post( $post_id );

	if ( ! in_array( $post->post_type, [ 'question', 'answer' ], true ) ) {
		return false;
	}

	if ( $post->post_type == 'answer' ) {
		$post = ap_get_post( $post->post_parent );
	}

	if ( ap_is_expert( $user_id ) ) {
		if ( ap_is_in_expert_categories( $post, $user_id ) ) {
			return true;
		} else {
			return false;
		}
	}

	return false;
}

/**
 * Get user role by user_id
 *
 * @param boolean|integer $user_id User ID.
 * @return void
 * @since 1.0.0
 */
function ap_get_user_role( $user_id ) {
	$user = get_userdata( $user_id );
  return empty( $user ) ? array() : $user->roles;
}

/**
 * Check user has role
 *
 * @param  boolean|integer $user_id
 * @param  string $role
 * @return boolean
 * @since  1.0.0
 */
function ap_is_user_in_role( $user_id, $role ) {
	return in_array( $role, ap_get_user_role( $user_id ) );
}

/**
 * Check user is moderator
 *
 * @param integer|boolean $user_id
 * @return boolean
 */
function ap_is_moderator( $user_id ) {
	return ap_is_user_in_role( $user_id, 'ap_moderator' );
}

/**
 * Check user is participant
 *
 * @param integer|boolean $user_id
 * @return boolean
 */
function ap_is_participant( $user_id ) {
	return ap_is_user_in_role( $user_id, 'ap_participant' );
}

function ap_is_expert( $user_id = false ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	return ap_is_user_in_role( $user_id, 'ap_expert' );
}

function ap_is_in_expert_categories( $_post, $user_id = false ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	$expert_categories = ap_get_expert_categories( $user_id );
	if ( ! empty( $expert_categories ) && in_array( $_post->post_type, [ 'question', 'answer' ] ) ) {
		$terms = array();
		$post_id = (int)( ( $_post->post_type == 'question' ) ? $_post->ID : $_post->post_parent );
		$term_ids = array();
		$terms = get_the_terms( $post_id, 'question_category' );
		if ( is_array( $terms ) ) {
			foreach( $terms as $term ) {
				$term_ids[] = $term->term_id;
			}
		}
	
		$result = array_intersect($expert_categories, $term_ids);
		if ( ! empty( $result ) ) {
			return true;
		}
	}
	return false;	
}

function ap_get_expert_categories( $user_id ) {
	$all = array();
	$expert_categories = get_user_meta( $user_id, 'expert_categories', true );

	if ( is_array( $expert_categories ) ) {
		foreach( $expert_categories as $id ) {
			$all[] = (int)$id;
			$all = array_merge( $all, get_term_children( $id, 'question_category' ) );
		}
		$all = array_unique( $all );
	}

	return $all;
}

function ap_user_can_control_mine( $post, $user_id ) {
	$type = $post->post_type;
	if ( ( ! empty( $post->post_author ) ) && ( $user_id == $post->post_author ) ) { // loose comparison ok.
		// select best answer or got votes
		if ( $type == 'question' && ( $post->selected_id > 0 || $post->votes_net > 0 ) ) {
			return false;
		}
		// selected as best answer or got votes
		if ( $type == 'answer' && ( $post->selected || $post->votes_net > 0 ) ) {
			return false;
		}

		return true;
	}	
}