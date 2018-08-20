<?php
/**
 * AnsPresss admin ajax class
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * @package AnsPress
 */
class AP_Admin_Ajax {
	/**
	 * Initialize admin ajax
	 */
	public static function init() {
		self::includes();
		
		anspress()->add_action( 'ap_ajax_ap_admin_vote', __CLASS__, 'ap_admin_vote' );
		anspress()->add_action( 'ap_ajax_get_all_answers', __CLASS__, 'get_all_answers' );
		anspress()->add_action( 'wp_ajax_ap_uninstall_data', __CLASS__, 'ap_uninstall_data' );
		anspress()->add_action( 'wp_ajax_open_yas_table_modal', __CLASS__, 'open_yas_table_modal' );
	}
	
	public static function includes() {
		require_once 'functions.php';
	}
  
	/**
	 * Handle ajax vote in wp-admin post edit screen.
	 * Cast vote as guest user with ID 0, so that when this vote never get
	 * rest if user vote.
	 *
	 * @since 2.5
	 */
	public static function ap_admin_vote() {
		 
		$args = ap_sanitize_unslash( 'args', 'p' );

		if ( current_user_can( 'manage_options' ) && ap_verify_nonce( 'admin_vote' ) ) {
			$post = ap_get_post( $args[0] );

			if ( $post ) {
				$value  = 'up' === $args[1] ? true : false;
				$counts = ap_add_post_vote( $post->ID, 0, $value );
				echo esc_attr( $counts['votes_net'] );
			}
		}
		die();
	}

	/**
	 * Ajax callback to get all answers. Used in wp-admin post edit screen to show
	 * all answers of a question.
	 *
	 * @since 4.0
	 */
	public static function get_all_answers() {
		global $answers;
		
		$question_id = ap_sanitize_unslash( 'question_id', 'p' );
		$answers_arr = [];
    $answers     = ap_get_answers( [ 'question_id' => $question_id, 'showposts' => -1 ] );

		while ( ap_have_answers() ) :
			ap_the_answer();
			global $post, $wp_post_statuses;
			
      $answers_arr[] = array(
        'ID'        		=> get_the_ID(),
        'content'   		=> get_the_content(),
        'avatar'    		=> ap_get_author_avatar( 30 ),
        'author'    		=> ap_user_display_name( $post->post_author ),
        'editLink'  		=> esc_url_raw( get_edit_post_link() ),
				'trashLink' 		=> esc_url_raw( get_delete_post_link() ),
				'untrashLink'		=> esc_url_raw( ap_get_untrash_post_link( $post ) ),
				'deleteLink'		=> esc_url_raw( get_delete_post_link( $post->ID, '', true ) ),
				'cloneLink'			=> esc_url_raw( AP_Duplicator::get_duplicate_post_link( $post , 'display' ) ),
				'status'    		=> esc_attr( $post->post_status ),
				'status_label'	=> esc_attr( $wp_post_statuses[ $post->post_status ]->label ),
        'selected'  		=> ap_get_post_field( 'selected' ),
      );
		endwhile;

		wp_send_json( $answers_arr );

		wp_die();
	}

	/**
	 * Uninstall actions.
	 *
	 * @since 4.0.0
	 */
	public static function ap_uninstall_data() {
		check_ajax_referer( 'ap_uninstall_data', '__nonce' );

		$data_type  = ap_sanitize_unslash( 'data_type', 'r' );
		$valid_data = [ 'qa', 'answers', 'options', 'userdata', 'terms', 'tables', 'reactivate' ];

		global $wpdb;

		// Only allow super admin to delete data.
		if ( is_super_admin() && in_array( $data_type, $valid_data, true ) ) {
			$done = 0;

			if ( 'qa' === $data_type ) {

				$count = $wpdb->get_var( "SELECT count(*) FROM $wpdb->posts WHERE post_type='question' OR post_type='answer'" );
				$ids   = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type='question' OR post_type='answer' LIMIT 30" );

				foreach ( (array) $ids as $id ) {
					if ( false !== wp_delete_post( $id, true ) ) {
						$done++;
					}
				}

				wp_send_json(
					[
						'done'  => (int) $done,
						'total' => (int) $count,
					]
				);
			} elseif ( 'answers' === $data_type ) {

				$count = $wpdb->get_var( "SELECT count(*) FROM $wpdb->posts WHERE post_type='answer'" );
				$ids   = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type='answer' LIMIT 30" );

				foreach ( (array) $ids as $id ) {
					if ( false !== wp_delete_post( $id, true ) ) {
						$done++;
					}
				}

				wp_send_json(
					[
						'done'  => (int) $done,
						'total' => (int) $count,
					]
				);
			} elseif ( 'userdata' === $data_type ) {

				$upload_dir = wp_upload_dir();

				// Delete avatar folder.
				wp_delete_file( $upload_dir['baseurl'] . '/ap_avatars' );

				// Remove user roles.
				AP_Roles::remove_roles();

				// Delete vote meta.
				$wpdb->delete( $wpdb->usermeta, [ 'meta_key' => '__up_vote_casted' ], array( '%s' ) ); // @codingStandardsIgnoreLine
				$wpdb->delete( $wpdb->usermeta, [ 'meta_key' => '__down_vote_casted' ], array( '%s' ) ); // @codingStandardsIgnoreLine

				wp_send_json(
					[
						'done'  => 1,
						'total' => 0,
					]
				);
			} elseif ( 'options' === $data_type ) {

				delete_option( 'anspress_opt' );
				delete_option( 'anspress_reputation_events' );

				wp_send_json(
					[
						'done'  => 1,
						'total' => 0,
					]
				);
			} elseif ( 'terms' === $data_type ) {

				$question_taxo = (array) get_object_taxonomies( 'question', 'names' );
				$answer_taxo   = (array) get_object_taxonomies( 'answer', 'names' );

				$taxos = $question_taxo + $answer_taxo;

				foreach ( (array) $taxos as $tax ) {
					$terms = get_terms(
						array(
							'taxonomy'   => $tax,
							'hide_empty' => false,
							'fields'     => 'ids',
						)
					);

					foreach ( (array) $terms as $t ) {
						wp_delete_term( $t, $tax );
					}
				}

				wp_send_json(
					[
						'done'  => 1,
						'total' => 0,
					]
				);
			} elseif ( 'tables' === $data_type ) {

				$tables = [ $wpdb->ap_qameta, $wpdb->ap_votes, $wpdb->ap_views, $wpdb->ap_reputations ];

				foreach ( $tables as $table ) {
					$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
				}

				wp_send_json(
					[
						'done'  => 1,
						'total' => 0,
					]
				);
			} elseif ( 'reactivate' === $data_type ) {
				update_option( 'alpool_activate', 'no', true );
				wp_send_json(
					[
						'done'  => 1,
						'total' => 1,
					]
				);
			}
		}

		// Send empty JSON if nothing done.
		wp_send_json( [] );
	}

	public static function open_yas_table_modal() {
		$term_id = ap_isset_post_value( 'term_id' );
		if ( $term_id ) {
			if ( ap_verify_nonce( 'statistic_' . $term_id ) ) {
				$term_name = ap_isset_post_value( 'term_name' );
				$args = array(
					'term_id'		=>	$term_id,
					'term_name' => $term_name,
					'screen'		=> 'ap_statistic',
					'term_name' => ap_isset_post_value( 'term_name' )
				);
				AP_Statistic::display_yas_statistic_table( $args );
			}
		}

		wp_die();
	}

}