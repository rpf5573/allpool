<?php

require_once 'class-term-statistic-list-table.php';
require_once 'class-yas-statistic-list-table.php';
require_once 'class-tag-statistic-list-table.php';

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
    add_submenu_page( 'anspress', __( 'Statistic', 'anspress-question-answer' ), __( 'Statistic', 'anspress-question-answer' ), 'delete_pages', 'ap_statistic', array( __CLASS__, 'statistic_page' ) );
	}

	public static function statistic_page() { ?>
		<div class="statistic-group-table-container"> <?php
			// group for term and yas( yas will be added by js )
			self::display_term_statistic_table(); ?>
		</div> <?php
		self::display_tag_statistic_table();
	}
	
  /**
	 * Control the output of question selection.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public static function display_term_statistic_table() {
		//Create an instance of our package class...
    $statistic_list_table = new AP_Term_Statistic_List_Table();
    //Fetch, prepare, sort, and filter our data...
		$statistic_list_table->prepare_items();	?>
    <div class="statistic-table-container --term">
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

	public static function display_yas_statistic_table( $args ) {	
		//Create an instance of our package class...
    $statistic_list_table = new AP_YaS_Statistic_List_Table( $args );
    //Fetch, prepare, sort, and filter our data...
		$statistic_list_table->prepare_items(); ?>
    <div class="statistic-table-container --yas">
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

	public static function display_tag_statistic_table() {
		//Create an instance of our package class...
    $statistic_list_table = new AP_Tag_Statistic_List_Table();
    //Fetch, prepare, sort, and filter our data...
		$statistic_list_table->prepare_items();	?>
    <div class="statistic-table-container --tag w40">
			<div class="statistic-title">
				<h1> 태그별 통계 </h1>
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

	/*  Tag filter
	/* --------------------------------------------------- */
	public static function tag_filter_question( $sql, $instance ) {
		$filter = ap_isset_post_value( 'tag_filter' );
		if ( $filter == 'tag_name' ) {
			$tag_id = ap_isset_post_value( 'tag_id' );
			global $pagenow, $wpdb;
			$vars = $instance->query_vars;
			if ( isset( $vars['post_type'] ) && $vars['post_type'] != 'question' ) {
				return $sql;
			}
			$sql['join'] .= " LEFT JOIN {$wpdb->term_relationships} AS term_relationships
													ON term_relationships.object_id={$wpdb->posts}.ID";
			$sql['where'] .= " AND ( term_relationships.term_taxonomy_id = {$tag_id} ) ";
		}

		return $sql;
	}

	public static function tag_filter_answer( $sql, $instance ) {
		$filter = ap_isset_post_value( 'tag_filter' );
		if ( $filter == 'tag_name' ) {
			$tag_id = ap_isset_post_value( 'tag_id' );
			global $pagenow, $wpdb;
			$vars = $instance->query_vars;
			if ( isset( $vars['post_type'] ) && $vars['post_type'] != 'answer' ) {
				return $sql;
			}
			$sql['join'] .= " LEFT JOIN {$wpdb->term_relationships} AS term_relationships
													ON term_relationships.object_id={$wpdb->posts}.ID";
			$sql['where'] .= " AND ( term_relationships.term_taxonomy_id = {$tag_id} ) ";
		}

		return $sql;
	}

	/*  Users
	/* --------------------------------------------------- */
	public static function add_user_columns( $column ) {
		$column['questions'] = 'Questions';
		$column['answers'] = 'Answers';
		if ( isset( $column['posts'] ) ) {
			unset( $column['posts'] );
		}
		return $column;
	}
	public static function user_column( $val, $column_name, $user_id ) {
		switch( $column_name ) {
			case 'questions' :
				$val = self::user_questions_column( $user_id );
			break;

			case 'answers' :
				$val = self::user_answers_column( $user_id );
			break;
		}
		
		return $val;
	}
	public static function user_questions_column( $user_id ) {
		global $wpdb;
		$prefix = $wpdb->prefix;
		$type = 'question';
		$sql = self::user_get_sql( $type, $user_id );
		
		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			return self::user_get_link( $type, $user_id, $count );
		}

		return $count;
	}
	public static function user_answers_column( $user_id ) {
		global $wpdb;
		$prefix = $wpdb->prefix;
		$type = 'answer';
		$sql = self::user_get_sql( $type, $user_id );
		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			return self::user_get_link( $type, $user_id, $count );
		}

		return $count;
	}
	public static function user_get_sql( $type, $user_id ) {
		global $wpdb;
		$prefix = $wpdb->prefix;
		$sql = " SELECT COUNT(*) 
						FROM {$prefix}posts as posts
						WHERE posts.post_author = {$user_id}
						AND posts.post_type = '$type'
						AND posts.post_status = 'publish' ";

		return $sql;
	}
	public static function user_get_link( $type, $user_id, $count ) {
		$url = esc_url( admin_url( "edit.php?post_type={$type}&user_filter=user_id&user_id={$user_id}" ) );
		$link = "<a href='" . $url . "' target='_blank'>" . $count . "</a>";
		
		return $link;
	}
	public static function user_filter_question( $sql, $instance ) {
		$filter = ap_isset_post_value( 'user_filter' );
		if ( $filter == 'user_id' ) {
			$user_id = ap_isset_post_value( 'user_id' );
			global $pagenow, $wpdb;
			$vars = $instance->query_vars;
			if ( isset( $vars['post_type'] ) && $vars['post_type'] != 'question' ) {
				return $sql;
			}
			$sql['where'] .= " AND {$wpdb->posts}.post_author = {$user_id} ";
			
		}

		return $sql;
	}
	public static function user_filter_answer( $sql, $instance ) {
		$filter = ap_isset_post_value( 'user_filter' );
		if ( $filter == 'user_id' ) {
			$user_id = ap_isset_post_value( 'user_id' );
			global $pagenow, $wpdb;
			$vars = $instance->query_vars;
			if ( isset( $vars['post_type'] ) && $vars['post_type'] != 'answer' ) {
				return $sql;
			}
			$sql['where'] .= " AND {$wpdb->posts}.post_author = {$user_id} ";
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

	public static function show_statistic_tag_filter_result() {
		global $pagenow;
		$filter = ap_isset_post_value( 'tag_filter' );
		if ( ( $filter ) && $pagenow == 'edit.php' ) { 
			$output = '';
			$tag_name = ap_isset_post_value( 'tag_name' );

			if ( $tag_name ) {
				$output .= ('태그 : ' . $tag_name);
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