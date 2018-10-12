<?php
/**
 * Award reputation to user based on activities.
 *
 * @author       Rahul Aryan <support@anspress.io>
 * @copyright    2014 AnsPress.io & Rahul Aryan
 * @license      GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link         https://anspress.io
 * @package      AnsPress
 * @subpackage   Reputation addon
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Reputation hooks.
 */
class AP_Wishlist extends \AnsPress\Singleton {

	/**
	 * Instance of this class.
	 *
	 * @var     object
	 * @since 4.1.8
	 */
	protected static $instance = null;

	/**
	 * Init class.
	 */
	protected function __construct() {}

	public static function toggle_wishlist() {
		$post_id = (int) ap_sanitize_unslash( 'id', 'r' );

		// 로그인 한 사람만 담을 수 있어
		if ( ! is_user_logged_in() ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => __( 'You must be logged in to subscribe to a question', 'anspress-question-answer' ) ],
			) );
		}
		$user_id = get_current_user_id();
		$_post = ap_get_post( $post_id );

		// 먼저 이 포스트의 author가 나이면 안되고
		if ( $_post->post_author == $user_id ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => __( 'You can not add your own question into wishlist', 'anspress-question-answer' ) ],
			) );
		}

		// nonce check
		if ( 'question' === $_post->post_type && ! ap_verify_nonce( 'wish_' . $post_id ) ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => __( 'Sorry, unable to add this question into wishlist', 'anspress-question-answer' ) ],
			) );
		}

		// 이미 위시리스트에 담겼는지, 아닌지 체크해야지
		$exists = ap_has_user_wishlist( $post_id, $user_id );
		if ( $exists ) {
			ap_delete_wishlist( $post_id, $user_id );
			ap_ajax_json( array(
				'success' => true,
				'snackbar' => [ 'message' => __( 'Successfully deleted this question from wishlist', 'anspress-question-answer' ) ],
				'label' => __( 'Add to wishlist', 'anspress-question-answer' ),
				'status' => 'deleted'
			) );	
		}

		ap_add_wishlist( $post_id, $user_id );
		ap_ajax_json( array(
			'success' => true,
			'snackbar' => [ 'message' => __( 'Successfully add this question into wishlist', 'anspress-question-answer' ) ],
			'label' => __( 'Remove from wishlist', 'anspress-question-answer' ),
			'status' => 'added'
		) );
	}

	public static function add_wishlist_question_btn( $btns, $question_id ) {
		if ( is_user_logged_in() && is_question() ) {
			$_post = ap_get_post();
			$user_id = get_current_user_id();
			if ( $_post->post_author != $user_id ) {
				$btns['wish'] = ap_wish_btn( $_post, false );
			}
		}

		return $btns;
	}

	/**
	 * Adds reputations tab in AnsPress authors page.
	 */
	public static function ap_user_pages() {
		anspress()->user_pages[] = array(
			'slug'  => 'wishlist',
			'label' => __( 'Wishlist', 'anspress-question-answer' ),
			'icon'  => 'fas fa-heart',
			'cb'    => [ __CLASS__, 'wishlist_page' ],
			'order' => 6,
		);
	}

	/**
	 * Display reputation tab content in AnsPress author page.
	 */
	public static function wishlist_page() {
		$user_id = get_queried_object_id();
		$wishlist = ap_get_wishlist( $user_id );

		if ( ! empty( $wishlist ) ) {
			$args = array(
				'post__in' => $wishlist,
				'ap_current_user_ignore' => true,
				'showposts'           => 10
			);
			anspress()->questions = new \Question_Query( $args );	
		}

		ap_template_part( 'profile/wishlist' );
	}
}

AP_Wishlist::init();


function ap_get_wishlist( $user_id = false ) {
  if ( $user_id == false ) {
    $user_id = get_current_user_id();
  }
  $key   = 'wishlist_of_' . $user_id;
  $cache = wp_cache_get( $key, 'ap_wishlist' );
  
  if ( false !== $cache ) {
    return $cache;
  }
	$wishlist =  maybe_unserialize( get_user_meta( $user_id, 'ap_wishlist', true ) );

  return $wishlist;
}

function ap_has_user_wishlist( $post_id, $user_id = false ) {
	if ( $user_id == false ) {
		$user_id = get_current_user_id();
	}
	$wishlist =  ap_get_wishlist( $user_id );
	if ( $wishlist && in_array( $post_id, $wishlist, false ) ) {
		return true;
	}
	return false;
}

function ap_delete_wishlist( $post_id, $user_id = false ) {
	if ( $user_id == false ) {
		$user_id = get_current_user_id();
	}

	$wishlist = ap_get_wishlist( $user_id );
	$key = array_search($post_id, $wishlist);
	unset($wishlist[$key]);

	$serialized_wishlist = maybe_serialize( $wishlist );
	update_user_meta( $user_id, 'ap_wishlist', $serialized_wishlist );
	
	$key   = 'wishlist_of_' . $user_id;
	wp_cache_set( $key, $wishlist, 'ap_wishlist' );
}

/**
 * Output add wishlist button
 *
 * @param boolean $echo
 * @return void
 */
function ap_wish_btn( $_post = false, $echo = true ) {
	if ( ! $_post ) {
		$_post = ap_get_post();
	}
	$args = wp_json_encode(
		[
			'__nonce' => wp_create_nonce( 'wish_' . $_post->ID ),
			'id'      => $_post->ID,
			'ap_ajax_action' => 'wish'
		]
	);

	$wished = ap_has_user_wishlist( $_post->ID );
	$label = $wished ? '위시리스트에서 삭제' : '위시리스트에 추가';
	$html = '<button class="ap-btn ap-btn-wish ap-btn-big ' . ( $wished ? 'active' : '' ) . '" apwish apquery="' . esc_js( $args ) . '">' . esc_attr( $label ) . '</button>';

	if ( ! $echo ) {
		return $html;
	}

	echo $html; // WPCS: xss okay.
}

function ap_add_wishlist( $post_id, $user_id = false ) {
	if ( $user_id == false ) {
		$user_id = get_current_user_id();
	}

	$wishlist = ap_get_wishlist( $user_id );
	if ( ! is_array( $wishlist ) ) {
		$wishlist = array();
	}
	$wishlist[] = $post_id;

	$serialized_wishlist = maybe_serialize( $wishlist );
	update_user_meta( $user_id, 'ap_wishlist', $serialized_wishlist );

	$key   = 'wishlist_of_' . $user_id;
	wp_cache_set( $key, $wishlist, 'ap_wishlist' );
}