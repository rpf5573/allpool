<?php

/**
 * Dynamic addon avatar.
 *
 * An AnsPress add-on to check and filter bad words in
 * question, answer and comments. Add restricted words
 * after activating addon.
 *
 * @author     Rahul Aryan <support@anspress.io>
 * @copyright  2014 AnsPress.io & Rahul Aryan
 * @license    GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link       https://anspress.io
 * @package    AnsPress
 * @subpackage Dynamic Avatar Addon
 *
 * @anspress-addon
 * Addon Name:    Dynamic Avatar
 * Addon URI:     https://anspress.io
 * Description:   Generate user avatar dynamically.
 * Author:        Rahul Aryan
 * Author URI:    https://anspress.io
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once 'class-generator.php';

/**
 * AnsPress avatar hook class.
 *
 * @since 4.1.8
 */
class AP_Avatar extends \AnsPress\Singleton {
	/**
	 * Refers to a single instance of this class.
	 *
	 * @var null|object
	 * @since 4.1.8
	 */
	public static $instance = null;

	/**
	 * Initialize the class.
	 */
	protected function __construct() {
		ap_add_default_options(
			[
				'avatar_font'  => 'Pacifico',
				'avatar_force' => true,
			]
		);
	}

	/**
	 * Override get_avatar.
	 *
	 * @param  string         $args         Avatar image.
	 * @param  integer|string $id_or_email  User id or email.
	 * @return string
	 */
	public static function get_avatar( $args, $id_or_email ) {
		$override = apply_filters( 'ap_pre_avatar_url', false, $args, $id_or_email );

		// Return if override is not false.
		if ( false !== $override ) {
			return $override;
		}

		$args['default'] = ap_generate_avatar( $id_or_email );

		// Set default avatar url.
		if ( ap_opt( 'avatar_force' ) ) {
			$args['url'] = ap_generate_avatar( $id_or_email );
		}

		return $args;
	}

	/**
	 * Ajax callback for clearing avatar cache.
	 */
	public static function clear_avatar_cache() {
		check_ajax_referer( 'clear_avatar_cache', '__nonce' );

		if ( current_user_can( 'manage_options' ) ) {
			WP_Filesystem();
			global $wp_filesystem;
			$upload_dir = wp_upload_dir();
			$wp_filesystem->rmdir( $upload_dir['basedir'] . '/ap_avatars', true );
			wp_die( 'success' );
		}

		wp_die( 'failed' );
	}
}

/**
 * Check if avatar exists already.
 *
 * @param integer $user_id User ID or name.
 * @return boolean
 */
function ap_is_avatar_exists( $user_id ) {
	$filename   = md5( $user_id );
	$upload_dir = wp_upload_dir();
	$avatar_dir = $upload_dir['basedir'] . '/ap_avatars';

	return file_exists( $avatar_dir . $filename . '.jpg' );
}

/**
 * Generate avatar.
 *
 * @param integer|string $user_id User ID or name.
 * @return string Link to generated avatar.
 */
function ap_generate_avatar( $user_id ) {
	$avatar = new AnsPress\Avatar\Generator( $user_id );
	$avatar->generate();

	return $avatar->fileurl();
}

// Init class.
AP_Avatar::init();