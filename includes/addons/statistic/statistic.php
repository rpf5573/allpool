<?php

require_once 'class-term-statistic-list-table.php';
require_once 'class-yas-statistic-list-table.php';

class AP_Statistic {

	public static $slug_label = array(
		'term_name'					=> '카테고리',
		'year'           		=> '년도',
		'session'        		=> '회차',
		'questions'					=> '질문',
		'answers'						=> '답변',
		'did_select_answer' => '답변채택',
		'vote_to_question'	=> '추천(질문)',
		'vote_to_answer'		=> '추천(답변)',
		'moderate_question'	=> '검수미완료(질문)',
		'moderate_answer'		=> '검수미완료(답변)',
		'income_of_answer'	=> '추천수익'
	);

  public static function add_statistic_submenu() {
		//add_submenu_page( 'anspress', __( 'Questions Category', 'anspress-question-answer' ), __( 'Category', 'anspress-question-answer' ), 'manage_options', 'edit-tags.php?taxonomy=question_category' );
    add_submenu_page( 'anspress', __( 'Statistic', 'anspress-question-answer' ), __( 'Statistic', 'anspress-question-answer' ), 'delete_pages', 'ap_statistic', array( __CLASS__, 'display_term_statistic_page' ) );
	}
	
  /**
	 * Control the output of question selection.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public static function display_term_statistic_page() {
		//Create an instance of our package class...
    $statistic_list_table = new AP_Term_Statistic_List_Table();
    //Fetch, prepare, sort, and filter our data...
		$statistic_list_table->prepare_items();	?>
    <div class="statistic-table-container">
			<div class="statistic-title">
				<h1> 카테고리별 통계 </h1>
				<i class="fas fa-4x fa-angle-double-right go-to-yas"></i>
			</div>
			<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
			<form class="list-table-form terms" method="get">
				<!-- For plugins, we also need to ensure that the form posts back to our current page -->
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<!-- Now we can render the completed list table -->
				<div class="wrapper">
				<?php $statistic_list_table->display(); ?>
				</div>
			</form>
    </div>
		<?php
	}

	public static function display_yas_statistic_page( $args ) {	
		//Create an instance of our package class...
    $statistic_list_table = new AP_YaS_Statistic_List_Table( $args );
    //Fetch, prepare, sort, and filter our data...
		$statistic_list_table->prepare_items(); ?>
    <div class="statistic-table-container">
			<div class="statistic-title">
				<i class="fas fa-4x fa-angle-double-left back-to-terms"></i>
				<h1> <span class="term_name"> <?php echo $args['term_name']; ?></span> - 년도/회차별 통계 페이지 </h1>
			</div>
			<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
			<form class="list-table-form yas" method="get">
				<!-- For plugins, we also need to ensure that the form posts back to our current page -->
				<!-- Now we can render the completed list table -->
				<div class="wrapper">
				<?php $statistic_list_table->display(); ?>
				</div>
			</form>
		</div> <?php
	}

	/*  Term Filters
	/* --------------------------------------------------- */
	public static function term_filter_question( $sql, $instance ) {
		$filter = ap_isset_post_value( 'term_filter' );
		if ( $filter == 'term_name' ) {
			$term_id = ap_isset_post_value( 'term_id' );
			global $pagenow, $wpdb;
			$vars = $instance->query_vars;
			if ( isset( $vars['post_type'] ) && $vars['post_type'] != 'question' ) {
				return $sql;
			}

			$terms = ap_get_term_family( $term_id );
			$sql['join'] .= " LEFT JOIN {$wpdb->term_relationships} AS term_relationships
													ON term_relationships.object_id={$wpdb->posts}.ID";
			$sql['where'] .= " AND ( term_relationships.term_taxonomy_id IN ($terms) )";
		}

		return $sql;
	}

