<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( 'iamport/iamportPaymentPlugin.php' );

/**
 * Reputation hooks.
 */
class AP_Point extends \AnsPress\Singleton {

	public static $mycred_type = 'mycred_point';

	public static $mycred_entry = array(
		'purchase_answers' 	=> '답변 구매',
		'point_charge'			=> '포인트 충전',
		'best_answer'				=> '내가 올린 답변이 채택됨',
		'vote_up_answer'		=> '추천을 받음'
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

	public static function install_iamport() {
		if ( class_exists( 'IamportPaymentPlugin' ) ) {
			IamportPaymentPlugin::iamport_activated();
		}
	}

	public static function mycred_creds( $user_id ) {
		$point = ap_get_user_point( $user_id );
		$href = ap_user_link( $user_id ) . 'point'; ?>
		<div class="ui right labeled button ap-user-point-creds" tabindex="0">
			<a class="ui mini button primary ap-primary" href="<?=$href?>"> 포인트 </a>
			<a class="ui basic left pointing label"><?=$point?></a>
		</div> <?php
	}	

	public static function purchase_answers_button_modal() {
		

		$question_id = get_the_ID();
		$count = ap_get_answers_count( $question_id );
		if ( $count == 0 ) { return; }

		$price = ap_get_question_price();

		

		$user_id = get_current_user_id();
		if ( $user_id ) {
			$post = ap_get_post();
			// admin can see all answers for free
			if ( ap_is_admin( $user_id ) ) { 
				return;
			}
			// no need to purchase answers again
			if ( ap_has_purchased_answers( $user_id, $question_id ) ) {
				return;
			}
			// no need to purchase answers if you are the author of this question
			if ( ( $post->post_type == 'question' && (int)($post->post_author) == $user_id ) ) {
				return false;
			}
			if ( $price == 0 ) {
				return;
			}
		}

		$args = array(
			'price' 				=> $price,
			'answer_count' 	=> $count,
			'question_id'		=> $question_id,
		);
		ap_template_part( 'purchase-answers','button', $args );
		ap_template_part( 'purchase-answers', 'modal', $args );
	}

	public static function point_charge_button( $user_id ) {
		$logged_in_user_id = get_current_user_id();
		if ( $logged_in_user_id == (int)$user_id ) {
			echo do_shortcode( '[iamport_payment_button title="포인트 충전" class="iamport_btn" description="아래 정보를 기입 후 결제진행해주세요." name="알풀 포인트 충전" amount="1000,3000,5000,10000" pay_method_list="card,trans,phone"]포인트 충전[/iamport_payment_button]' );
		}
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
				// hide answers if current user is not the author of this question
				if ( $post->post_author != $user_id ) {
					// hide answers if current user did not purchase answers or price is not zero
					if ( ( (int)$post->price != 0 ) && ( ! ap_has_purchased_answers( $user_id, $args['question_id'] ) ) ) {
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

		$user_link = ap_user_link( $user_id );
		$user_point = (int)ap_get_user_point( $user_id );
		$question_price = (int)$_post->price;
		$price = ap_get_rate_applied_point( $question_price, 'purchase_answers' );
		if ( $user_point <= $price ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => '포인트가 부족합니다. 마이페이지에서 충전해 주시기 바랍니다' ],
				'redirect' => $user_link
			) );
		} else {
			ap_update_user_point( 'purchase_answers', $user_id, -$price, $post_id );
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
		ap_template_part( 'profile/point', 'log', array( 'user_id' => $user_id ) );
	}

	public static function after_charge_point( $iamport_result ) {
		if ( $iamport_result->success ) {
			$user_id = get_current_user_id();
			if ( $iamport_result->data->status == 'paid' && $iamport_result->data->amount > 0 && $user_id ) {
				$point = (int)($iamport_result->data->amount);
				ap_update_user_point( 'point_charge', $user_id, $point );
			}
		}
	}

	public static function after_vote_up( $post_id, $counts ) {
		$post = ap_get_post( $post_id );
		if ( $post->post_type == 'answer' ) {
			$question = ap_get_post( $post->post_parent );
			$price = (int)$question->price;
			$reward = ap_get_rate_applied_point( $price, 'vote_up_answer' );
			if ( $reward > 0 ) {
				ap_update_user_point( 'vote_up_answer', (int)$post->post_author, $reward, $question->ID );
			}
		}
	}

	public static function after_select_answer( $post, $question_id ) {
		
		$question = ap_get_post( $question_id );
		
		if ( $post->post_type == 'answer' && $post->selected && $question->post_type == 'question' ) {
			$price = (int)$question->price;
			$reward = ap_get_rate_applied_point( $price, 'best_answer' );
			if ( $reward > 0 ) {
				ap_update_user_point( 'best_answer', (int)$post->post_author, $reward, $question->ID );
			}
		}
	}

	public static function mycred_after_general_setting() {
		
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
		case 'point_charge':
			$icon_class = 'fas fa-credit-card point-charge';
		break;
		case 'vote_up_answer':
			$icon_class .= 'thumb-up thumb-up answer';
			if ( isset( $log_entry->data['parent'] ) ) {
				$icon_class .= (' ' . $log_entry->data['parent']);
			}
		break;
		case 'best_answer':
			$icon_class .= 'check best_answer';
			break;
		case 'manual':
			$icon_class = 'fas fa-balance-scale manual';
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

function ap_update_user_point( $ref, $user_id, $point, $post_id = null ) {
	mycred_add( $ref, $user_id, $point, AP_Point::$mycred_entry[$ref], $post_id, null, AP_Point::$mycred_type );
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

function ap_get_rate_applied_point( $price, $ref ) {
	$opt = ap_opt();
	$rate = $opt[$ref] * 0.01; // 30% = 0.3
	return round( round($price * 0.1) * $rate ) * 10;
}