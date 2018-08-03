<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once 'mycred-hooks.php';

/**
 * Reputation hooks.
 */
class AP_Reputation extends \AnsPress\Singleton {

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
				'user_page_title_reputations' => __( '전문가지수', 'anspress-question-answer' ),
				'user_page_slug_reputations'  => 'reputations',
			]
		);
	}

	public static function unset_useless_hooks( $installed, $type ) {
		if ( $type == 'mycred_default' ) {
			$default_hooks = ['affiliate', 'comments', 'deleted_content', 'link_click', 'logging_in', 'publishing_content', 'registration', 'site_visit', 'video_view', 'view_contents'];
			foreach( $default_hooks as $hook ) {
				if ( isset( $installed[$hook] ) ) {
					unset( $installed[$hook] );
				}
			}
		}
	}

	public static function register_hooks( $installed, $type ) {
		if ( $type == 'mycred_default' ) {
			$installed['register'] = array(
				'title'       => __( 'Register', 'anspress-question-answer' ),
				'description' => __( '회원가입 했을때 얻는 포인트 입니다', 'anspress-question-answer' ),
				'callback'    => array( 'Anspress\Reputation\Register' )
			);
			$installed['ask'] = array(
				'title'       => __( 'Ask', 'anspress-question-answer' ),
				'description' => __( 'Points awarded when user asks or delete a question', 'anspress-question-answer' ),
				'callback'    => array( 'Anspress\Reputation\Ask' )
			);
			$installed['answer'] = array(
				'title'       => __( 'Answer', 'anspress-question-answer' ),
				'description' => __( 'Points awarded when user asnwer to a question', 'anspress-question-answer' ),
				'callback'    => array( 'Anspress\Reputation\Answer' )
			);
			$installed['select_answer'] = array(
				'title'       => __( 'Select answer', 'anspress-question-answer' ),
				'description' => __( 'Points awarded when user select or unselect best answer', 'anspress-question-answer' ),
				'callback'    => array( 'Anspress\Reputation\Select_Answer' )
			);
			$installed['best_answer'] = array(
				'title'       => __( 'Best answer', 'anspress-question-answer' ),
				'description' => __( 'Points awarded when answer is selected or cancelled as best', 'anspress-question-answer' ),
				'callback'    => array( 'Anspress\Reputation\Best_Answer' )
			);
			$installed['vote_up'] = array(
				'title'       => __( 'Vote up', 'anspress-question-answer' ),
				'description' => __( 'Points awarded when question or answer get vote up from other user', 'anspress-question-answer' ),
				'callback'    => array( 'Anspress\Reputation\Vote_Up' )
			);
			$installed['vote_down'] = array(
				'title'       => __( 'Vote down', 'anspress-question-answer' ),
				'description' => __( 'Points awarded when question or answer get vote down from other user', 'anspress-question-answer' ),
				'callback'    => array( 'Anspress\Reputation\Vote_Down' )
			);
		}
		
		return $installed;
	}

	public static function mycred_creds( $user_id ) {
		$reputation = mycred_get_users_balance( $user_id );
		$href = ap_user_link( $user_id ) . 'reputations'; ?>
		<div class="ui right labeled button ap-user-reputation-creds" tabindex="0">
			<a class="ui mini button" href="<?=$href?>"> 전문가지수 </a>
			<a class="ui basic left pointing label"><?=$reputation?></a>
		</div> <?php
	}	

	public static function display_name( $name, $args ) {
		$query_var = get_query_var( 'ap_page', false );
		if ( $args['user_id'] > 0 && $query_var != 'user' ) {
			$reputation = mycred_get_users_balance( $args['user_id'] );
			$name .= '<span class="ap-user-reputation-creds mini-creds">' . $reputation . '</span>';
		}
		return $name;
	}

	/**
	 * Adds reputations tab in AnsPress authors page.
	 */
	public static function ap_user_pages() {
		anspress()->user_pages[] = array(
			'slug'  => 'reputations',
			'label' => __( '전문가지수', 'anspress-question-answer' ),
			'icon'  => 'apicon-reputation',
			'cb'    => [ __CLASS__, 'reputation_page' ],
			'order' => 5,
		);
	}

	/**
	 * Display reputation tab content in AnsPress author page.
	 */
	public static function reputation_page() {
		$user_id = get_queried_object_id();
		ap_template_part( 'profile/reputation', 'log', array( 'user_id' => $user_id ) );
	}
	
}

// Initialize addon.
AP_Reputation::init();


function ap_get_reputation_icon_class( $log_entry ) {

	
	// 
	
	// use apicon and fontawesome both
	$icon_class = '';
	switch( $log_entry->ref ) {
		case 'register':
			// $icon_class .= 'question';
			$icon_class = 'fas fa-user-alt register';
		break;
		case 'ask':
			$icon_class = 'apicon-question ask';
		break;
		case 'answer':
			$icon_class = 'apicon-answer answer';
		break;
		case 'vote_up':
			$icon_class = 'apicon-thumb-up thumb-up';
			if ( isset( $log_entry->data['parent'] ) ) {
				$icon_class .= (' ' . $log_entry->data['parent']);
			}
		break;
		case 'vote_down':
			$icon_class = 'apicon-thumb-down thumb-down';
			if ( isset( $log_entry->data['parent'] ) ) {
				$icon_class .= (' ' . $log_entry->data['parent']);
			}
		break;
		case 'best_answer':
			$icon_class = 'fas fa-medal best_answer';
			break;
		case 'manual':
			$icon_class = 'fas fa-balance-scale manual';
			break;
		case 'select_answer':
			$icon_class = 'apicon-check select_answer';
			break;
	}

	if ( $log_entry->data && isset( $log_entry->data['type'] ) && $log_entry->data['type'] == 'undo' ) {
		$icon_class .= ' undo';
	}
	
	return $icon_class;
}

function ap_reputation_ref_content( $log_entry ) {
	if ( ! empty( $log_entry->ref_id ) ) {
		$post = get_post( $log_entry->ref_id );
		
		echo '<a class="ap-user-reputation-log-ref ap-user-mycred-log-ref" href="' . esc_url( ap_get_short_link( [ 'ap_p' => $log_entry->ref_id ] ) ) . '">';
		if ( ! empty( $post->post_title ) ) {
			echo '<strong>' . esc_html( $post->post_title ) . '</strong>';
		}
		if ( ! empty( $post->post_content ) ) {
			echo '<p>' . esc_html( ap_truncate_chars( strip_tags( $post->post_content ), 200 ) ) . '</p>';
		}
		echo '</a>';
	}
}