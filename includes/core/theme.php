<?php
/**
 * Class for anspress theme
 *
 * @package      AnsPress
 * @subpackage   Theme Hooks
 * @author       Rahul Aryan <support@anspress.io>
 * @license      GPL-3.0+
 * @link         https://anspress.io
 * @copyright    2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Holds all hooks related to frontend layout/theme
 */
class AP_Theme {

	public static function add_theme_supports() {
		add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array(
      'gallery',
      'caption'
    ) );
    add_theme_support( 'custom-logo', array(
      'width'       => 300,
      'height'      => 150,
      'flex-width'  => true
    ) );
		add_theme_support( 'customize-selective-refresh-widgets' );
	}

	public static function register_nav_menus() {
		register_nav_menu( 'top', '데스크탑에서 보여지는 기본 메뉴입니다' );
    register_nav_menu( 'mobile-no-login', '모바일에서 로그인하지 않은 유저에게 보여지는 메뉴입니다' );
		register_nav_menu( 'mobile-logged-in', '모바일에서 로그인한 유저에게 보여지는 메뉴입니다' );
		register_nav_menu( 'footer', '페이지 하단에 보여지는 메뉴입니다. 개인정보 처리방침, 이용약관페이지를 넣어주세요!' );
	}

	public static function enqueue_scripts() {
		// wp_enqueue_script( 'import', 'https://cdn.iamport.kr/js/iamport.payment-1.1.5.js', array('jquery'), false, false );

		wp_enqueue_script( 'selectize', ANSPRESS_URL . 'assets/lib/selectize.min.js', array('jquery'), true );

		// Semantic-ui
		wp_enqueue_script( 'semantic-ui', ANSPRESS_URL . 'assets/lib/semantic-ui/semantic.min.js', array('jquery'), true );
    wp_enqueue_style( 'semantic-ui',  ANSPRESS_URL . 'assets/lib/semantic-ui/semantic.min.css' );
  
    // SumoSelect
    wp_enqueue_script( 'sumoselect', ANSPRESS_URL . 'assets/lib/sumoselect/jquery.sumoselect.js', array('jquery'), '1.0', true );
    wp_enqueue_style( 'sumoselect', ANSPRESS_URL . 'assets/lib/sumoselect/sumoselect-min.css' );
  
    // mmenu
    wp_enqueue_script( 'mmenu', ANSPRESS_URL . 'assets/lib/mmenu/jquery.mmenu.all.js', array('jquery'), '1.0', true );
		wp_enqueue_style( 'mmenu', ANSPRESS_URL . 'assets/lib/mmenu/jquery.mmenu.all.css' );
  
    // Main
		wp_enqueue_style( 'ap-main', get_stylesheet_uri() );
		wp_enqueue_script( 'ap-main', ANSPRESS_URL . 'assets/js/main-min.js', [ 'jquery', 'jquery-form', 'backbone', 'underscore' ], '1.0', true );

		wp_enqueue_style( 'ap-print', ANSPRESS_URL . 'print.css', array(), '1.0.0', 'print' );
		
		$aplang = array(
			'loading'                => __( 'Loading..', 'anspress-question-answer' ),
			'sending'                => __( 'Sending request', 'anspress-question-answer' ),
			'file_size_error'        => sprintf( __( 'File size is bigger than %s MB', 'anspress-question-answer' ), round( ap_opt( 'max_upload_size' ) / ( 1024 * 1024 ), 2 ) ),
			'attached_max'           => __( 'You have already attached maximum numbers of allowed attachments', 'anspress-question-answer' ),
			'commented'              => __( 'commented', 'anspress-question-answer' ),
			'comment'                => __( 'Comment', 'anspress-question-answer' ),
			'cancel'                 => __( 'Cancel', 'anspress-question-answer' ),
			'update'                 => __( 'Update', 'anspress-question-answer' ),
			'your_comment'           => __( 'Write your comment...', 'anspress-question-answer' ),
			'notifications'          => __( 'Notifications', 'anspress-question-answer' ),
			'mark_all_seen'          => __( 'Mark all as seen', 'anspress-question-answer' ),
			'search'                 => __( 'Search', 'anspress-question-answer' ),
			'no_permission_comments' => __( 'Sorry, you don\'t have permission to read comments.', 'anspress-question-answer' ),
		);
	
		echo '<script type="text/javascript">';
			echo 'var ajaxurl = "' . admin_url( 'admin-ajax.php' ) . '",';
			echo 'ap_nonce 	= "' . wp_create_nonce( 'ap_ajax_nonce' ) . '",';
			echo 'apTemplateUrl = "' . 'nope' . '";';
			echo 'apQuestionID = "' . get_question_id() . '";';
			echo 'aplang = ' . wp_json_encode( $aplang ) . ';';
			echo 'disable_q_suggestion = "' . (bool) ap_opt( 'disable_q_suggestion' ) . '";';
		echo '</script>';
	}

	public static function remove_admin_bar_on_front() {
    if ( ! current_user_can('administrator') && ! is_admin() ) {
      show_admin_bar(false);
    }
  }

	/**
	 * Function get called on init
	 */
	public static function add_shortcode() {
		// Register anspress shortcode.
		add_shortcode( 'anspress', array( AP_BasePage_Shortcode::get_instance(), 'anspress_sc' ) );
	}

	/**
	 * Add answer-seleted class in post_class.
	 *
	 * @param  array $classes Post class attribute.
	 * @return array
	 * @since 2.0.1
	 * @since 4.1.8 Fixes #426: Undefined property `post_type`.
	 */
	public static function question_answer_post_class( $classes ) {
		global $post;

		if ( ! $post ) {
			return $classes;
		}

		if ( 'question' === $post->post_type ) {
			if ( ap_have_answer_selected( $post->ID ) ) {
				$classes[] = 'answer-selected';
			}

			$classes[] = 'answer-count-' . ap_get_answers_count();

		} elseif ( 'answer' === $post->post_type ) {

			if ( ap_is_selected( $post->ID ) ) {
				$classes[] = 'best-answer';
			}

			if ( ! ap_user_can_read_answer( $post ) ) {
				$classes[] = 'no-permission';
			}
		}

		return $classes;
	}

	/**
	 * Add anspress classes to body.
	 *
	 * @param  array $classes Body class attribute.
	 * @return array
	 * @since 2.0.1
	 */
	public static function body_class( $classes ) {
		// Add anspress class to body.
		if ( is_anspress() ) {
			$classes[] = 'anspress-content';
			$classes[] = 'ap-page-' . ap_current_page();
		}

		return $classes;
	}

	/**
	 * Filter wp_title.
	 *
	 * @param string $title WP page title.
	 * @return string
	 * @since 4.1.1 Do not override title of all pages except single question.
	 */
	public static function ap_title( $title ) {
		if ( is_anspress() ) {
			remove_filter( 'wp_title', [ __CLASS__, 'ap_title' ] );

			if ( is_question() ) {
				return ap_question_title_with_solved_prefix() . ' | ';
			}
		}

		return $title;
	}

	/**
	 * Add default before body sidebar in AnsPress contents
	 */
	public static function ap_before_html_body() {
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$data         = wp_json_encode(
				array(
					'user_login'   => $current_user->data->user_login,
					'display_name' => $current_user->data->display_name,
					'user_email'   => $current_user->data->user_email,
					'avatar'       => get_avatar( $current_user->ID ),
				)
			);
			?>
				<script type="text/javascript">
					apCurrentUser = <?php echo $data; // xss okay. ?>;
				</script>
			<?php
		}
		dynamic_sidebar( 'ap-before' );
	}

	/**
	 * Ajax callback for post actions dropdown.
	 *
	 * @since 3.0.0
	 */
	public static function post_actions() {
		$post_id = (int) ap_sanitize_unslash( 'post_id', 'r' );

		if ( ! check_ajax_referer( 'post-actions-' . $post_id, 'nonce', false ) || ! is_user_logged_in() ) {
			ap_ajax_json( 'something_wrong' );
		}

		$actions = ap_post_actions( $post_id );

		ap_ajax_json(
			[
				'success' => true,
				'actions' => $actions,
			]
		);
	}

	/**
	 * Check if anspress.php file exists in theme. If exists
	 * then load this template for AnsPress.
	 *
	 * @param  string $template Template.
	 * @return string
	 * @since  3.0.0
	 * @since  4.1.0 Give priority to page templates and then anspress.php and lastly fallback to page.php.
	 * @since  4.1.1 Load single question template if exists.
	 */
	public static function anspress_basepage_template( $template ) {
		if ( is_anspress() ) {
			$templates = [ 'anspress.php', 'page.php', 'singular.php', 'index.php' ];

			if ( is_page() ) {
				$_post = get_queried_object();

				array_unshift( $templates, 'page-' . $_post->ID . '.php' );
				array_unshift( $templates, 'page-' . $_post->post_name . '.php' );

				$page_template = get_post_meta( $_post->ID, '_wp_page_template', true );

				if ( ! empty( $page_template ) && 'default' !== $page_template ) {
					array_unshift( $templates, $page_template );
				}
			} elseif ( is_single() ) {
				$_post = get_queried_object();

				array_unshift( $templates, 'single-' . $_post->ID . '.php' );
				array_unshift( $templates, 'single-' . $_post->post_name . '.php' );
				array_unshift( $templates, 'single-' . $_post->post_type . '.php' );

			} elseif ( is_tax() ) {
				$_term     = get_queried_object();
				$term_type = str_replace( 'question_', '', $_term->taxonomy );
				array_unshift( $templates, 'anspress-' . $term_type . '.php' );
			}

			$new_template = locate_template( $templates );

			if ( '' !== $new_template ) {
				return $new_template;
			}
		}

		return $template;
	}

	/**
	 * Generate question excerpt if there is not any already.
	 *
	 * @param string      $excerpt Default excerpt.
	 * @param object|null $post    WP_Post object.
	 * @return string
	 * @since 4.1.0
	 */
	public static function get_the_excerpt( $excerpt, $post = null ) {
		$post = get_post( $post );

		if ( 'question' === $post->post_type ) {
			if ( get_query_var( 'answer_id' ) ) {
				$post = ap_get_post( get_query_var( 'answer_id' ) );
			}

			// Check if excerpt exists.
			if ( ! empty( $post->post_excerpt ) ) {
				return $post->post_excerpt;
			}

			$excerpt_length = apply_filters( 'excerpt_length', 55 );
			$excerpt_more   = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
			return wp_trim_words( $post->post_content, $excerpt_length, $excerpt_more );
		}

		return $excerpt;
	}

	/**
	 * Remove hentry class from question, answers and main pages .
	 *
	 * @param array   $post_classes Post classes.
	 * @param array   $class        An array of additional classes added to the post.
	 * @param integer $post_id      Post ID.
	 * @return array
	 * @since 4.1.0
	 */
	public static function remove_hentry_class( $post_classes, $class, $post_id ) {
		$_post = ap_get_post( $post_id );

		if ( $_post && ( in_array( $_post->post_type, [ 'answer', 'question' ], true ) || in_array( $_post->ID, ap_main_pages_id() ) ) ) {
			return array_diff( $post_classes, [ 'hentry' ] );
		}

		return $post_classes;
	}

	/**
	 * Add current-menu-item class in AnsPress pages
	 *
	 * @param	array	$class Menu class.
	 * @param	object $item Current menu item.
	 * @return array menu item.
	 * @since	2.1
	 */
	public static function fix_nav_current_class( $class, $item ) {
		// Return if empty or `$item` is not object.
		if ( empty( $item ) || ! is_object( $item ) ) {
			return $class;
		}

		if ( ap_current_page() === $item->object ) {
			$class[] = 'current-menu-item';
		}

		return $class;
	}

	/**
	 * Make human_time_diff strings translatable.
	 *
	 * @param	string $since Time since.
	 * @return string
	 * @since	2.4.8
	 */
	public static function human_time_diff( $since ) {
		$replace = array(
			'min'			  => __( 'minute', 'anspress-question-answer' ),
			'mins'		  => __( 'minutes', 'anspress-question-answer' ),
			'hour'		  => __( 'hour', 'anspress-question-answer' ),
			'hours' 	  => __( 'hours', 'anspress-question-answer' ),
			'day'	 	    => __( 'day', 'anspress-question-answer' ),
			'days'		  => __( 'days', 'anspress-question-answer' ),
			'week'		  => __( 'week', 'anspress-question-answer' ),
			'weeks'		  => __( 'weeks', 'anspress-question-answer' ),
			'year'		  => __( 'year', 'anspress-question-answer' ),
			'years'		  => __( 'years', 'anspress-question-answer' ),
		);

		return strtr( $since, $replace );
	}

	public static function deregister_social_login_btns_in_register_form() {
    remove_action( 'register_form', 'wsl_render_auth_widget_in_wp_register_form' );
	}
	
	public static function design_tml() {
    $forms = tml_get_forms();
    foreach( $forms as $form ) {
      $form->add_attribute( 'class', 'ui form' );
      if ( 'register' == $form->get_name() ) {
        $args = array(
          'type'    => 'checkbox',
          'label'   => '저작권양도방침에 동의합니다',
          'render_args' => array(
            'before' => '<div class="tml-field-wrap ui field"> <label class="tml-label">저작권양도방침</label> ' . ap_get_copyright_content(),
            'after'  => '</div>'
          ),
          'error' => '저작권양도방침에 동의해주세요',
          'priority' => 25,
        );
        $field = new Theme_My_Login_Form_Field( $form, 'giveup_copyright', $args );
        tml_add_form_field( $form, $field );
      }

      foreach( tml_get_form_fields( $form ) as $field ) {
        $field_type = $field->get_type();
        if ( 'rememberme' != $field->get_name() ) {
          $field->add_attribute( 'required', 'true' );
        }
        if ( 'submit' == $field_type ) {
          if( 'register' == $form->get_name() ) {
            $field->add_attribute( 'class', 'fluid positive ui button' );
          } else {
            $field->add_attribute( 'class', 'fluid primary ui button' );
          }
        }
        if ( ! ('hidden' == $field_type || 'checkbox' == $field_type) ) {
          $field->render_args['before'] = '<div class="tml-field-wrap ui field">';
          $field->render_args['after'] = '</div>';
        }
      }
    }
	}
	
	public static function redirect_after_login() {
    if ( is_user_logged_in() && is_admin() && ! defined( 'DOING_AJAX' ) ) {
      $user_id = get_current_user_id();
      if ( ap_is_participant( $user_id ) ) {
        
        wp_redirect( home_url() );
      }
    }
	}

	public static function set_mce_btns( $mce_buttons ) {
		$defualt_buttons = array( 'bold', 'italic', 'underline', 'strikethrough', 'bullist', 'numlist', 'link', 'blockquote' );
    $merged_buttons = array_merge( $mce_buttons, $defualt_buttons );
    $unique_buttons = array_unique( $merged_buttons );
    $buttons = array_diff( $unique_buttons, ['fullscreen', 'unlink', 'wp_more', 'spellchecker', 'wp_adv'] );
          
    return $buttons;		
	}

	public static function question_choices_answer() {
		$group = get_field( 'question_choices_answer' );
		
		if ( isset( $group['choices'] ) && $group['choices'] ) { ?>
			<div class="question-choices"> 
				<h2> 보기 </h2><?php 
				echo $group['choices']; ?>
			</div> <?php
		}
		if ( isset( $group['answer'] ) && $group['answer'] ) { ?>
			<div class="question-answer"> 
				정답 : <span> <?php echo $group['answer']; ?> </span>
			</div> <?php
		}
	}

}

