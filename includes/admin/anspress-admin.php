<?php
/**
 * AnsPresss admin class
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
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
 * @author  Rahul Aryan <support@anspress.io>
 */
class AP_Admin {

	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * AnsPress option key.
	 *
	 * @var string
	 */
	protected $option_name = 'anspress_opt';

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 */
	public static function init() {
		self::includes();

		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . 'qapress.php' );
		anspress()->add_filter( 'plugin_action_links_' . $plugin_basename, __CLASS__, 'add_action_links' );
		anspress()->add_action( 'save_post', __CLASS__, 'ans_parent_post', 10, 2 );
		anspress()->add_action( 'trashed_post', __CLASS__, 'trashed_post', 10, 2 );
		anspress()->add_action( 'admin_enqueue_scripts', __CLASS__, 'enqueue_admin_styles' );
		anspress()->add_action( 'admin_enqueue_scripts', __CLASS__, 'enqueue_admin_scripts' );
		anspress()->add_action( 'admin_menu', __CLASS__, 'add_plugin_admin_menu' );
		anspress()->add_action( 'parent_file', __CLASS__, 'fix_active_admin_menu', 1000 );
		anspress()->add_action( 'admin_init', __CLASS__, 'init_actions' );
		anspress()->add_action( 'parent_file', __CLASS__, 'tax_menu_correction' );
		anspress()->add_action( 'load-post.php', __CLASS__, 'question_meta_box_class' );
		anspress()->add_action( 'load-post-new.php', __CLASS__, 'question_meta_box_class' );
		anspress()->add_action( 'admin_menu', __CLASS__, 'change_post_menu_label' );
		anspress()->add_filter( 'wp_insert_post_data', __CLASS__, 'post_data_check', 99 );
		anspress()->add_action( 'admin_head-nav-menus.php', __CLASS__, 'ap_menu_metaboxes' );
		anspress()->add_action( 'get_pages', __CLASS__, 'get_pages', 10, 2 );
		anspress()->add_action( 'wp_insert_post_data', __CLASS__, 'modify_answer_title', 10, 2 );
		anspress()->add_action( 'admin_post_anspress_create_base_page', __CLASS__, 'anspress_create_base_page' );
		anspress()->add_action( 'admin_notices', __CLASS__, 'anspress_notice' );
		anspress()->add_action( 'ap_register_options', __CLASS__, 'register_options' );
		anspress()->add_action( 'ap_after_field_markup', __CLASS__, 'page_select_field_opt' );
		anspress()->add_action( 'admin_footer', __CLASS__, 'admin_footer' );
		anspress()->add_filter( 'wp_terms_checklist_args', __CLASS__, 'disable_selected_category_ontop', 100, 1 );

		// additional menu
		anspress()->add_action( 'ap_admin_menu', 'AP_Category', 'admin_category_menu' );
		anspress()->add_action( 'ap_admin_menu', 'AP_Tag', 'admin_tag_menu' );
		anspress()->add_action( 'ap_admin_menu', 'AP_Analysis_Keyword', 'admin_analysis_keyword_menu' );

		// add setting field
		anspress()->add_action( 'admin_init', 'AP_Admin_Custom_Settings', 'giveup_copyright' );

		// acf
		anspress()->add_filter( 'acf/update_value', 'AP_ACF', 'prevent_update_wp_postmeta', 10, 3 );
		anspress()->add_filter( 'acf/load_value', 'AP_ACF', 'load_qameta', 10, 3 );

		// filter
		anspress()->add_filter( 'ap_insert_question_qameta', 'AP_Filters', 'save_inspection_check', 10, 3 );
		anspress()->add_filter( 'ap_insert_answer_qameta', 'AP_Filters', 'save_inspection_check', 10, 3 );

		// expert category
		anspress()->add_action( 'pre_post_update', __CLASS__, 'prevent_edit_question_by_expert_categories', -999, 2 );
		anspress()->add_action( 'pre_post_update', __CLASS__, 'prevent_edit_answer_by_expert_categories', -998, 2 );
		anspress()->add_filter( 'ap_trash_question', __CLASS__, 'prevent_trash_question_by_expert_categories', -999, 2 );
		anspress()->add_filter( 'ap_trash_answer', __CLASS__, 'prevent_trash_answer_by_expert_categories', -999, 2 );
		anspress()->add_filter( 'ap_untrash_question', __CLASS__, 'prevent_untrash_question_by_expert_categories', -999, 2 );
		anspress()->add_filter( 'ap_untrash_answer', __CLASS__, 'prevent_untrash_answer_by_expert_categories', -999, 2 );
		anspress()->add_filter( 'ap_before_delete_question', __CLASS__, 'prevent_delete_question_by_expert_categories', -999, 2 );
		anspress()->add_filter( 'ap_before_delete_answer', __CLASS__, 'prevent_delete_answer_by_expert_categories', -999, 2 );

