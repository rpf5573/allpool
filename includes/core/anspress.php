<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define databse version.
define( 'AP_DB_VERSION', 1 );

class AnsPress {

	private $_plugin_version = '1.0.0';

	/**
	 * Class instance
	 *
	 * @access public
	 * @static
	 * @var object
	 */
	public static $instance = null;

	/**
	 * AnsPress pages
	 *
	 * @access public
	 * @var array All AnsPress pages
	 */
	public $pages;

	/**
	 * The array of actions registered with WordPress.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var array The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions;

	/**
	 * The array of filters registered with WordPress.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var array The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters;

	/**
	 * The session.
	 *
	 * @var AnsPress\Session
	 * @since 1.0.0
	 */
	public $session;

	/**
	 * AnsPress question loop
	 *
	 * @access public
	 * @var object AnsPress question query loop
	 */
	public $questions;

	/**
	 * Current question.
	 *
	 * @var object
	 */
	public $current_question;

	/**
	 * AnsPress answers loop.
	 *
	 * @var object Answer query loop
	 */
	public $answers;

	/**
	 * Current answer.
	 *
	 * @var object
	 */
	public $current_answer;

	/**
	 * AnsPress question rewrite rules.
	 *
	 * @var array
	 * @since 4.1.0
	 */
	public $question_rule = [];

	/**
	 * The forms.
	 *
	 * @var array
	 * @since 4.1.0
	 */
	public $forms = [];

