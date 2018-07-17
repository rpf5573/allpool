<?php
/**
 * Post table hooks.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

// Die if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Post table hooks.
 */
class AP_Post_Table_Hooks {

	/**
	 * Initialize the class
	 */
	public static function init() {
		anspress()->add_action( 'posts_clauses', __CLASS__, 'posts_clauses', 10, 2 );
		anspress()->add_action( 'manage_answer_posts_custom_column', __CLASS__, 'answer_row_actions', 10, 2 );
		anspress()->add_filter( 'manage_edit-question_columns', __CLASS__, 'cpt_question_columns' );
		anspress()->add_action( 'manage_posts_custom_column', __CLASS__, 'custom_columns_value' );
    anspress()->add_filter( 'manage_edit-answer_columns', __CLASS__, 'cpt_answer_columns' );
    anspress()->add_filter( 'manage_edit-question_sortable_columns', __CLASS__, 'admin_column_sort_flag' );
		anspress()->add_filter( 'manage_edit-answer_sortable_columns', __CLASS__, 'admin_column_sort_flag' );
		anspress()->add_action( 'edit_form_after_title', __CLASS__, 'edit_form_after_title' );
		anspress()->add_filter( 'post_updated_messages', __CLASS__, 'post_custom_message' );

		// statistic - terms
		anspress()->add_action( 'posts_clauses', 'AP_Statistic', 'term_filter_question', 100, 2 );
		anspress()->add_action( 'posts_clauses', 'AP_Statistic', 'term_filter_answer', 100, 2 );
		anspress()->add_action( 'posts_clauses', 'AP_Statistic', 'term_filter_question_with_did_select_answer', 100, 2 );
		anspress()->add_action( 'posts_clauses', 'AP_Statistic', 'term_filter_question_with_vote', 100, 2 );
		anspress()->add_action( 'posts_clauses', 'AP_Statistic', 'term_filter_answer_with_vote', 100, 2 );
		anspress()->add_action( 'posts_clauses', 'AP_Statistic', 'term_filter_question_with_inspection_check', 100, 2 );
		anspress()->add_action( 'posts_clauses', 'AP_Statistic', 'term_filter_answer_with_inspection_check', 100, 2 );

		// statistic - yas
		anspress()->add_action( 'posts_clauses', 'AP_Statistic', 'yas_filter_question', 100, 2 );
		anspress()->add_action( 'posts_clauses', 'AP_Statistic', 'yas_filter_answer', 100, 2 );
		anspress()->add_action( 'posts_clauses', 'AP_Statistic', 'yas_filter_question_with_did_select_answer', 100, 2 );
		anspress()->add_action( 'posts_clauses', 'AP_Statistic', 'yas_filter_question_with_vote', 100, 2 );
		anspress()->add_action( 'posts_clauses', 'AP_Statistic', 'yas_filter_answer_with_vote', 100, 2 );
		anspress()->add_action( 'posts_clauses', 'AP_Statistic', 'yas_filter_question_with_inspection_check', 100, 2 );
		anspress()->add_action( 'posts_clauses', 'AP_Statistic', 'yas_filter_answer_with_inspection_check', 100, 2 );

		// statistic - tag
		anspress()->add_action( 'posts_clauses', 'AP_Statistic', 'tag_filter_question', 100, 2 );
		anspress()->add_action( 'posts_clauses', 'AP_Statistic', 'tag_filter_answer', 100, 2 );

		// statistic - user page
		anspress()->add_filter( 'manage_users_columns', 'AP_Statistic', 'add_user_columns' );
		anspress()->add_filter( 'manage_users_custom_column', 'AP_Statistic', 'user_column', 999, 3 );
		anspress()->add_filter( 'posts_clauses', 'AP_Statistic', 'user_filter_question', 100, 2 );
		anspress()->add_filter( 'posts_clauses', 'AP_Statistic', 'user_filter_answer', 100, 2 );

	}

