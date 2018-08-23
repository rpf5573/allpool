<?php
/**
 * Installation and activation of anspress, register hooks that are fired when the plugin is activated.
 *
 * @package     AnsPress
 * @copyright   Copyright (c) 2013, Rahul Aryan
 * @license     https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @since       0.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activate AnsPress.
 */
class AP_Activate {
	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Char set.
	 *
	 * @var string
	 */
	public $charset_collate;

	/**
	 * Tables
	 *
	 * @var array
	 */
	public $tables = array();

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			anspress(); // include files
			self::$instance          = new self();
		}

		return self::$instance;
	}

	/**
	 * Construct class.
	 */
	public function __construct() {
		// Append table names in $wpdb.
		ap_append_table_names();

		$this->activate();

		update_option( 'alpool_activate', 'yes' );
	}

	/**
	 * Ap_qameta table.
	 *
	 * @since 4.1.8 Added primary key.
	 */
	public function qameta_table() {
		global $wpdb;

		// @codingStandardsIgnoreLine
		$this->tables[] = 'CREATE TABLE ' . $wpdb->ap_qameta . ' (
			post_id bigint(20) NOT NULL,
			selected_id bigint(20) DEFAULT NULL,
			answers bigint(20) DEFAULT 0,
			ptype varchar(100) DEFAULT NULL,
			selected tinyint(1) DEFAULT 0,
			votes_up bigint(20) DEFAULT 0,
			votes_down bigint(20) DEFAULT 0,
			views bigint(20) DEFAULT 0,
			closed tinyint(1) DEFAULT 0,
			terms LONGTEXT DEFAULT NULL,
			year smallint(20) DEFAULT 0,
			session tinyint(1) DEFAULT 0,
			inspection_check tinyint(1) DEFAULT 0,
			price bigint(20) DEFAULT 0,
			sold_count bigint(20) DEFAULT 0,
			attach LONGTEXT DEFAULT NULL,
			activities LONGTEXT DEFAULT NULL,
			fields LONGTEXT DEFAULT NULL,
			roles varchar(100) DEFAULT NULL,
			last_updated timestamp NULL DEFAULT NULL,
			PRIMARY KEY  (post_id)
		)' . $this->charset_collate . ';';
	}

	/**
	 * AnsPress ap_votes table.
	 */
	public function votes_table() {
		global $wpdb;

		$this->tables[] = 'CREATE TABLE ' . $wpdb->ap_votes . ' (
				vote_id bigint(20) NOT NULL AUTO_INCREMENT,
				vote_post_id bigint(20) NOT NULL,
				vote_user_id bigint(20) NOT NULL,
				vote_rec_user bigint(20) NOT NULL,
				vote_type varchar(100) DEFAULT NULL,
				vote_value varchar(100) DEFAULT NULL,
				vote_date timestamp NULL DEFAULT NULL,
				PRIMARY KEY  (vote_id),
				KEY vote_post_id (vote_post_id)
			)' . $this->charset_collate . ';';
	}

	/**
	 * AnsPress views table.
	 */
	public function views_table() {
		global $wpdb;

		$this->tables[] = 'CREATE TABLE ' . $wpdb->ap_views . ' (
				view_id bigint(20) NOT NULL AUTO_INCREMENT,
				view_user_id bigint(20) DEFAULT NULL,
				view_type varchar(100) DEFAULT NULL,
				view_ref_id bigint(20) DEFAULT NULL,
				view_ip varchar(39),
				view_date timestamp NULL DEFAULT NULL,
				PRIMARY KEY  (view_id),
				KEY view_user_id (view_user_id)
			)' . $this->charset_collate . ';';
	}

	/**
	 * Insert and update tables
	 */
	public function insert_tables() {
		global $wpdb;
		$this->charset_collate = ! empty( $wpdb->charset ) ? 'DEFAULT CHARACTER SET ' . $wpdb->charset : '';

		$this->qameta_table();
		$this->votes_table();
		$this->views_table();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		if ( count( $this->tables ) > 0 ) {
			foreach ( $this->tables as $table ) {
				dbDelta( $table );
			}
		}
	}

	/**
	 * Create base pages, add roles, add caps and create tables
	 */
	public function activate() {
		// add roles.
		$ap_roles = new AP_Roles();
		$ap_roles->remove_default_roles();
		$ap_roles->add_roles();
		$ap_roles->add_capabilities();

		AP_Point::install_iamport();
		
		$this->insert_tables();
		update_option( 'anspress_db_version', AP_DB_VERSION );
		update_option( 'anspress_opt', get_option( 'anspress_opt' ) + ap_default_options() );

		// Create main pages.
		ap_create_base_page();

		ap_opt( 'ap_flush', 'true' );
	}
  
}