	public static function term_filter_answer( $sql, $instance ) {
		$filter = ap_isset_post_value( 'term_filter' );
		if ( $filter == 'term_name' ) {
			$term_id = ap_isset_post_value( 'term_id' );
			global $pagenow, $wpdb;
			$vars = $instance->query_vars;

			if ( isset( $vars['post_type'] ) && $vars['post_type'] != 'answer' ) {
				return $sql;
			}

			$terms = ap_get_term_family( $term_id );

			$sql['join'] .= " LEFT JOIN {$wpdb->term_relationships} AS term_relationships
													ON term_relationships.object_id={$wpdb->posts}.post_parent";
			$sql['where'] .= " AND ( term_relationships.term_taxonomy_id IN ($terms) )";
		}

		return $sql;
	}

	public static function term_filter_question_with_did_select_answer( $sql, $instance ) {
		$filter = ap_isset_post_value( 'term_filter' );
		if ( $filter == 'did_select_answer' ) {
			$term_id = ap_isset_post_value( 'term_id' );
			global $pagenow, $wpdb;
			$vars = $instance->query_vars;
			if ( isset( $vars['post_type'] ) && $vars['post_type'] != 'question' ) {
				return $sql;
			}

			$terms = ap_get_term_family( $term_id );
			$sql['join'] .= " LEFT JOIN {$wpdb->term_relationships} AS term_relationships
													ON term_relationships.object_id={$wpdb->posts}.ID";
			$sql['where'] .= " AND ( term_relationships.term_taxonomy_id IN ($terms) )";
			$sql['where'] .= " AND qameta.selected_id IS NOT NULL";
		}

		return $sql;
	}

	public static function term_filter_question_with_vote( $sql, $instance ) {
		$filter = ap_isset_post_value( 'term_filter' );
		if ( $filter == 'vote' ) {
			$term_id = ap_isset_post_value( 'term_id' );
			global $pagenow, $wpdb;
			$vars = $instance->query_vars;
			if ( isset( $vars['post_type'] ) && $vars['post_type'] != 'question' ) {
				return $sql;
			}

			$terms = ap_get_term_family( $term_id );
			$sql['join'] .= " LEFT JOIN {$wpdb->term_relationships} AS term_relationships
													ON term_relationships.object_id={$wpdb->posts}.ID";
			$sql['where'] .= " AND ( term_relationships.term_taxonomy_id IN ($terms) )";
			$sql['where'] .= " AND (qameta.votes_up - qameta.votes_down) > 0";
		}

		return $sql;
	}

	public static function term_filter_answer_with_vote( $sql, $instance ) {
		$filter = ap_isset_post_value( 'term_filter' );
		if ( $filter == 'vote' ) {
			$term_id = ap_isset_post_value( 'term_id' );
			global $pagenow, $wpdb;
			$vars = $instance->query_vars;
			if ( isset( $vars['post_type'] ) && $vars['post_type'] != 'answer' ) {
				return $sql;
			}

			$terms = ap_get_term_family( $term_id );
			$sql['join'] .= " LEFT JOIN {$wpdb->term_relationships} AS term_relationships
													ON term_relationships.object_id={$wpdb->posts}.post_parent";
			$sql['where'] .= " AND ( term_relationships.term_taxonomy_id IN ($terms) )";
			$sql['where'] .= " AND (qameta.votes_up - qameta.votes_down) > 0";
		}

		return $sql;
	}

	public static function term_filter_question_with_inspection_check( $sql, $instance ) {
		$filter = ap_isset_post_value( 'term_filter' );
		if ( $filter == 'inspection_check' ) {
			$term_id = ap_isset_post_value( 'term_id' );
			global $pagenow, $wpdb;
			$vars = $instance->query_vars;
			if ( isset( $vars['post_type'] ) && $vars['post_type'] != 'question' ) {
				return $sql;
			}

			$terms = ap_get_term_family( $term_id );
			$sql['join'] .= " LEFT JOIN {$wpdb->term_relationships} AS term_relationships
													ON term_relationships.object_id={$wpdb->posts}.ID";
			$sql['where'] .= " AND ( term_relationships.term_taxonomy_id IN ($terms) )";
			$sql['where'] .= " AND (qameta.inspection_check = 0)";
		}

		return $sql;
	}