	/**
	 * Initializes the plugin by setting localization, hooks, filters, and administrative functions.
	 *
	 * @access public
	 * @static
	 *
	 * @return instance
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
			self::$instance = new self();
			self::$instance->setup_constants();
			self::$instance->actions = array();
			self::$instance->filters = array();

			self::$instance->includes();
			self::$instance->session = AnsPress\Session::init();

			AP_Hooks::init();

			new AP_Process_Form();

			/*
				* Hooks for extension to load their codes after AnsPress is loaded.
				*/
			do_action( 'anspress_loaded' );
		} // End if().

		return self::$instance;
	}

	/**
	 * Setup plugin constants.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function setup_constants() {
		$theme_dir = get_template_directory() . DIRECTORY_SEPARATOR;
    $theme_dir_uri = get_stylesheet_directory_uri() . DIRECTORY_SEPARATOR;

		$constants = array(
			'DS'                  => DIRECTORY_SEPARATOR,
			'AP_VERSION'          => $this->_plugin_version,
			'ANSPRESS_DIR'        => $theme_dir,
			'ANSPRESS_URL'        => $theme_dir_uri,
    );
    

		foreach ( $constants as $k => $val ) {
			if ( ! defined( $k ) ) {
				define( $k, $val );
			}
		}
	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @access public
	 */
	public function setup_hooks() {
		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}
	}

	/**
	 * Include required files.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function includes() {
		// Core
		require_once ANSPRESS_DIR . 'includes/core/acf.php';
		require_once ANSPRESS_DIR . 'includes/core/roles-cap.php';
		require_once ANSPRESS_DIR . 'includes/core/rewrite.php';
		require_once ANSPRESS_DIR . 'includes/core/shortcode.php';
		require_once ANSPRESS_DIR . 'includes/core/options.php';
		require_once ANSPRESS_DIR . 'includes/core/common-pages.php';
		require_once ANSPRESS_DIR . 'includes/core/post-types.php';
		require_once ANSPRESS_DIR . 'includes/core/qameta.php';	
		require_once ANSPRESS_DIR . 'includes/core/theme.php';
		require_once ANSPRESS_DIR . 'includes/core/query/qaquery.php';
		require_once ANSPRESS_DIR . 'includes/core/query/qaquery-hooks.php';
		require_once ANSPRESS_DIR . 'includes/core/query/question-query.php';
		require_once ANSPRESS_DIR . 'includes/core/query/answer-query.php';
		require_once ANSPRESS_DIR . 'includes/core/form/process-form.php';
		require_once ANSPRESS_DIR . 'includes/core/form/form-hooks.php';
		require_once ANSPRESS_DIR . 'includes/core/form/uploader.php';
		require_once ANSPRESS_DIR . 'includes/core/form/class-form.php';
		require_once ANSPRESS_DIR . 'includes/core/form/class-validate.php';
		require_once ANSPRESS_DIR . 'includes/core/form/fields/class-field.php';
		require_once ANSPRESS_DIR . 'includes/core/form/fields/class-input.php';
		require_once ANSPRESS_DIR . 'includes/core/form/fields/class-group.php';
		require_once ANSPRESS_DIR . 'includes/core/form/fields/class-repeatable.php';
		require_once ANSPRESS_DIR . 'includes/core/form/fields/class-checkbox.php';
		require_once ANSPRESS_DIR . 'includes/core/form/fields/class-select.php';
		require_once ANSPRESS_DIR . 'includes/core/form/fields/class-editor.php';
		require_once ANSPRESS_DIR . 'includes/core/form/fields/class-upload.php';
		require_once ANSPRESS_DIR . 'includes/core/form/fields/class-tags.php';
		require_once ANSPRESS_DIR . 'includes/core/form/fields/class-radio.php';
		require_once ANSPRESS_DIR . 'includes/core/form/fields/class-textarea.php';
    
    // Tools
    require_once ANSPRESS_DIR . 'includes/tools/singleton.php';
		require_once ANSPRESS_DIR . 'includes/tools/session.php';
		require_once ANSPRESS_DIR . 'includes/tools/wp-all-import.php';
    
    // Addons
    require_once ANSPRESS_DIR . 'includes/addons/avatar/avatar.php';
		require_once ANSPRESS_DIR . 'includes/addons/reputation/reputation.php';
		require_once ANSPRESS_DIR . 'includes/addons/category.php';
		require_once ANSPRESS_DIR . 'includes/addons/filters.php';
		require_once ANSPRESS_DIR . 'includes/addons/votes.php';
		require_once ANSPRESS_DIR . 'includes/addons/views.php';
    require_once ANSPRESS_DIR . 'includes/addons/profile.php';
		require_once ANSPRESS_DIR . 'includes/addons/wishlist.php';
		require_once ANSPRESS_DIR . 'includes/addons/inspection-check.php';

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			require_once ANSPRESS_DIR . 'includes/tools/ajax.php';
			require_once ANSPRESS_DIR . 'includes/core/ajax-hooks.php';
			require_once ANSPRESS_DIR . 'includes/core/toggle-best-answer.php';
			if ( is_admin() ) {
				require_once ANSPRESS_DIR . 'includes/admin/ajax.php';
			}
		}

		if ( is_admin() ) {
			require_once ANSPRESS_DIR . 'includes/addons/statistic/statistic.php';
			require_once ANSPRESS_DIR . 'includes/admin/anspress-admin.php';
			require_once ANSPRESS_DIR . 'includes/admin/list-table-hooks.php';
			require_once ANSPRESS_DIR . 'includes/admin/custom-settings.php';
		}

		require_once ANSPRESS_DIR . 'includes/hooks.php';
	}

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since  2.4
	 * @access public
	 *
	 * @param string            $hook          The name of the WordPress action that is being registered.
	 * @param object            $component     A reference to the instance of the object on which the action is defined.
	 * @param string            $callback      The name of the function definition on the $component.
	 * @param int      Optional $priority      The priority at which the function should be fired.
	 * @param int      Optional $accepted_args The number of arguments that should be passed to the $callback.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since  2.4
	 * @access public
	 *
	 * @param string            $hook          The name of the WordPress filter that is being registered.
	 * @param object            $component     A reference to the instance of the object on which the filter is defined.
	 * @param string            $callback      The name of the function definition on the $component.
	 * @param int      Optional $priority      The priority at which the function should be fired.
	 * @param int      Optional $accepted_args The number of arguments that should be passed to the $callback.
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * A utility function that is used to register the actions and hooks into a single
	 * collection.
	 *
	 * @since  2.4
	 * @access private
	 *
	 * @param array             $hooks         The collection of hooks that is being registered (that is, actions or filters).
	 * @param string            $hook          The name of the WordPress filter that is being registered.
	 * @param object            $component     A reference to the instance of the object on which the filter is defined.
	 * @param string            $callback      The name of the function definition on the $component.
	 * @param int      Optional $priority      The priority at which the function should be fired.
	 * @param int      Optional $accepted_args The number of arguments that should be passed to the $callback.
	 * @param integer           $priority      Priority.
	 * @param integer           $accepted_args Accepted arguments.
	 *
	 * @return type The collection of actions and filters registered with WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);

		return $hooks;
	}

	/**
	 * Get specific AnsPress form.
	 *
	 * @param string $name Name of form.
	 * @return false|object
	 * @since 4.1.0
	 */
	public function &get_form( $name ) {
		$name = preg_replace( '/^form_/i', '', $name );

		if ( $this->form_exists( $name ) ) {
			return $this->forms[ $name ];
		}

		return false;
	}

	/**
	 * Check if a form exists in AnsPress, if not then tries to register.
	 *
	 * @param string $name Name of form.
	 * @return boolean
	 * @since 4.1.0
	 */
	public function form_exists( $name ) {
			
		$name = preg_replace( '/^form_/i', '', $name );

		if ( isset( $this->forms[ $name ] ) ) {
			return true;
		}

		/**
		 * Register a form in AnsPress.
		 *
		 * @param array $form {
		 *      Form options and fields. Check @see `AnsPress\Form` for more detail.
		 *
		 *      @type string  $submit_label Custom submit button label.
		 *      @type boolean $editing      Pass true if currently in editing mode.
		 *      @type integer $editing_id   If editing then pass editing post or comment id.
		 *      @type array   $fields       Fields. For more detail on field option check documentations.
		 * }
		 * @since 4.1.0
		 * @todo  Add detailed docs for `$fields`.
		 */
		$args = apply_filters( 'ap_form_' . $name, null );

		if ( ! is_null( $args ) && ! empty( $args ) ) {
			$this->forms[ $name ] = new AnsPress\Form( 'form_' . $name, $args );

			return true;
		}

		return false;
	}

}

/**
 * Initialize AnsPress.
 */
function anspress() {
	return AnsPress::instance();
}

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
function anspress_activation() {
	require_once dirname( __FILE__ ) . '/activate.php';
	\AP_Activate::get_instance();
}

if ( get_option( 'alpool_activate', 'no' ) == 'no' ) {
	anspress_activation();
}

anspress()->setup_hooks();