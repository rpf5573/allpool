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
				'user_page_title_reputations' => __( 'Reputations', 'anspress-question-answer' ),
				'user_page_slug_reputations'  => 'reputations',
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
		
		return $installed;
	}

	/**
	 * Save reputation events.
	 */
	public static function ap_save_events() {
		check_ajax_referer( 'ap-save-events', '__nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$events_point = ap_isset_post_value( 'events', 'r' );
		$points       = [];

		foreach ( ap_get_reputation_events() as $slug => $event ) {
			if ( isset( $events_point[ $slug ] ) ) {
				$points[ sanitize_text_field( $slug ) ] = (int) $events_point[ $slug ];
			}
		}

		if ( ! empty( $points ) ) {
			update_option( 'anspress_reputation_events', $points );
		}

		echo '<div class="notice notice-success is-dismissible"><p>' . esc_attr__( 'Successfully updated reputation points!', 'anspress-question-answer' ) . '</p></div>';

		wp_die();
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
				$reputation = mycred_get_users_balance( $args['user_id'] );
				return $name . '<a href="' . ap_user_link( $args['user_id'] ) . 'reputations" class="ap-user-reputation" title="' . __( 'Reputation', 'anspress-question-answer' ) . '">' . $reputation . '</a>';
			}
		}

		return $name;
	}

	/**
	 * Adds reputations tab in AnsPress authors page.
	 */
	public static function ap_user_pages() {
		anspress()->user_pages[] = array(
			'slug'  => 'reputations',
			'label' => __( 'Reputations', 'anspress-question-answer' ),
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
		ap_template_part( 'reputation', null, array( 'user_id' => $user_id ) );
	}
}

// Initialize addon.
AP_Reputation::init();


function ap_get_reputation_icon_class( $log_entry ) {
	
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

function ap_reputation_ref_content( $log_entry ) {
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