	public static function term_filter_answer_with_inspection_check( $sql, $instance ) {
		$filter = ap_isset_post_value( 'term_filter' );
		if ( $filter == 'inspection_check' ) {
			$term_id = ap_isset_post_value( 'term_id' );
			global $pagenow, $wpdb;
			$vars = $instance->query_vars;
			if ( isset( $vars['post_type'] ) && $vars['post_type'] != 'answer' ) {
				return $sql;
			}

			$terms = ap_get_term_family( $term_id );
			$sql['join'] .= " LEFT JOIN {$wpdb->term_relationships} AS term_relationships
													ON term_relationships.object_id={$wpdb->posts}.post_parent";
			$sql['where'] .= " AND ( term_relationships.term_taxonomy_id IN ($terms) )";
			$sql['where'] .= " AND (qameta.inspection_check = 0)";
		}

		return $sql;
	}

	/*  Year & Session Filters
	/* --------------------------------------------------- */
	public static function yas_filter_question( $sql, $instance ) {
		$filter = ap_isset_post_value( 'yas_filter' );
		if ( $filter == 'term_name' ) {
			$term_id = ap_isset_post_value( 'term_id' );
			$year = ap_isset_post_value( 'ap_year' );
			$session = ap_isset_post_value( 'ap_session' );
			global $pagenow, $wpdb;
			$vars = $instance->query_vars;
			if ( isset( $vars['post_type'] ) && $vars['post_type'] != 'question' ) {
				return $sql;
			}

			$terms = ap_get_term_family( $term_id );

			$sql['join'] .= " LEFT JOIN {$wpdb->term_relationships} AS term_relationships
													ON term_relationships.object_id={$wpdb->posts}.ID";
			$sql['where'] .= " AND ( term_relationships.term_taxonomy_id IN ($terms) )
												AND qameta.year = {$year}
												AND qameta.session = {$session}";
		}

		return $sql;
	}

	public static function yas_filter_answer( $sql, $instance ) {
		$filter = ap_isset_post_value( 'yas_filter' );
		if ( $filter == 'term_name' ) {
			$term_id = ap_isset_post_value( 'term_id' );
			$year = ap_isset_post_value( 'ap_year' );
			$session = ap_isset_post_value( 'ap_session' );
			global $pagenow, $wpdb;
			$vars = $instance->query_vars;

			if ( isset( $vars['post_type'] ) && $vars['post_type'] != 'answer' ) {
				return $sql;
			}

			$terms = ap_get_term_family( $term_id );
			$q_ids = ap_get_question_ids( $year, $session, $terms );
			if ( ! empty( $q_ids ) ) {
				$ids = implode( ',', $q_ids );
				$sql['join'] .= " LEFT JOIN {$wpdb->term_relationships} AS term_relationships
														ON term_relationships.object_id={$wpdb->posts}.post_parent";
				$sql['where'] .= " AND ( term_relationships.term_taxonomy_id IN ($terms) )
													AND {$wpdb->posts}.post_parent IN ($ids)";
			}
		}

		return $sql;
	}

	public static function yas_filter_question_with_did_select_answer( $sql, $instance ) {
		$filter = ap_isset_post_value( 'yas_filter' );
		if ( $filter == 'did_select_answer' ) {
			$term_id = ap_isset_post_value( 'term_id' );
			$year = ap_isset_post_value( 'ap_year' );
			$session = ap_isset_post_value( 'ap_session' );
			global $pagenow, $wpdb;
			$vars = $instance->query_vars;
			if ( isset( $vars['post_type'] ) && $vars['post_type'] != 'question' ) {
				return $sql;
			}

			$terms = ap_get_term_family( $term_id );
			$sql['join'] .= " LEFT JOIN {$wpdb->term_relationships} AS term_relationships
													ON term_relationships.object_id={$wpdb->posts}.ID";
			$sql['where'] .= " AND ( term_relationships.term_taxonomy_id IN ($terms) )
												AND qameta.selected_id IS NOT NULL 
												AND qameta.year = {$year}
												AND qameta.session = {$session}";

			\PC::debug( ['sql' => $sql], __FUNCTION__ );
		}

		return $sql;
	}