function ap_template_part( $slug, $name = null, $template_args = array() ) {
  $file = 'template-parts/' . "{$slug}";
  if ( ! is_null( $name ) ) {
    $file = 'template-parts/' . "{$slug}-{$name}";
	}
	$file = ANSPRESS_DIR . $file . '.php';

  if ( empty( $template_args ) ) {
    include $file;
  } else {
    $template_args = wp_parse_args( $template_args );
    ob_start();
    $return = require( $file );
    $data = ob_get_clean();
    echo $data;
  }
}

function ap_template_part_location( $slug, $name ) {
	$file = 'template-parts/' . "{$slug}";
  if ( ! is_null( $name ) ) {
    $file = 'template-parts/' . "{$slug}-{$name}";
	}
	$file = ANSPRESS_DIR . $file . '.php';

	return $file;
}

/**
 * Return AnsPress page slug.
 *
 * @param string $slug Default page slug.
 * @return string
 */
function ap_get_page_slug( $slug ) {
	$option = ap_opt( $slug . '_page_slug' );

	if ( ! empty( $option ) ) {
		$slug = $option;
	}

	return apply_filters( 'ap_page_slug_' . $slug, $slug );
}

/**
 * Register anspress pages.
 *
 * @param string   $page_slug    slug for links.
 * @param string   $page_title   Page title.
 * @param callable $func         Hook to run when shortcode is found.
 * @param bool     $show_in_menu User can add this pages to their WordPress menu from appearance->menu->AnsPress.
 * @param bool     $private Only show to currently logged in user?
 *
 * @since 2.0.1
 */
