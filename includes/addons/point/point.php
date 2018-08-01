<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

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
		\PC::debug( ['args' => $args], __FUNCTION__ );
		if ( $args['user_id'] > 0 ) {
			if ( $args['html'] ) {
				$point = mycred_get_users_balance( $args['user_id'] );
				return $name . '<a href="' . ap_user_link( $args['user_id'] ) . 'point" class="ap-user-point" title="' . __( '포인트', 'anspress-question-answer' ) . '">' . $point . '</a>';
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
		ap_template_part( 'point', 'charge', array( 'user_id' => $user_id ) );
		ap_template_part( 'point', null, array( 'user_id' => $user_id ) );
	}
	
}

// Initialize addon.
AP_Reputation::init();


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
		case 'manual':
			$icon_class = 'manual';
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