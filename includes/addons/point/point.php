<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once 'mycred-hooks.php';

/**
 * Reputation hooks.
 */
class AP_Point extends \AnsPress\Singleton {

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
				'user_page_title_point' => __( 'Point', 'anspress-question-answer' ),
				'user_page_slug_point'  => 'point',
			]
		);
	}

	public static function unset_useless_hooks( $installed ) {
		$default_hooks = ['affiliate', 'comments', 'deleted_content', 'link_click', 'logging_in', 'publishing_content', 'registration', 'site_visit', 'video_view', 'view_contents'];
		
		foreach( $default_hooks as $hook ) {
			if ( isset( $installed[$hook] ) ) {
				unset( $installed[$hook] );
			}
		}
	}

	public static function register_default_hooks( $installed ) {
		$installed['register'] = array(
			'title'       => __( 'Register', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user account is created', 'anspress-question-answer' ),
			'callback'    => array( 'Anspress\Point\Register' )
		);
		$installed['ask'] = array(
			'title'       => __( 'Ask', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user asks or delete a question', 'anspress-question-answer' ),
			'callback'    => array( 'Anspress\Point\Ask' )
		);
		$installed['answer'] = array(
			'title'       => __( 'Answer', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user asnwer to a question', 'anspress-question-answer' ),
			'callback'    => array( 'Anspress\Point\Answer' )
		);
		$installed['select_answer'] = array(
			'title'       => __( 'Select answer', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user select or unselect best answer', 'anspress-question-answer' ),
			'callback'    => array( 'Anspress\Point\Select_Answer' )
		);
		$installed['best_answer'] = array(
			'title'       => __( 'Best answer', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when answer is selected or cancelled as best', 'anspress-question-answer' ),
			'callback'    => array( 'Anspress\Point\Best_Answer' )
		);
		$installed['vote_up'] = array(
			'title'       => __( 'Vote up', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when question or answer get vote up from other user', 'anspress-question-answer' ),
			'callback'    => array( 'Anspress\Point\Vote_Up' )
		);
		$installed['vote_down'] = array(
			'title'       => __( 'Vote down', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when question or answer get vote down from other user', 'anspress-question-answer' ),
			'callback'    => array( 'Anspress\Point\Vote_Down' )
		);
		
		return $installed;
	}

	/**
	 * Append user reputations in display name.
	 *
	 * @param string $name User display name.
	 * @param array  $args Arguments.
	 * @return string
	 */
	public static function display_name( $name, $args ) {
		if ( $args['user_id'] > 0 ) {
			if ( $args['html'] ) {
				$point = mycred_get_users_balance( $args['user_id'] );
				return $name . '<a href="' . ap_user_link( $args['user_id'] ) . 'point" class="ap-user-point" title="' . __( 'Point', 'anspress-question-answer' ) . '">' . $point . '</a>';
			}
		}

		return $name;
	}

	/**
	 * Adds reputations tab in AnsPress authors page.
	 */
	public static function ap_user_pages() {
		anspress()->user_pages[] = array(
			'slug'  => 'point',
			'label' => __( 'Point', 'anspress-question-answer' ),
			'icon'  => 'apicon-point',
			'cb'    => [ __CLASS__, 'point_page' ],
			'order' => 6,
		);
	}

	/**
	 * Display reputation tab content in AnsPress author page.
	 */
	public static function reputation_page() {
		$user_id = get_queried_object_id();
		ap_template_part( 'point', null, array( 'user_id' => $user_id ) );
	}
}

// Initialize addon.
AP_Point::init();

function ap_get_point_icon_class( $log_entry ) {
	
	$icon_class = 'apicon-';
	switch( $log_entry->ref ) {
		case 'register':
			// $icon_class .= 'question';
			$icon_class = 'register';
		break;
		case 'ask':
			$icon_class .= 'question ask';
		break;
		case 'answer':
			$icon_class .= 'answer answer';
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
		break;
	}
	
	return $icon_class;
}

function ap_point_ref_content( $log_entry ) {
	if ( ! empty( $log_entry->ref_id ) ) {
		$post = get_post( $log_entry->ref_id );
		
		echo '<a class="ap-reputation-ref" href="' . esc_url( ap_get_short_link( [ 'ap_p' => $log_entry->ref_id ] ) ) . '">';
		if ( ! empty( $post->post_title ) ) {
			echo '<strong>' . esc_html( $post->post_title ) . '</strong>';
		}
		if ( ! empty( $post->post_content ) ) {
			echo '<p>' . esc_html( ap_truncate_chars( strip_tags( $post->post_content ), 200 ) ) . '</p>';
		}
		echo '</a>';
	}
}