function ap_register_page( $page_slug, $page_title, $func, $show_in_menu = true, $private = false ) {
	anspress()->pages[ $page_slug ] = array(
		'title'        => $page_title,
		'func'         => $func,
		'show_in_menu' => $show_in_menu,
		'private'      => $private,
	);
}

/**
 * Check if question is closed.
 *
 * @param boolean|integer $post_id question or answer ID.
 * @return boolean
 * @since 2.0.0
 */
function ap_is_post_closed( $post_id = null ) {
	if ( '1' === ap_get_post_field( 'closed', $post_id ) ) {
		return true;
	}
	return false;
}

/**
 * Return current page title.
 *
 * @return string current title
 * @since unknown
 * @since 4.1.0 Removed `question_name` query var check.
 */
function ap_page_title() {
	$new_title = '';
	$new_title = apply_filters( 'ap_page_title', $new_title );

	return $new_title;
}

/**
 * Output current AnsPress page.
 *
 * @param string $current_page Pass current page to override.
 *
 * @since 2.0.0
 * @since 4.1.9 Fixed: page attribute not working.
 */
function ap_page( $current_page = '' ) {
	$pages = anspress()->pages;
	  
	if ( empty( $current_page ) ) {
		$current_page = ap_current_page();
		$current_page = '' === $current_page ? 'base' : $current_page;
	}

	if ( isset( $pages[ $current_page ]['func'] ) ) {
		call_user_func( $pages[ $current_page ]['func'] );
	} else {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		ap_template_part( 'not-found' );
	}
}

