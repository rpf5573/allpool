<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// require_once( 'iamport/iamportPaymentPlugin.php' );

/**
 * Reputation hooks.
 */
class AP_Point extends \AnsPress\Singleton {

	public static $mycred_type = 'mycred_point';

	public static $mycred_entry = array(
		'purchase_answers' 	=> '답변 구매',
		'point_charge'	=> '포인트 충전'
	);

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
	protected function __construct() {
		ap_add_default_options(
			[
				'user_page_title_point' => __( '포인트', 'anspress-question-answer' ),
				'user_page_slug_point'  => 'point',
			]
		);
	}

	public static function unset_useless_hooks( $installed, $type ) {
		if ( $type == 'mycred_point' ) {
			$default_hooks = ['affiliate', 'comments', 'deleted_content', 'link_click', 'logging_in', 'publishing_content', 'registration', 'site_visit', 'video_view', 'view_contents'];
			foreach( $default_hooks as $hook ) {
				if ( isset( $installed[$hook] ) ) {
					unset( $installed[$hook] );
				}
			}
		}
	}

	public static function register_hooks( $installed, $type ) {
		if ( $type == 'mycred_point' ) {	
		}
		return $installed;
	}

	public static function display_name( $name, $args ) {
		// show point only in profile page
		if ( $args['user_id'] > 0 ) {
			$point = ap_get_user_point( $args['user_id'] );
			if ( isset($args['is_profile']) && $args['is_profile'] && $args['html'] ) {
				$href = ap_user_link( $args['user_id'] ) . 'point';
				$name .= 
				'<div class="ui right labeled button ap-user-point" tabindex="0">
					<a class="ui button primary" href="' . $href . '"> 포인트 </a>
					<a class="ui basic left pointing label"> ' . $point . ' </a>
				</div>';
			} else {
				$name .= '<span class="ap-user-point-creds mini-creds">' . $point . '</span>';
			}
		}
		return $name;
	}

	public static function purchase_answers_button_modal() {
		$question_id = get_the_ID();
		$count = ap_get_answers_count( $question_id );
		if ( $count == 0 ) { return; }

		$user_id = get_current_user_id();
		if ( $user_id ) {
			if ( ap_is_admin( $user_id ) || ap_has_purchased_answers( $user_id, $question_id ) ) { 
				return false;
			}
		}
		$args = array(
			'price' 				=> ap_get_question_price(),
			'answer_count' 	=> $count,
			'question_id'		=> $question_id,
		);
		ap_template_part( 'buy-answers','button', $args );
		ap_template_part( 'buy-answers', 'modal', $args );
	}

	/**
	 * This function is for only loggedin user.
	 * Because un-loggedin user can see answer originally. - see 'ap_user_can_read_answer()'
	 *
	 * @param [type] $args
	 * @return void
	 */
	public static function answers_query_args( $args ) {
		global $post;
		if ( $post && $post->post_type == 'question' && is_singular( 'question' ) ) {
			$user_id = get_current_user_id();
			if ( $user_id && isset( $args['question_id'] ) && $args['question_id'] > 0 && ! ap_is_admin( $user_id ) ) {
				// show answers if current user is this question's author
				if ( $post->post_author != $user_id ) {
					// show answers if current user has purchased this question's answers
					if ( ! ap_has_purchased_answers( $user_id, $args['question_id'] ) ) {
						$args['author'] = $user_id;
					}
				}
			}
		}
		return $args;
	}

