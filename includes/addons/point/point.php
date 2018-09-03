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
		'ask_question'							=> '질문 생성',
		'edit_question_point'   		=> '질문 포인트 수정',
		'purchase_answers' 					=> '답변 구매',
		'point_charge'							=> '포인트 충전',
		'selected_best_answer'			=> '베스트 답변으로 채택됨',
		'unselected_best_answer'		=> '베스트 답변 채택 취소',
		'vote_up_answer'						=> '추천을 받음',
		'delete_question'						=> '질문 삭제(포인트 회수)',
		'trash_question'						=> '질문 휴지통으로 이동',
		'untrash_question'					=> '질문 복구'
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
				'user_page_title_purchased_question' => '구매 리스트',
				'user_page_slug_purchased_question' => 'purchased_question'
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
			// IamportPaymentPlugin::iamport_activated();
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

	public static function point_charge_button() {
		echo do_shortcode( '[iamport_payment_button title="포인트 충전" class="iamport_btn" description="아래 정보를 기입 후 결제진행해주세요." name="알풀 포인트 충전" amount="1000,3000,5000,10000" pay_method_list="card,trans,phone"]포인트 충전[/iamport_payment_button]' );
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
		if ( 'question' !== $_post->post_type || ! ap_verify_nonce( 'purchase_answers_of_' . $post_id ) ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => '알수없는 에러가 발생했습니다' ],
			) );
		}

		if ( ap_has_purchased_answers( $user_id, $post_id ) ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => '이미 구매하신 답변입니다' ],
				'redirect' => get_permalink( $post_id )
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
			ap_update_sold_count( $post_id );
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

		anspress()->user_pages[] = array(
			'slug' => 'purchased_question',
			'label' => '구매 리스트',
			'icon'  => 'fas fa-cart-arrow-down',
			'cb'    => [ __CLASS__, 'purchased_question_page' ],
			'order' => 9,
		);
	}

	/**
	 * Display reputation tab content in AnsPress author page.
	 */
	public static function point_page() {
		$user_id = get_queried_object_id();
		$logged_in_user_id = get_current_user_id();
		// only show to owner of this page
		if ( $user_id == $logged_in_user_id ) {
			ap_template_part( 'profile/point', 'log', array( 'user_id' => $user_id ) );
		} else {
			ap_template_part( 'message', null, array(
				'type' => 'error',
				'header' => '잠시만요',
				'body' => '다른 사용자의 포인트 기록은 확인하실 수 없습니다'
			) );
		}
	}

	public static function purchased_question_page() {
		$user_id = get_queried_object_id();
		$purchased_answer_ids = ap_get_purchased_answers( $user_id );

		if ( ! empty( $purchased_answer_ids ) ) {
			$args = array(
				'post__in' => $purchased_answer_ids
			);
			anspress()->questions = new \Question_Query( $args );
	
			ap_template_part( 'profile/questions', null, array( 'user_id' => $user_id ) );
		}

	}

	public static function save_price_from_admin( $qameta, $post, $updated ) {
    $acf = ap_isset_post_value( 'acf', false );
    if ( $acf ) {
      if ( isset( $acf['price'] ) ) {
        $qameta['price'] = (int) $acf['price'];
      }
		}
    return $qameta;
  }

	/*  myCRED Hooks
	/* --------------------------------------------------- */
	public static function after_charge_point( $ready, $order_status, $order, $result ) {
		$user_id = get_current_user_id();
		if ( $result->__get('status') == 'paid' && $result->__get('amount') > 0 && $user_id ) {
			$point = (int)($result->__get('amount'));
			ap_update_user_point( 'point_charge', $user_id, $point );
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

	public static function selected_best_answer( $post, $question_id ) {
		$question = ap_get_post( $question_id );
		$price = (int)$question->price;
		$reward = ap_get_rate_applied_point( $price, 'best_answer' );
		if ( $reward > 0 ) {
			$by = '';
			if ( is_admin() && ! wp_doing_ajax() ) {
				$by = ' - 관리자권한';
			}
			$answer_id = $question->selected_id;
			ap_update_user_point( 'selected_best_answer', (int)$post->post_author, $reward, $answer_id, $by );
		}
	}

	public static function unselected_best_answer( $post, $question_id ) {
		$question = ap_get_post( $question_id );
		$price = (int)$question->price;
		$reward = ap_get_rate_applied_point( $price, 'best_answer' );
		if ( $reward > 0 ) {
			$by = '';
			if ( is_admin() && ! wp_doing_ajax() ) {
				$by = ' - 관리자권한';
			}
			$answer_id = $post->ID;
			
			ap_update_user_point( 'unselected_best_answer', (int)$post->post_author, -$reward, $answer_id, $by );
		}
	}

	public static function recover_point_after_delete_empty_question( $post_id ) {
		$answer_ids = ap_get_answer_ids( $post_id );
		$post = ap_get_post( $post_id );
		if ( ! $answer_ids && (int)$post->price > 0 ) {
			ap_update_user_point( 'delete_question', (int)$post->post_author, (int)$post->price, $post_id );
		}
	}

	public static function recover_point_after_trash_question_on_admin( $post_id, $post ) {
		
		if ( (int)$post->price > 0 ) {
			ap_update_user_point( 'trash_question', (int)$post->post_author, (int)$post->price, $post_id, ' - 관리자권한' );
		}
	}

	public static function reuse_point_after_untrash_question( $post_id, $post ) {
		if ( (int)$post->price > 0 ) {
			ap_update_user_point( 'untrash_question', (int)$post->post_author, -((int)$post->price), $post_id, ' - 관리자권한' );
		}
	}

	public static function use_point_on_asking( $qameta, $post, $updated ) {

		$values = anspress()->get_form( 'question' )->get_values();
		if ( isset( $values['price'] ) && $values['price']['value'] ) {
      $qameta['price'] = (int) $values['price']['value'];
      $user_id = get_current_user_id();
      if ( $user_id ) {
        // refund point
        if ( $updated ) {
          $original_price = (int) ap_get_post_field( 'price', $post );
          if ( $original_price > 0 && $qameta['price'] > 0 && $original_price != $qameta['price'] ) {
            ap_update_user_point( 'edit_question_point', $user_id, $original_price - $qameta['price'], $post->ID );
          }
        } else {
          // minus point of asker
          ap_update_user_point( 'ask_question', $user_id, -$qameta['price'], $post->ID );
        }
      }
		}
		
		return $qameta;
	}
	
}

// Initialize addon.
AP_Point::init();

function ap_update_user_point( $ref, $user_id, $point, $post_id = null, $by = '' ) {
	$data = null;
	if ( $post_id ) {
		$_post = ap_get_post( $post_id );
		
		if ( $_post ) {
			$data = array(
				'post_title' 		=> $_post->post_title,
				'post_content' 	=> esc_html( ap_truncate_chars( strip_tags( $_post->post_content ), 200 ) ),
				'ptype'					=> $_post->post_type
			);
		}
	}
	mycred_add( $ref, $user_id, $point, AP_Point::$mycred_entry[$ref] . $by, $post_id, $data, AP_Point::$mycred_type );
}

function ap_get_point_icon_class( $log_entry ) {
	
	$icon_class = 'apicon-';
	switch( $log_entry->ref ) {
		case 'delete_question';
			$icon_class .= 'question ask undo';
		break;
		case 'edit_question_point':
			$icon_class = 'fas fa-edit edit_question_point';
		break;
		case 'ask_question':
			$icon_class .= 'question ask';
		break;
		case 'purchase_answers':
			$icon_class = 'fas fa-cart-plus purchase-answers';
		break;
		case 'point_charge':
			$icon_class = 'fas fa-credit-card point-charge';
		break;
		case 'vote_up_answer':
			$icon_class .= 'thumb-up thumb-up answer';
			if ( isset( $log_entry->data['ptype'] ) ) {
				$icon_class .= (' ' . $log_entry->data['ptype']);
			}
		break;
		case 'selected_best_answer':
			$icon_class = 'fas fa-medal best_answer';
		break;
		case 'unselected_best_answer':
			$icon_class = 'fas fa-medal best_answer undo';
		break;
		case 'trash_question':
			$icon_class .= 'question ask undo';
		break;
		case 'untrash_question':
			$icon_class .= 'question ask';
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
		// trash , delete have no link
		if ( $log_entry->data && ( is_null( $post ) || $post->post_status == 'trash' ) ) { ?>
			<div class="ap-user-point-log-ref ap-user-mycred-log-ref"> <?php 
				if ( $log_entry->data['ptype'] == 'question'  ) { ?>
					<strong><?php echo $log_entry->data['post_title']; ?> </strong> <?php
				} ?>
				<p><?php echo $log_entry->data['post_content']; ?></p>
			</div> <?php
			return;
		}
		
		echo '<a class="ap-point-log-ref ap-user-mycred-log-ref" href="' . esc_url( ap_get_short_link( [ 'ap_p' => $log_entry->ref_id ] ) ) . '">';
		if ( ! empty( $post->post_title ) && $post->post_type != 'answer' ) {
			echo '<strong>' . esc_html( $post->post_title ) . '</strong>';
		}
		if ( ! empty( $post->post_content ) ) {
			echo '<p>' . esc_html( ap_truncate_chars( strip_tags( $post->post_content ), 200 ) ) . '</p>';
		}
		echo '</a>';
	}
}

function ap_get_user_point( $user_id ) {
	return (int) mycred_get_users_balance( $user_id, AP_Point::$mycred_type );
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

function ap_update_sold_count( $question_id ) {
	global $wpdb;
	$prefix = $wpdb->prefix;
	$sql = "UPDATE {$prefix}ap_qameta SET sold_count = sold_count + 1 WHERE post_id = {$question_id}";
	$result = $wpdb->query( $sql );
}

function ap_get_rate_applied_point( $price, $ref ) {
	$opt = ap_opt();
	$rate = $opt[$ref] * 0.01; // 30% = 0.3
	return round( round($price * 0.1) * $rate ) * 10;
}