/**
 * Get all list filters.
 *
 * @param string $current_url Current URL.
 */
function ap_get_list_filters() {
	$param    = array();
	$search_q = get_query_var( 'ap_s' );

	if ( ! empty( $search_q ) ) {
		$param['ap_s'] = $search_q;
	}

	$filters = array(
		'order_by' => array(
			'title'    => __( 'Order By', 'anspress-question-answer' ),
			'items'    => [],
			'multiple' => false,
		),
	);

	/*
	 * Filter question sorting.
	 * @param array Question sortings.
	 * @since 2.3
	 */
	return apply_filters( 'ap_list_filters', $filters );
}

/**
 * Return post actions array.
 *
 * @param mixed $_post Post.
 * @return array
 * @since  3.0.0
 */
function ap_post_actions( $_post = null ) {
	$_post = ap_get_post( $_post );

	$actions = [];

	if ( ! in_array( $_post->post_type, [ 'question', 'answer' ], true ) ) {
		return $actions;
	}

	// Edit link.
	$actions[] = array(
		'cb'    => 'edit_post',
		'query' => [
			'post_id' => $_post->ID,
			'__nonce' => wp_create_nonce( 'edit_post_' . $_post->ID ),
		],
		'label' => __( 'Edit', 'anspress-question-answer' ),
	);

	// Permanent delete link.
	$actions[] = array(
		'cb'    => 'delete_permanently',
		'query' => [
			'post_id' => $_post->ID,
			'__nonce' => wp_create_nonce( 'delete_post_' . $_post->ID ),
		],
		'label' => __( 'Delete Permanently', 'anspress-question-answer' ),
		'title' => __( 'Delete post permanently (cannot be restored again)', 'anspress-question-answer' ),
	);

	/**
	 * For filtering post actions buttons
	 *
	 * @var     string
	 * @since   2.0
	 */
	$actions = apply_filters( 'ap_post_actions', array_filter( $actions ) );
	return array_values( $actions );
}