	public static function ajax_purchase_answers() {
		$post_id = (int) ap_sanitize_unslash( 'id', 'r' );
		

		// 로그인 한 사람만 담을 수 있어
		if ( ! is_user_logged_in() ) {
			$login_url = tml_get_action('login')->get_url();
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => '로그인이 필요합니다' ],
				'redirect' => $login_url
			) );
		}
		$user_id = get_current_user_id();
		$_post = ap_get_post( $post_id );

		// nonce check
		if ( 'question' === $_post->post_type && ! ap_verify_nonce( 'purchase_answers_of_' . $post_id ) ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => '알수없는 에러가 발생했습니다' ],
			) );
		}

		if ( ap_has_purchased_answers( $user_id, $post_id ) ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => '이미 구매하신 답변입니다' ],
			) );
		}

		$user_point = (int)ap_get_user_point( $user_id );
		$question_price = (int)$_post->price;
		if ( $user_point < $question_price ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => '포인트가 부족합니다. 마이페이지에서 충전해 주시기 바랍니다' ],
			) );
		} else {
			ap_update_user_point( 'purchase_answers', $user_id, -$question_price, $post_id );
			ap_update_purchased_answers( $user_id, $post_id );
			ap_ajax_json( array(
				'success' => true,
				'snackbar' => [ 'message' => '성공적으로 구매하였습니다' ],
				'redirect' => get_permalink( $post_id )
			) );
		}

	}

	/**
	 * Adds reputations tab in AnsPress authors page.
	 */
	public static function ap_user_pages() {
		anspress()->user_pages[] = array(
			'slug'  => 'point',
			'label' => __( '포인트', 'anspress-question-answer' ),
			'icon'  => 'fas fa-dollar-sign',
			'cb'    => [ __CLASS__, 'point_page' ],
			'order' => 6,
		);
	}

	/**
	 * Display reputation tab content in AnsPress author page.
	 */
	public static function point_page() {
		$user_id = get_queried_object_id();
		ap_template_part( 'profile/point', 'charge', array( 'user_id' => $user_id ) );
		ap_template_part( 'profile/point', 'log', array( 'user_id' => $user_id ) );
	}
	
}

// Initialize addon.
AP_Point::init();

function ap_get_point_icon_class( $log_entry ) {

	$icon_class = 'apicon-';
	switch( $log_entry->ref ) {
		case 'purchase_answers':
			$icon_class = 'fas fa-cart-plus purchase-answers';
		break;
		case 'vote_up':
			$icon_class .= 'thumb-up';
			if ( isset( $log_entry->data['parent'] ) ) {
				$icon_class .= (' ' . $log_entry->data['parent']);
			}
		break;
		case 'vote_down':
			$icon_class .= 'thumb-down';
			if ( isset( $log_entry->data['parent'] ) ) {
				$icon_class .= (' ' . $log_entry->data['parent']);
			}
		break;
		case 'best_answer':
			$icon_class .= 'check best_answer';
		case 'manual':
			$icon_class = 'manual';
		break;
	}
	
	return $icon_class;
}

function ap_point_ref_content( $log_entry ) {
	if ( ! empty( $log_entry->ref_id ) ) {
		$post = get_post( $log_entry->ref_id );
		
		echo '<a class="ap-point-log-ref ap-user-mycred-log-ref" href="' . esc_url( ap_get_short_link( [ 'ap_p' => $log_entry->ref_id ] ) ) . '">';
		if ( ! empty( $post->post_title ) ) {
			echo '<strong>' . esc_html( $post->post_title ) . '</strong>';
		}
		if ( ! empty( $post->post_content ) ) {
			echo '<p>' . esc_html( ap_truncate_chars( strip_tags( $post->post_content ), 200 ) ) . '</p>';
		}
		echo '</a>';
	}
}

function ap_get_user_point( $user_id ) {
	return mycred_get_users_balance( $user_id, AP_Point::$mycred_type );
}
function ap_update_user_point( $ref, $user_id, $point, $question_id = null ) {
	mycred_add( $ref, $user_id, $point, AP_Point::$mycred_entry[$ref], $question_id, null, AP_Point::$mycred_type );
}

function ap_get_purchased_answers( $user_id ) {
	$result = maybe_unserialize( get_user_meta( $user_id, 'purchased_answers', true ) ); 
	if ( ! is_array( $result ) ) {
		$result = array();
	}
	return $result;
}

function ap_has_purchased_answers( $user_id, $question_id ) {
	$purchased_answers = ap_get_purchased_answers( $user_id );
	if ( is_array( $purchased_answers ) && in_array( $question_id, $purchased_answers ) ) {
		return true;
	}
	return false;
}

function ap_update_purchased_answers( $user_id, $question_id ) {
	$purchased_answers = ap_get_purchased_answers( $user_id );
	$purchased_answers[] = $question_id;
	update_user_meta( $user_id, 'purchased_answers', maybe_serialize( $purchased_answers ) );
}