		// statistic
		anspress()->add_action( 'ap_admin_menu', 'AP_Statistic', 'add_statistic_submenu' );
		anspress()->add_action( 'admin_notices', 'AP_Statistic', 'show_statistic_term_filter_result' );
		anspress()->add_action( 'admin_notices', 'AP_Statistic', 'show_statistic_yas_filter_result' );
		anspress()->add_action( 'admin_notices', 'AP_Statistic', 'show_statistic_tag_filter_result' );

		// Question Filter
		anspress()->add_filter( 'ap_insert_question_qameta', 'AP_Filters', 'save_meta_from_admin', 10, 3 );

		// select answer
		anspress()->add_action( 'ap_insert_question_qameta', __CLASS__, 'save_best_answer_selection', 10, 3 );

		// back to question
		// anspress()->add_action( 'admin_notices', __CLASS__, 'back_to_question' );
		
		// anspress()->add_action( 'pre_get_posts', __CLASS__, 'filter_questions_by_their_own_category' );
		// anspress()->add_action( 'pre_get_posts', __CLASS__, 'filter_answers_by_parent_category' );
	}

	/**
	 * Include files required in wp-admin
	 */
	public static function includes() {
		require_once 'functions.php';
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 */
	public static function enqueue_admin_styles() {
		if ( ! ap_load_admin_assets() ) {
			return;
		}

		wp_enqueue_style( 'ap-loading-indicator', ANSPRESS_URL . 'assets/lib/loading-indicator/css/modal-loading.css', [], AP_VERSION );
		wp_enqueue_style( 'ap-admin-css', ANSPRESS_URL . 'admin.css', [], AP_VERSION );
		wp_enqueue_style( 'wp-color-picker' );
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 */
	public static function enqueue_admin_scripts() {
		$page = get_current_screen();

		if ( ! ap_load_admin_assets() ) {
			return;
		}
		$ver = rand( 1, 300 );
		
		wp_enqueue_script( 'ap-sticky-header', ANSPRESS_URL . 'assets/lib/jquery.floatThead.js', array( 'jquery' ), true );
		wp_enqueue_script( 'ap-loading-indicator', ANSPRESS_URL . 'assets/lib/loading-indicator/js/modal-loading.js', array( 'jquery' ), true );
		wp_enqueue_script( 'ap-admin-js', ANSPRESS_URL . 'assets/js/admin-min.js', array( 'jquery', 'jquery-form', 'backbone', 'underscore' ), $ver, true );
		?>
			<script type="text/javascript">
				currentQuestionID = '<?php the_ID(); ?>';
				apTemplateUrl = '<?php echo 'nope'; ?>';
				aplang = {};
				apShowComments  = false;
			</script>
		<?php
		wp_enqueue_script( 'postbox' );

		$user_id = get_current_user_id();
		if ( ap_is_expert( $user_id ) && ap_is_admin_edit_or_new_question_page() ) {
			$expert_categories = ap_get_expert_categories( $user_id );
			wp_localize_script( 'ap-admin-js', 'expert_categories', $expert_categories );
		}
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 */
	public static function add_plugin_admin_menu() {
		if ( ! current_user_can( 'delete_pages' ) ) {
			return;
		}
		$pos    = self::get_free_menu_position( 42.9 );

		add_menu_page( 'AnsPress', 'AnsPress', 'delete_pages', 'anspress', array( __CLASS__, 'dashboard_page' ), ANSPRESS_URL . 'assets/images/answer.png', $pos );

		add_submenu_page( 'anspress', __( 'All Questions', 'anspress-question-answer' ), __( 'All Questions', 'anspress-question-answer' ), 'delete_pages', 'edit.php?post_type=question', '' );

		add_submenu_page( 'anspress', __( 'New Question', 'anspress-question-answer' ), __( 'New Question', 'anspress-question-answer' ), 'delete_pages', 'post-new.php?post_type=question', '' );

		add_submenu_page( 'anspress', __( 'All Answers', 'anspress-question-answer' ), __( 'All Answers', 'anspress-question-answer' ), 'delete_pages', 'edit.php?post_type=answer', '' );

		add_submenu_page( 'anspress', __( 'New Answer', 'anspress-question-answer' ), __( 'New Answer', 'anspress-question-answer' ), 'delete_pages', 'ap_select_question', array( __CLASS__, 'display_select_question' ) );

		/**
		 * Action hook for adding custom menu in wp-admin.
		 *
		 * @since unknown
		 */
		do_action( 'ap_admin_menu' );

		add_submenu_page( 'anspress', __( 'AnsPress Options', 'anspress-question-answer' ), __( 'Options', 'anspress-question-answer' ), 'manage_options', 'anspress_options', array( __CLASS__, 'display_plugin_options_page' ) );

		add_submenu_page( 'anspress-hidden', __( 'About AnsPress', 'anspress-question-answer' ), __( 'About AnsPress', 'anspress-question-answer' ), 'manage_options', 'anspress_about', array( __CLASS__, 'display_plugin_about_page' ) );

	}

	public static function fix_active_admin_menu( $parent_file ) {
		global $submenu_file, $current_screen, $plugin_page;
		
		$post_type = $current_screen->post_type;
		if ( ! in_array( $post_type, [ 'question', 'answer' ], true )  ) {
			return $parent_file; 
		}

		// Set correct active/current menu and submenu in the WordPress Admin menu for the "example_cpt" Add-New/Edit/List
		$parent_file  = 'anspress';
		$submenu_file = 'edit.php?post_type=' . $post_type;
		if ( $current_screen->action == 'add' ) {
			$submenu_file = ( $post_type == 'question' ) ? 'post-new.php?post_type=question' : 'ap_select_question';
		}

		return $parent_file;
	}

	/**
	 * Get free unused menu position. This function helps prevent other plugin
	 * menu conflict when assigned to same position.
	 *
	 * @param integer $start          position.
	 * @param double  $increment     position.
	 */
	public static function get_free_menu_position( $start, $increment = 0.99 ) {
    $menus_positions = array_keys( $GLOBALS['menu'] );

		if ( ! in_array( $start, $menus_positions, true ) ) {
			return $start;
		}

		// This position is already reserved find the closet one.
		while ( in_array( $start, $menus_positions, true ) ) {
			$start += $increment;
		}
		return $start;
	}

	/**
	 * Highlight the proper top level menu.
	 *
	 * @param   string $parent_file parent menu item.
	 * @return  string
	 */
	public static function tax_menu_correction( $parent_file ) {
		global $current_screen;
		$taxonomy = $current_screen->taxonomy;

		if ( 'question_category' === $taxonomy || 'question_tag' === $taxonomy ) {
			$parent_file = 'anspress';
		}

		return $parent_file;
	}

	/**
	 * Render the settings page for this plugin.
	 */
	public static function display_plugin_options_page() {
		include_once 'views/options.php';
	}

	/**
	 * Load about page layout
	 */
	public static function display_plugin_about_page() {
		include_once 'views/about.php';
	}

	/**
	 * Load dashboard page layout.
	 *
	 * @since 2.4
	 */
	public static function dashboard_page() {
		include_once 'views/dashboard.php';
	}

	/**
	 * Control the output of question selection.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public static function display_select_question() {
		include_once 'views/select_question.php';
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @param string $links Pugin action links.
	 */
	public static function add_action_links( $links ) {
		return array_merge(
			$links,
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=anspress_options' ) . '">' . __( 'Settings', 'anspress-question-answer' ) . '</a>',
				'about'    => '<a href="' . admin_url( 'admin.php?page=anspress_about' ) . '">' . __( 'About', 'anspress-question-answer' ) . '</a>',
			)
		);
	}

	/**
	 * Hook to run on init
	 */
	public static function init_actions() {
		$GLOBALS['wp']->add_query_var( 'post_parent' );

		// Flush_rules if option updated.
		if ( isset( $_GET['page'] ) && ('anspress_options' === $_GET['page']) && isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) { // @codingStandardsIgnoreLine.
			$options                   = ap_opt();
			$page                      = get_page( ap_opt( 'base_page' ) );
			$options['base_page_slug'] = $page->post_name;
			update_option( 'anspress_opt', $options );
			ap_opt( 'ap_flush', 'true' );
		}

		// If creating a new question then first set a question ID.
		global $typenow;
		global $pagenow;

		if ( in_array( $pagenow, array( 'post-new.php' ), true ) &&
				'answer' === $typenow &&
				! isset( $_GET['post_parent'] ) // @codingStandardsIgnoreLine.
			) {
			wp_safe_redirect( admin_url( 'admin.php?page=ap_select_question' ) );
			exit;
		}
	}

	/**
	 * Question meta box.
	 */
	public static function question_meta_box_class() {
		require_once 'meta-box.php';
		new AP_Question_Meta_Box();
	}

	/**
	 * Change post menu label.
	 */
	public static function change_post_menu_label() {
		global $menu;
		global $submenu;
		$submenu['anspress'][0][0] = '데쉬보드';
	}

	/**
	 * Set answer CPT post parent when saving.
	 *
	 * @param  integer $post_id Post ID.
	 * @param  object  $post Post Object.
	 * @since 2.0.0
	 */
	public static function ans_parent_post( $post_id, $post ) {
		global $pagenow;
		if ( ! in_array( $pagenow, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}
		if ( 'answer' === $post->post_type ) {
			$parent_q = (int) ap_sanitize_unslash( 'post_parent', 'p' );
			if ( empty( $parent_q ) ) {
				return;
			} else {
				global $wpdb;
				$wpdb->update( $wpdb->posts, array( 'post_parent' => $parent_q ), array( 'ID' => $post->ID ) ); // db call ok, cache ok.
			}
		}
	}

	/**
	 * Delete page check transient after AnsPress pages are deleted.
	 *
	 * @param integer $post_id Page ID.
	 * @return void
	 * @since 4.1.0
	 */
	public static function trashed_post( $post_id ) {
		$_post = get_post( $post_id );

		if ( 'page' === $_post->post_type ) {
			$pages_slug = [ 'base_page', 'ask_page' ];
			$page_ids   = [];
			$opt        = ap_opt();

			foreach ( $pages_slug as $slug ) {
				$page_ids[] = $opt[ $slug ];
			}

			if ( in_array( $_post->ID, $page_ids, true ) ) {
				delete_transient( 'ap_pages_check' );
			}
		}
	}

	/**
	 * [Not documented]
	 *
	 * @param array $data Post data array.
	 * @return array
	 */
	public static function post_data_check( $data ) {
		global $pagenow;

		if ( 'post.php' === $pagenow && 'answer' === $data['post_type'] ) {
			$parent_q = ap_sanitize_unslash( 'ap_q', 'p' );

			$parent_q = ! empty( 'parent_q' ) ? $parent_q : $data['post_parent'];

			if ( ! empty( $parent_q ) ) {
				add_filter( 'redirect_post_location', [ __CLASS__, 'custom_post_location' ], 99 );
				return;
			}
		}

		return $data;
	}

	/**
	 * Redirect to custom post location for error message.
	 *
	 * @param String $location redirect url.
	 * @return string
	 */
	public static function custom_post_location( $location ) {
		remove_filter( 'redirect_post_location', __FUNCTION__, 99 );
		$location = add_query_arg( 'message', 99, $location );

		return $location;
	}

	/**
	 * Hook menu meta box.
	 *
	 * @return void
	 * @since unknown
	 */
	public static function ap_menu_metaboxes() {
		add_meta_box( 'anspress-menu-mb', '알풀', [ __CLASS__, 'render_menu' ], 'nav-menus', 'side', 'high' );
	}

	/**
	 * Shows AnsPress menu meta box in WP menu editor.
	 *
	 * @return void
	 * @since unknown
	 */
	public static function render_menu( $object, $args ) {
		global $nav_menu_selected_id;
		$menu_items = ap_menu_obejct();

		$walker       = new Walker_Nav_Menu_Checklist( false );
		$removed_args = array(
			'action',
			'customlink-tab',
			'edit-menu-item',
			'menu-item',
			'page-tab',
			'_wpnonce',
		);
		?>

		<div id="anspress-div">
			<div id="tabs-panel-anspress-all" class="tabs-panel tabs-panel-active">
			<ul id="anspress-checklist-pop" class="categorychecklist form-no-clear" >
				<?php
					echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $menu_items ), 0, (object) array( 'walker' => $walker ) );
				?>
			</ul>

			<p class="button-controls">
				<span class="list-controls">
					<a href="
					<?php
						echo esc_url(
							add_query_arg(
								array(
									'anspress-all' => 'all',
									'selectall'    => 1,
								),
								remove_query_arg( $removed_args )
							)
						);
					?>
					#anspress-menu-mb" class="select-all"><?php _e( 'Select All', 'anspress-question-answer' ); ?></a>
				</span>

				<span class="add-to-menu">
					<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-anspress-menu-item" id="submit-anspress-div" />
					<span class="spinner"></span>
				</span>
			</p>
		</div>
	<?php
	}

	/**
	 * Filter comment clauses, join meta where key is _ap_flag
	 * As pre_get_comments custom meta query not working so we are adding JOIN statement
	 *
	 * @param  array $clauses WordPress comment clauses.
	 * @return array
	 */
	public static function filter_comments_query( $clauses ) {
		global $wpdb;
		$clauses['join'] = "JOIN $wpdb->commentmeta ON $wpdb->comments.comment_ID = $wpdb->commentmeta.comment_id AND meta_key = '_ap_flag'";

		return $clauses;
	}

	/**
	 * Remove AnsPress base page from front page page select input.
	 *
	 * @param array $pages Page array.
	 * @param array $r Arguments.
	 * @return array
	 */
	public static function get_pages( $pages, $r ) {
		if ( isset( $r['name'] ) && 'page_on_front' === $r['name'] ) {
			foreach ( (array) $pages as $k => $page ) {
				if ( ap_opt( 'base_page' ) == $page->ID ) { // loose comparison okay.
					unset( $pages[ $k ] );
				}
			}
		}

		return $pages;
	}

	/**
	 * Modify answer title before saving, in wp-admin.
	 *
	 * @param  array $data    Raw post data.
	 * @return array
	 */
	public static function modify_answer_title( $data ) {
		if ( 'answer' === $data['post_type'] ) {
			$data['post_title'] = get_the_title( $data['post_parent'] );
		}

		return $data;
	}

	/**
	 * Show AnsPress notices.
	 */
	public static function anspress_notice() {
		$page             = get_current_screen();
		$anspress_updates = get_option( 'anspress_updates', [] );
		$have_updates     = empty( $anspress_updates ) || in_array( false, $anspress_updates, true );

		$messages = array(
			'missing_pages' => [
				'type'    => 'error',
				'message' => __( 'One or more AnsPress page(s) does not exists.', 'anspress-question-answer' ),
				'button'  => ' <a href="' . admin_url( 'admin-post.php?action=anspress_create_base_page' ) . '">' . __( 'Set automatically', 'anspress-question-answer' ) . '</a> ' . __( 'Or', 'anspress-question-answer' ) . ' <a href="' . admin_url( 'admin.php?page=anspress_options' ) . '">' . __( 'Set set by yourself', 'anspress-question-answer' ) . '</a>',
				'show'    => ( ! self::check_pages_exists() ),
			],
		);

		foreach ( $messages as $msg ) {
			if ( $msg['show'] ) {
				$class = 'ap-notice notice notice-' . $msg['type'];
				printf(
					'<div class="%1$s %4$s"><p>%2$s%3$s</p></div>',
					esc_attr( $class ),
					esc_html( $msg['message'] ),
					$msg['button'],
					'apicon-anspress-icon'
				);
			}
		}
	}

	/**
	 * Check if AnsPress pages are exists.
	 *
	 * @return boolean
	 * @since 4.1.0
	 */
	private static function check_pages_exists() {
		$cache = get_transient( 'ap_pages_check' );

		if ( false == $cache ) {
			$opt        = ap_opt();
			$pages_slug = array_keys( ap_main_pages() );

			$pages_in = [];
			foreach ( $pages_slug as $slug ) {
				$pages_in[] = $opt[ $slug ];
			}

			$args = array(
				'include'     => $pages_in,
				'post_type'   => 'page',
				'post_status' => 'publish',
			);

			$pages = get_posts( $args );

			if ( count( $pages ) < count( $pages_slug ) ) {
				$cache = '0';
				set_transient( 'ap_pages_check', '0', HOUR_IN_SECONDS );
			} else {
				set_transient( 'ap_pages_check', '1', HOUR_IN_SECONDS );
				$cache = '1';
			}
		}

		return '0' === $cache ? false : true;
	}

	/**
	 * Create a page and set it as base page.
	 */
	public static function anspress_create_base_page() {
		if ( current_user_can( 'manage_options' ) ) {
			ap_create_base_page();
			flush_rewrite_rules();
			delete_transient( 'ap_pages_check' );
		}

		wp_redirect( admin_url( 'admin.php?page=anspress_options' ) );
	}

	/**
	 * Register all AnsPress options.
	 *
	 * @return void
	 * @since 4.1.0
	 */
	public static function register_options() {
		add_filter( 'ap_form_options_general_pages', [ __CLASS__, 'options_general_pages' ] );
		add_filter( 'ap_form_options_general_layout', [ __CLASS__, 'options_general_layout' ] );
		add_filter( 'ap_form_options_general_upload', [ __CLASS__, 'options_general_upload' ] );
	}

	/**
	 * Register AnsPress general pages options.
	 *
	 * @return array
	 * @since 4.1.0
	 */
	public static function options_general_pages() {
		$opt  = ap_opt();
		$form = array(
			'submit_label' => __( 'Save Pages', 'anspress-question-answer' ),
			'fields'       => array( ),
			'sep-warning'    => array(
				'html' => '<div class="ap-uninstall-warning">' . __( 'If you have created main pages manually then make sure to have [anspress] shortcode in all pages.', 'anspress-question-answer' ) . '</div>',
			),
		);

		foreach ( ap_main_pages() as $slug => $args ) {
			$form['fields'][ $slug ] = array(
				'label'      => $args['label'],
				'desc'       => $args['desc'],
				'type'       => 'select',
				'options'    => 'posts',
				'posts_args' => array(
					'post_type' => 'page',
					'showposts' => -1,
				),
				'value'      => $opt[ $slug ],
				'sanitize'   => 'absint',
			);
		}

		/**
		 * Filter to override pages options form.
		 *
		 * @param array $form Form arguments.
		 * @since 4.1.0
		 */
		return apply_filters( 'ap_options_form_pages', $form );
	}

	/**
	 * Register AnsPress general layout options.
	 *
	 * @return array
	 * @since 4.1.0
	 */
	public static function options_general_layout() {
		$opt = ap_opt();

		$form = array(
			'fields' => array(
				'question_per_page'            => array(
					'label'   => __( 'Questions per page', 'anspress-question-answer' ),
					'desc'    => __( 'Questions to show per page.', 'anspress-question-answer' ),
					'subtype' => 'number',
					'value'   => $opt['question_per_page'],
				),
				'answers_per_page'             => array(
					'label'   => __( 'Answers per page', 'anspress-question-answer' ),
					'desc'    => __( 'Answers to show per page.', 'anspress-question-answer' ),
					'subtype' => 'number',
					'value'   => $opt['answers_per_page'],
				),
			),
		);

		/**
		 * Filter to override layout options form.
		 *
		 * @param array $form Form arguments.
		 * @since 4.1.0
		 */
		return apply_filters( 'ap_options_form_layout', $form );
	}

	/**
	 * Register other UAC options.
	 *
	 * @return array
	 * @since 4.1.0
	 */
	public static function options_general_upload() {
		$opt = ap_opt();

		$form = array(
			'fields' => array(
				'uploads_per_post'    => array(
					'label' => __( 'Max uploads per post', 'anspress-question-answer' ),
					'desc'  => __( 'Set numbers of media user can upload for each post.', 'anspress-question-answer' ),
					'value' => $opt['uploads_per_post'],
				),
				'max_upload_size'     => array(
					'label' => __( 'Max upload size', 'anspress-question-answer' ),
					'desc'  => __( 'Set maximum upload size.', 'anspress-question-answer' ),
					'value' => $opt['max_upload_size'],
				),
			),
		);

		return $form;
	}

	/**
	 * Add link to view, edit and create right next to page select field.
	 *
	 * @param object $field Field object.
	 * @return void
	 */
	public static function page_select_field_opt( $field ) {
		$page_slugs = array_keys( ap_main_pages() );

		// Return if not the field we are looking for.
		if ( ! in_array( $field->original_name, $page_slugs, true ) ) {
			return;
		}

		$field->add_html( '&nbsp;&nbsp;&nbsp;<a href="' . esc_url( get_permalink( $field->value() ) ) . '">' . __( 'View page', 'anspress-question-answer' ) . '</a>&nbsp;&nbsp;&nbsp;' );
		$field->add_html( '<a href="' . esc_url( get_edit_post_link( $field->value() ) ) . '">' . __( 'Edit page', 'anspress-question-answer' ) . '</a>' );
	}

	/**
	 * Output custom script and styles in admin_footer.
	 *
	 * @return void
	 * @since 4.1.8
	 */
	public static function admin_footer() {
		?>
			<style>
				#adminmenu .anspress-license-count{
					background: #0073aa;
				}
			</style>
		<?php
	}

	public static function disable_selected_category_ontop( $args ) {
		if ( ap_is_admin_edit_or_new_question_page() ) {
			$args['checked_ontop'] = false;
		}
		return $args;
	}

	public static function prevent_edit_question_by_expert_categories( $post_id, $data ) {
		if ( ap_is_admin_update( 'question' ) ) {
			if ( ! ap_user_can_edit_other_category_qa( $post_id ) ) {
				wp_die( __( 'You can not edit this post, because you are not expert in this category. Plese contact super administrator', 'anspress-question-answer' ), 'STOP !' );
			}
		}
	}

	public static function prevent_edit_answer_by_expert_categories() {
		
		if ( ap_is_admin_update( 'answer' ) ) {
			
			if ( ! ap_user_can_edit_other_category_qa( $post_id ) ) {
				wp_die( __( 'You can not edit this post, because you are not expert in this category. Plese contact super administrator', 'anspress-question-answer' ), 'STOP !' );
			}
		}
	}

	public static function save_best_answer_selection($qameta, $question, $updated) {
		if ( ap_is_admin_update( 'question' ) ) {
			
			// Unselect best answer if already selected.
			if ( ap_have_answer_selected( $question->ID ) ) {
				ap_unset_selected_answer( $question->ID );
			}

			$selected_id = ap_isset_post_value( 'selected_answer_id' );			
			if ( $selected_id ) {
				$answer = get_post( $selected_id );
				// Do not allow answer to be selected as best if status is moderate.
				if ( in_array( $answer->post_status, ['trash'], true ) ) {
					return;
				}

				ap_set_selected_answer( $answer->post_parent, $answer->ID );
			}
		}
		return $qameta;
	}

	public static function prevent_trash_question_by_expert_categories( $post_id, $post ) {
		if ( ! ap_user_can_edit_other_category_qa( $post_id ) ) {
			wp_die( "해당 질문의 전문가만 삭제할 수 있습니다", "ERROR" );
		}
	}

	public static function prevent_trash_answer_by_expert_categories( $post_id, $post ) {
		if ( ! ap_user_can_edit_other_category_qa( $post_id ) ) {
			wp_die( "해당 답변의 전문가만 삭제할 수 있습니다", "ERROR" );
		}
	}

	public static function prevent_untrash_question_by_expert_categories( $post_id, $post ) {
		if ( ! ap_user_can_edit_other_category_qa( $post_id ) ) {
			wp_die( "해당 질문의 전문가만 복구할 수 있습니다", "ERROR" );
		}
	}

	public static function prevent_untrash_answer_by_expert_categories( $post_id, $post ) {
		if ( ! ap_user_can_edit_other_category_qa( $post_id ) ) {
			wp_die( "해당 답변의 전문가만 복구할 수 있습니다", "ERROR" );
		}
	}

	public static function prevent_delete_question_by_expert_categories( $post_id, $post ) {
		if ( ! ap_user_can_edit_other_category_qa( $post_id ) ) {
			wp_die( "해당 질문의 전문가만 삭제할 수 있습니다", "ERROR" );
		}
	}

	public static function prevent_delete_answer_by_expert_categories( $post_id, $post ) {
		if ( ! ap_user_can_edit_other_category_qa( $post_id ) ) {
			wp_die( "해당 답변의 전문가만 삭제할 수 있습니다", "ERROR" );
		}
	}

	public static function back_to_question() {
		global $pagenow;
		$action = ap_isset_post_value( 'trashed' );
		if ( $action && isset( $_REQUEST['ids'] ) ) {
			$ids = preg_replace( '/[^0-9,]/', '', $_REQUEST['ids'] );
			
			echo '<div id="message" class="updated notice is-dismissible"><p>' . $message . '</p></div>';
		}
	}

}