/**
 * Return current AnsPress page
 *
 * @return string
 * @since unknown
 * @since 4.1.0 Check if ask question page.
 * @since 4.1.1 Do not return `base` by default.
 * @since 4.1.2 If 404 do not return anything.
 * @since 4.1.9 Changed cache key which was causing conflict with core.
 */
function ap_current_page() {
	static $ret = null;

	if ( null !== $ret ) {
		return $ret;
	}

	$query_var  = get_query_var( 'ap_page', '' );	 
	$main_pages = array_keys( ap_main_pages() );
	$page_ids   = [];

	foreach ( $main_pages as $page_slug ) {
		$page_ids[ ap_opt( $page_slug ) ] = $page_slug;
	}

	if ( is_question() || is_singular( 'question' ) ) {
		$query_var = 'question';
	} elseif ( 'edit' === $query_var ) {
		$query_var = 'edit';
	} elseif ( in_array( $query_var . '_page', $main_pages, true ) ) {
		$query_var = $query_var;
	} elseif ( in_array( get_the_ID(), array_keys( $page_ids ) ) ) {
		$query_var = str_replace( '_page', '', $page_ids[ get_the_ID() ] );
	} elseif ( 'base' === $query_var ) {
		$query_var = 'base';
	} elseif ( is_404() ) {
		$query_var = '';
	}

	/**
	 * Filter AnsPress current page.
	 *
	 * @param    string $query_var Current page slug.
	 */
	$ret = apply_filters( 'ap_current_page', esc_attr( $query_var ) );

	return $ret;
}