	/**
	 * Modify SQL query.
	 *
	 * @param array  $sql Sql claues.
	 * @param object $instance WP_Query instance.
	 * @return array
	 */
	public static function posts_clauses( $sql, $instance ) {
		global $pagenow, $wpdb;
		$vars = $instance->query_vars;

		if ( ! in_array( $vars['post_type'], [ 'question', 'answer' ], true ) ) {
			return $sql;
		}

		$sql['join']   = $sql['join'] . " LEFT JOIN {$wpdb->ap_qameta} qameta ON qameta.post_id = {$wpdb->posts}.ID";
		$sql['fields'] = $sql['fields'] . ', qameta.*, qameta.votes_up - qameta.votes_down AS votes_net';

		$orderby = ap_sanitize_unslash( 'orderby', 'p' );
		$order   = ap_sanitize_unslash( 'order', 'p' ) === 'asc' ? 'asc' : 'desc';

		if ( 'answers' === $orderby ) {
			// Sort by answers.
			$sql['orderby'] = " qameta.answers {$order}";
		} elseif ( 'votes' === $orderby ) {
			// Sort by answers.
			$sql['orderby'] = " votes_net {$order}";
		}

		return $sql;
	}

	/**
	 * Add action links below question/answer content in wp post list.
	 *
	 * @param  string  $column  Current column name.
	 * @param  integer $post_id Current post id.
	 */
	public static function answer_row_actions( $column, $post_id ) {
		global $post, $mode;

		if ( 'answer_content' !== $column ) {
			return;
		}

		$content = ap_truncate_chars( esc_html( get_the_excerpt() ), 90 );

		// Pregmatch will return an array and the first 80 chars will be in the first element.
		echo '<a href="' . esc_url( get_permalink( $post->post_parent ) ) . '" class="row-title">' . $content . '</a>'; // xss okay.

		// First set up some variables.
		$actions          = array();
		$post_type_object = get_post_type_object( $post->post_type ); // override ok.
		$can_edit_post    = current_user_can( $post_type_object->cap->edit_post, $post->ID );

		// Actions to delete/trash.
		if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
			if ( 'trash' === $post->post_status ) {
				$_wpnonce           = wp_create_nonce( 'untrash-post_' . $post_id );
				$url                = admin_url( 'post.php?post=' . $post_id . '&action=untrash&_wpnonce=' . $_wpnonce );
				$actions['untrash'] = "<a title='" . esc_attr( __( 'Restore this item from the Trash', 'anspress-question-answer' ) ) . "' href='" . $url . "'>" . __( 'Restore', 'anspress-question-answer' ) . '</a>';

			} elseif ( EMPTY_TRASH_DAYS ) {
				$actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move this item to the Trash', 'anspress-question-answer' ) ) . "' href='" . get_delete_post_link( $post->ID ) . "'>" . __( 'Trash', 'anspress-question-answer' ) . '</a>';
			}

			if ( 'trash' === $post->post_status || ! EMPTY_TRASH_DAYS ) {
				$actions['delete'] = "<a class='submitdelete' title='" . esc_attr( __( 'Delete this item permanently', 'anspress-question-answer' ) ) . "' href='" . get_delete_post_link( $post->ID, '', true ) . "'>" . __( 'Delete Permanently', 'anspress-question-answer' ) . '</a>';
			}
		}