	public static function yas_filter_question_with_vote( $sql, $instance ) {
		$filter = ap_isset_post_value( 'yas_filter' );
		if ( $filter == 'vote' ) {
			$term_id = ap_isset_post_value( 'term_id' );
			$year = ap_isset_post_value( 'ap_year' );
			$session = ap_isset_post_value( 'ap_session' );
			global $pagenow, $wpdb;
			$vars = $instance->query_vars;
			if ( isset( $vars['post_type'] ) && $vars['post_type'] != 'question' ) {
				return $sql;
			}

			$terms = ap_get_term_family( $term_id );
			$sql['join'] .= " LEFT JOIN {$wpdb->term_relationships} AS term_relationships
													ON term_relationships.object_id={$wpdb->posts}.ID";
			$sql['where'] .= " AND ( term_relationships.term_taxonomy_id IN ($terms) )
												AND (qameta.votes_up - qameta.votes_down) > 0
												AND qameta.year = {$year}
												AND qameta.session = {$session}";
		}

		return $sql;
	}

	public static function yas_filter_answer_with_vote( $sql, $instance ) {
		$filter = ap_isset_post_value( 'yas_filter' );
		if ( $filter == 'vote' ) {
			$term_id = ap_isset_post_value( 'term_id' );
			$year = ap_isset_post_value( 'ap_year' );
			$session = ap_isset_post_value( 'ap_session' );
			global $pagenow, $wpdb;
			$vars = $instance->query_vars;
			if ( isset( $vars['post_type'] ) && $vars['post_type'] != 'answer' ) {
				return $sql;
			}

			$terms = ap_get_term_family( $term_id );
			$q_ids = ap_get_question_ids( $year, $session, $terms );
			if ( ! empty( $q_ids ) ) {
				$ids = implode( ',', $q_ids );
				$sql['join'] .= " LEFT JOIN {$wpdb->term_relationships} AS term_relationships
														ON term_relationships.object_id={$wpdb->posts}.post_parent";
				$sql['where'] .= " AND ( term_relationships.term_taxonomy_id IN ($terms) )
													AND (qameta.votes_up - qameta.votes_down) > 0
													AND {$wpdb->posts}.post_parent IN ($ids)";
			}
		}

		return $sql;
	}

	public static function yas_filter_question_with_inspection_check( $sql, $instance ) {
		$filter = ap_isset_post_value( 'yas_filter' );
		if ( $filter == 'inspection_check' ) {
			$term_id = ap_isset_post_value( 'term_id' );
			$year = ap_isset_post_value( 'ap_year' );
			$session = ap_isset_post_value( 'ap_session' );
			global $pagenow, $wpdb;
			$vars = $instance->query_vars;
			if ( isset( $vars['post_type'] ) && $vars['post_type'] != 'question' ) {
				return $sql;
			}

			$terms = ap_get_term_family( $term_id );
			$sql['join'] .= " LEFT JOIN {$wpdb->term_relationships} AS term_relationships
													ON term_relationships.object_id={$wpdb->posts}.ID";
			$sql['where'] .= " AND ( term_relationships.term_taxonomy_id IN ($terms) )
												AND (qameta.inspection_check = 0)
												AND qameta.year = {$year}
												AND qameta.session = {$session}";
		}

		return $sql;
	}