/**
 * Create array of object containing AnsPress pages. To be used in admin menu metabox.
 *
 * @return array
 * @since unknown
 * @since 4.1.0 Improved ask page object.
 */
function ap_menu_obejct() {
	$menu_items = [];

	foreach ( (array) anspress()->pages as $k => $args ) {
		if ( $args['show_in_menu'] ) {
			$object_id = 1;
			$object    = $k;
			$title     = $args['title'];
			$url       = home_url( '/' );
			$type      = 'anspress-links';

			$main_pages = array_keys( ap_main_pages() );

			if ( in_array( $k . '_page', $main_pages, true ) ) {
				$post      = get_post( ap_opt( $k . '_page' ) );
				$object_id = ap_opt( $k . '_page' );
				$object    = 'page';
				$url       = get_permalink( $post );
				$title     = $post->post_title;
				$type      = 'post_type';
			}

			$menu_items[] = (object) array(
				'ID'               => 1,
				'db_id'            => 0,
				'menu_item_parent' => 0,
				'object_id'        => $object_id,
				'post_parent'      => 0,
				'type'             => $type,
				'object'           => $object,
				'type_label'       => __( 'AnsPress links', 'anspress-question-answer' ),
				'title'            => $title,
				'url'              => $url,
				'target'           => '',
				'attr_title'       => '',
				'description'      => '',
				'classes'          => [ 'anspress-menu-' . $k ],
				'xfn'              => '',
			);
		} // End if().
	} // End foreach().

	/**
	 * Hook for filtering default AnsPress menu objects.
	 *
	 * @param array $menu_items Array of menu objects.
	 * @since 4.1.2
	 */
	return apply_filters( 'ap_menu_object', $menu_items );
}