		if ( $can_edit_post ) {
			$actions['edit'] = '<a href="' . get_edit_post_link( $post->ID, '', true ) . '" title="' . esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'anspress-question-answer' ), $post->title ) ) . '" rel="permalink">' . __( 'Edit', 'anspress-question-answer' ) . '</a>';
		}

		// Actions to view/preview.
		if ( in_array( $post->post_status, [ 'pending', 'draft', 'future' ], true ) && $can_edit_post ) {

			$actions['view'] = '<a href="' . esc_url( add_query_arg( 'preview', 'true', get_permalink( $post->ID ) ) ) . '" title="' . esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;', 'anspress-question-answer' ), $post->title ) ) . '" rel="permalink">' . __( 'Preview', 'anspress-question-answer' ) . '</a>';

		} elseif ( 'trash' !== $post->post_status ) {
			$actions['view'] = '<a href="' . get_permalink( $post->ID ) . '" title="' . esc_attr( __( 'View &#8220;%s&#8221; question', 'anspress-question-answer' ) ) . '" rel="permalink">' . __( 'View', 'anspress-question-answer' ) . '</a>';
		}

		// Echo the 'actions' HTML, let WP_List_Table do the hard work.
		$WP_List_Table = new WP_List_Table(); // @codingStandardsIgnoreLine
		echo $WP_List_Table->row_actions( $actions );
	}

	/**
	 * Alter columns in question cpt.
	 *
	 * @param  array $columns Table column.
	 * @return array
	 * @since  2.0.0
	 */
	public static function cpt_question_columns( $columns ) {
		$columns              = array();
		$columns['cb']        = '<input type="checkbox" />';
		$columns['ap_author'] = __( 'Author', 'anspress-question-answer' );
		$columns['title']     = __( 'Title', 'anspress-question-answer' );

		if ( taxonomy_exists( 'question_category' ) ) {
			$columns['question_category'] = __( 'Category', 'anspress-question-answer' );
		}

		if ( taxonomy_exists( 'question_tag' ) ) {
			$columns['question_tag'] = __( 'Tag', 'anspress-question-answer' );
		}

		$columns['status']   = __( 'Status', 'anspress-question-answer' );
		$columns['answers']  = __( 'Ans', 'anspress-question-answer' );
		$columns['votes']    = __( 'Votes', 'anspress-question-answer' );
		$columns['date']     = __( 'Date', 'anspress-question-answer' );

		return $columns;
	}

	/**
	 * Custom post table column values.
	 *
	 * @param string $column Columns name.
	 */
	public static function custom_columns_value( $column ) {
    global $post;
    
     

		if ( ! in_array( $post->post_type, [ 'question', 'answer' ], true ) ) {
			return $column;
		}

		if ( 'ap_author' === $column ) {

			echo '<a class="ap-author-col" href="' . esc_url( ap_user_link( $post->post_author ) ) . '">';
			ap_author_avatar( 28 );
			echo '<span>' . esc_attr( ap_user_display_name() ) . '</span>';
			echo '</a>';

		} elseif ( 'status' === $column ) {
			global $wp_post_statuses;
			echo '<span class="post-status">';

			if ( isset( $wp_post_statuses[ $post->post_status ] ) ) {
				echo esc_attr( $wp_post_statuses[ $post->post_status ]->label );
			}

			echo '</span>';

		} elseif ( 'question_category' === $column && taxonomy_exists( 'question_category' ) ) {

			$category = get_the_terms( $post->ID, 'question_category' );

			if ( ! empty( $category ) ) {
				$out = array();

				if ( current_user_can( 'manage_categories' ) ) {
					foreach ( (array) $category as $cat ) {
						$out[] = edit_term_link( $cat->name, '', '', $cat, false );
					}
				} else {
					foreach ( (array) $category as $cat ) {
						$out[] = $cat->name;
					}
				}

				echo join( ', ', $out ); // xss okay.
			} else {
				esc_html_e( '--', 'anspress-question-answer' );
			}
		} elseif ( 'question_tag' === $column && taxonomy_exists( 'question_tag' ) ) {

			$terms = get_the_terms( $post->ID, 'question_tag' );

			if ( ! empty( $terms ) ) {
				$out = array();

				foreach ( (array) $terms as $term ) {
					$url   = esc_url(
						add_query_arg(
							[
								'post_type'    => $post->post_type,
								'question_tag' => $term->slug,
							], 'edit.php'
						)
					);
					$out[] = sprintf( '<a href="%s">%s</a>', $url, esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'question_tag', 'display' ) ) );
				}

				echo join( ', ', $out ); // xss ok.
			} else {
				esc_attr_e( '--', 'anspress-question-answer' );
			}
		} elseif ( 'answers' === $column ) {

			$url = add_query_arg(
				array(
					'post_type'   => 'answer',
					'post_parent' => $post->ID,
				), 'edit.php'
			);
			echo '<a class="ans-count" title="' . esc_html( sprintf( _n( '%d Answer', '%d Answers', $post->answers, 'anspress-question-answer' ), (int) $post->answers ) ) . '" href="' . esc_url( $url ) . '">' . esc_attr( $post->answers ) . '</a>';

		} elseif ( 'parent_question' === $column ) {
			$url = add_query_arg(
				[
					'post'   => $post->post_parent,
					'action' => 'edit',
				], 'post.php'
			);
			echo '<a class="parent_question" href="' . esc_url( $url ) . '"><strong>' . get_the_title( $post->post_parent ) . '</strong></a>';
		} elseif ( 'votes' === $column ) {
			echo '<span class="vote-count">' . esc_attr( $post->votes_net ) . '</span>';
		}

	}

	/**
	 * Answer CPT columns.
	 *
	 * @param  array $columns Columns.
	 * @return array
	 * @since 2.0.0
	 */
	public static function cpt_answer_columns( $columns ) {
		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'ap_author'      => __( 'Author', 'anspress-question-answer' ),
			'answer_content' => __( 'Content', 'anspress-question-answer' ),
			'status'         => __( 'Status', 'anspress-question-answer' ),
			'votes'          => __( 'Votes', 'anspress-question-answer' ),
			'date'           => __( 'Date', 'anspress-question-answer' ),
		);

		return $columns;
	}

	/**
	 * Flag sorting.
	 *
	 * @param array $columns Sorting columns.
	 * @return array
	 */
	public static function admin_column_sort_flag( $columns ) {
		$columns['answers'] = 'answers';
		$columns['votes']   = 'votes';

		return $columns;
	}

	/**
	 * Show question detail above new answer.
	 *
	 * @return void
	 * @since 2.0
	 */
	public static function edit_form_after_title() {
		global $typenow, $pagenow, $post;

		if ( in_array( $pagenow, [ 'post-new.php', 'post.php' ], true ) && 'answer' === $post->post_type ) {
			$post_parent = ap_sanitize_unslash( 'action', 'g', false ) ? $post->post_parent : ap_sanitize_unslash( 'post_parent', 'g' );
			echo '<div class="ap-selected-question">';

			if ( ! isset( $post_parent ) ) {
				echo '<p class="no-q-selected">' . esc_attr__( 'This question is orphan, no question is selected for this answer', 'anspress-question-answer' ) . '</p>';
			} else {
				$q       = ap_get_post( $post_parent );
				$answers = ap_get_post_field( 'answers', $q );
				?>

				<a class="ap-q-title" href="<?php echo esc_url( get_permalink( $q->post_id ) ); ?>">
					<?php echo esc_attr( $q->post_title ); ?>
				</a>
				<div class="ap-q-meta">
					<span class="ap-a-count">
						<?php echo esc_html( sprintf( _n( '%d Answer', '%d Answers', $answers, 'anspress-question-answer' ), $answers ) ); ?>
					</span>
					<span class="ap-edit-link">|
						<a href="<?php echo esc_url( get_edit_post_link( $q->ID ) ); ?>">
							<?php esc_attr_e( 'Edit question', 'anspress-question-answer' ); ?>
						</a>
					</span>
				</div>
				<div class="ap-q-content"><?php echo $q->post_content; // xss ok. ?></div>
				<input type="hidden" name="post_parent" value="<?php echo esc_attr( $post_parent ); ?>" />

				<?php
			}
			echo '</div>';
		}
	}

	/**
	 * Custom post update message.
	 *
	 * @param array $messages Messages.
	 * @return array
	 */
	public static function post_custom_message( $messages ) {
		global $post;
		if ( 'answer' === $post->post_type && (int) ap_sanitize_unslash( 'message', 'g' ) === 99 ) {
			add_action( 'admin_notices', [ __CLASS__, 'ans_notice' ] );
		}

		return $messages;
	}

	/**
	 * Answer error when there is not any question set.
	 */
	public static function ans_notice() {
		echo '<div class="error">
				<p>' . esc_html__( 'Please fill parent question field, Answer was not saved!', 'anspress-question-answer' ) . '</p>
			</div>';
	}

	public static function output_filters( $post_type, $which ) {
		$taxonomy = get_taxonomy( 'question_category' );
		wp_dropdown_categories( array(
			'show_option_all' => sprintf( __( 'All %s', 'admin-taxonomy-filter' ), $taxonomy->label ),
			'orderby'         => 'name',
			'order'           => 'ASC',
			'hide_empty'      => false,
			'hide_if_empty'   => true,
			'selected'        => filter_input( INPUT_GET, $taxonomy->query_var, FILTER_SANITIZE_STRING ),
			'hierarchical'    => true,
			'name'            => $taxonomy->query_var,
			'taxonomy'        => $taxonomy->name,
			'value_field'     => 'slug',
		) );
	}
	
}