	public static function yas_filter_answer_with_inspection_check( $sql, $instance ) {
		$filter = ap_isset_post_value( 'yas_filter' );
		if ( $filter == 'inspection_check' ) {
			$term_id = ap_isset_post_value( 'term_id' );
			$year = ap_isset_post_value( 'ap_year' );
			$session = ap_isset_post_value( 'ap_session' );
			global $pagenow, $wpdb;
			$vars = $instance->query_vars;
			if ( isset( $vars['post_type'] ) && $vars['post_type'] != 'answer' ) {
				return $sql;
			}

			$terms = ap_get_term_family( $term_id );
			$q_ids = ap_get_question_ids( $year, $session, $terms );
			if ( ! empty( $q_ids ) ) {
				$ids = implode( ',', $q_ids );
				$sql['join'] .= " LEFT JOIN {$wpdb->term_relationships} AS term_relationships
														ON term_relationships.object_id={$wpdb->posts}.post_parent";
				$sql['where'] .= " AND ( term_relationships.term_taxonomy_id IN ($terms) )
													AND (qameta.inspection_check = 0)
													AND {$wpdb->posts}.post_parent IN ($ids)";
			}
		}

		return $sql;
	}
	
	/*  Uncategorized
	/* --------------------------------------------------- */

	public static function show_statistic_term_filter_result() {
		global $pagenow;
		$filter = ap_isset_post_value( 'term_filter' );
		if ( ( $filter ) && $pagenow == 'edit.php' ) { 
			$output = '';
			$term_name = ap_isset_post_value( 'term_name' );

			if ( $term_name ) {
				$output .= ('카테고리 : ' . $term_name);
			}

			$output .= '<br>';

			if ( $filter != 'term_name' ) {
				$output .= '추가필터 : ';

				switch( $filter ) {
					case 'did_select_answer' :
						$output .= '답변채택';
						break;

					case 'vote' :
						$output .= '추천';
						break;

					case 'inspection_check' :
						$output .= '검수미완료';
						break;
				} 
			} ?>
			<div class="notice notice-warning">
				<p> <?php
					echo $output; ?>
				</p>
			</div> <?php
		}
	}

	public static function show_statistic_yas_filter_result() {
		global $pagenow;
		$filter = ap_isset_post_value( 'yas_filter' );
		if ( ( $filter ) && $pagenow == 'edit.php' ) { 
			$output = '';
			$term_name = ap_isset_post_value( 'term_name' );

			if ( $term_name ) {
				$output .= ('카테고리 : ' . $term_name);
			}

			$year = ap_isset_post_value( 'ap_year' );
			$session = ap_isset_post_value( 'ap_session' );
			if ( $year && $session ) {
				$output .= '(' . $year . '년도 ' . $session . '회차' . ')';
			}

			$output .= '<br>';

			if ( $filter != 'term_name' ) {
				$output .= '추가필터 : ';
				switch( $filter ) {
					case 'did_select_answer' :
						$output .= '답변채택';
						break;

					case 'vote' :
						$output .= '추천';
						break;

					case 'inspection_check' :
						$output .= '검수미완료';
						break;
				} 
			} ?>
			<div class="notice notice-warning">
				<p> <?php
					echo $output; ?>
				</p>
			</div> <?php
		}
	}

	public static function sync_yas_with_question( $qameta, $post, $updated ) {
		global $wpdb;
		$prefix = $wpdb->prefix;
		\PC::debug( ['qameta' => $qameta], __FUNCTION__ );
		
		if ( $post->post_type == 'question' ) {
			$year = (int)$qameta['year'];
			$session = (int)$qameta['session'];

			if ( $updated && isset( $qameta['answers'] ) && (int)$qameta['answers'] > 0 ) {
				$sql = "UPDATE {$prefix}ap_qameta AS qameta 
								LEFT JOIN {$prefix}posts AS posts
								ON qameta.post_id = posts.ID
								SET `year` = {$year}, `session` = {$session}
								WHERE qameta.post_id = ";
			}
		} else if ( $post->post_type == 'answer' ) {
			if ( ! $updated ) {

			}
		}

		return $qameta;
	}
}