/**
 * Post actions buttons.
 *
 * @since   2.0
 */
function ap_post_actions_buttons() {
	global $post;
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return;
	}
	if ( is_super_admin( $user_id ) || ap_is_moderator( $user_id ) || ap_is_expert( $user_id ) ) {
		return;
	}
	if ( $user_id != (int)$post->post_author ) {
		return;
	}

	$args = wp_json_encode( [
		'post_id' => get_the_ID(),
		'nonce'   => wp_create_nonce( 'post-actions-' . get_the_ID() ),
	] );

	echo '<postActions class="ap-dropdown"><button class="ap-btn apicon-gear ap-actions-handle ap-dropdown-toggle" ap="actiontoggle" apquery="' . esc_js( $args ) . '"></button><ul class="ap-actions ap-dropdown-menu"></ul></postActions>';
}

/**
 * Output answers tab.
 *
 * @param string|boolean $base Current page url.
 * @since 2.0.1
 */
function ap_answers_tab( $base = false ) {
	$sort = ap_sanitize_unslash( 'order_by', 'r', ap_opt( 'answers_sort' ) );

	if ( ! $base ) {
		$base = get_permalink();
	}

	$navs = array(
		'active' => array(
			'link'  => add_query_arg( [ 'order_by' => 'active' ], $base ),
			'title' => __( 'Active', 'anspress-question-answer' ),
		),
	);

	$navs['voted'] = array(
		'link'  => add_query_arg( [ 'order_by' => 'voted' ], $base ),
		'title' => __( 'Voted', 'anspress-question-answer' ),
	);

	$navs['newest'] = array(
		'link'  => add_query_arg( [ 'order_by' => 'newest' ], $base ),
		'title' => __( 'Newest', 'anspress-question-answer' ),
	);
	$navs['oldest'] = array(
		'link'  => add_query_arg( [ 'order_by' => 'oldest' ], $base ),
		'title' => __( 'Oldest', 'anspress-question-answer' ),
	);

	echo '<ul id="answers-order" class="ap-answers-tab ap-ul-inline clearfix">';
	foreach ( (array) $navs as $k => $nav ) {
		echo '<li' . ( $sort === $k ? ' class="active"' : '' ) . '><a href="' . esc_url( $nav['link'] . '#answers-order' ) . '">' . esc_attr( $nav['title'] ) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * Anspress pagination
 * Uses paginate_links.
 *
 * @param float  $current Current paged, if not set then get_query_var('paged') is used.
 * @param int    $total   Total number of pages, if not set then global $questions is used.
 * @param string $format  pagination format.
 * @param string $page_num_link  Base link.
 * @return string
 */
function ap_pagination( $current = false, $total = false, $format = '?paged=%#%', $page_num_link = false ) {
	global $ap_max_num_pages, $ap_current;

	if ( is_front_page() ) {
		$format = '';
	}

	$big = 999999999; // Need an unlikely integer.

	if ( false === $current ) {
		$paged   = ap_sanitize_unslash( 'ap_paged', 'r', 1 );
		$current = is_front_page() ? max( 1, $paged ) : max( 1, get_query_var( 'paged' ) );
	} elseif ( ! empty( $ap_current ) ) {
		$current = $ap_current;
	}

	if ( ! empty( $ap_max_num_pages ) ) {
		$total = $ap_max_num_pages;
	} elseif ( false === $total && isset( anspress()->questions->max_num_pages ) ) {
		$total = anspress()->questions->max_num_pages;
	}

	if ( false === $page_num_link ) {
		$page_num_link = str_replace( array( '&amp;', '&#038;' ), '&', get_pagenum_link( $big ) );
	}

	$base = str_replace( $big, '%#%', $page_num_link );

	if ( '1' == $total ) { // WPCS: loose comparison ok.
		return;
	}

	echo '<div class="ap-pagination clearfix">';
	$links = paginate_links( array( // WPCS: xss okay.
		'base'     => $base,
		'format'   => $format,
		'current'  => $current,
		'total'    => $total,
		'end_size' => 2,
		'mid_size' => 2,
	) );
	$links = str_replace('<a class="next page-numbers"', '<a class="next page-numbers" rel="next"', $links);
	$links = str_replace('<a class="prev page-numbers"', '<a class="prev page-numbers" rel="prev"', $links);
	echo $links;
	echo '</div>';
}

/**
 * Print select anser HTML button.
 *
 * @param mixed $_post Post.
 * @return string
 */
function ap_select_answer_btn_html( $_post = null ) {

	if ( ! ap_user_can_select_answer( $_post ) ) {
		return;
	}

	$_post = ap_get_post( $_post );
	$nonce = wp_create_nonce( 'select-answer-' . $_post->ID );

	$q = esc_js( wp_json_encode( [
		'answer_id' => $_post->ID,
		'__nonce'   => $nonce,
	] ) );

	$active = false;

	$title = __( 'Select this answer as best', 'anspress-question-answer' );
	$label = '채택';

	$have_best = ap_have_answer_selected( $_post->post_parent );
	$selected  = ap_is_selected( $_post );
	$hide      = false;

	if ( $have_best && $selected ) {
		if ( ! ap_opt( 'allow_unselect_answer' ) ) {
			return;
		}

		$title  = __( 'Unselect this answer', 'anspress-question-answer' );
		$label  = __( 'Unselect', 'anspress-question-answer' );
		$active = true;
	}

	if ( $have_best && ! $selected ) {
		$hide = true;
	}

	return '<a href="#" class="ap-btn ' . ( $active ? ' active' : '' ) . ( $hide ? ' hide' : '' ) . '" ap="select-answer-modal-open" apquery="' . $q . '" title="' . $title . '">' . $label . '</a>';
}

function ap_get_copyright_content() {
  $output = '<div class="giveup-copyright">';
  $output .= get_option( 'giveup_copyright', 'nothing' );
  $output .= '</div>';
  return $output;
}

/**
 * Post status message.
 *
 * @param mixed $post_id Post.
 * @return string
 * @since 4.0.0
 */
function ap_get_post_status_message( $post_id = false ) {
	$post      = ap_get_post( $post_id );
	$post_type = 'question' === $post->post_type ? __( 'Question', 'anspress-question-answer' ) : __( 'Answer', 'anspress-question-answer' );

	$ret = '';
	$msg = '';
	if ( ap_is_post_closed( $post_id ) ) {
		$ret = '<i class="apicon-x"></i><span>' . __( 'Question is closed for new answers.', 'anspress-question-answer' ) . '</span>';
	} elseif ( 'trash' === $post->post_status ) {
		$ret = '<i class="apicon-trashcan"></i><span>' . sprintf( __( 'This %s has been trashed, you can delete it permanently from wp-admin.', 'anspress-question-answer' ), $post_type ) . '</span>';
	}

	if ( ! empty( $ret ) ) {
		$msg = '<div class="ap-notice status-' . $post->post_status . ( ap_is_post_closed( $post_id ) ? ' closed' : '' ) . '">' . $ret . '</div>';
	}

	return apply_filters( 'ap_get_post_status_message', $msg, $post_id );
}

function ap_page_class() {
  $class = '';
  if ( is_front_page() ) {
    $class = 'front-page';
  }
  echo $class;
}

function ap_print_icon() {
  $url = ANSPRESS_URL . 'assets/images/print-icon.png'; ?>
  <a href="javascript:window.print();" class="print-btn"> <img src="<?php echo $url; ?>" alt=""> </a> <?php
}