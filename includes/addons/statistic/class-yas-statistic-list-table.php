<?php
/**
 * List Table API: WP_Terms_List_Table class
 *
 * @package WordPress
 * @subpackage Administration
 * @since 3.1.0
 */

/**
 * Core class used to implement displaying terms in a list table.
 *
 * @since 3.1.0
 * @access private
 *
 * @see WP_List_Table
 */

if(!class_exists('AP_List_Table')){
  require_once( 'class-ap-list-table.php' );
}

// YaS => Year and Session
class AP_YaS_Statistic_List_Table extends AP_List_Table {

	private $term_id = '';

	private $callback_args;

  private $years = array();
  
  private $session = 0;

	public function __construct( $args = array() ) {
		if ( isset( $args['term_id'] ) ) {
			$this->term_id = $args['term_id'];
			$this->years = ap_opt('year_filter_range');
			$this->session = count( ap_opt('session_filter_range') );
			parent::__construct( array(
				'plural' => 'year_and_session',
				'singular' => 'tag',
				'screen' => isset( $args['screen'] ) ? $args['screen'] : null,
				'ajax'  => false,
			) );
		}
  }

	public function prepare_items() {
    $columns = $this->get_columns();
    $hidden = array();
		$sortable = $this->get_sortable_columns();
		
		$this->_column_headers = array($columns, $hidden, $sortable);

		$this->set_pagination_args( array(
			'total_items' => (count($this->years) * $this->session),
		) );

	}

	public function get_columns() {
		$columns = array(
      'year'              => '년도',
      'session'           => '회차',
			'questions'					=> '질문',
			'answers'						=> '답변',
			'did_select_answer' => '답변채택',
			'vote_to_question'	=> '추천(질문)',
			'vote_to_answer'		=> '추천(답변)',
			'moderate_question'	=> '검수미완료(질문)',
			'moderate_answer'		=> '검수미완료(답변)',
			'income_of_answer'	=> '추천수익'
		);

		return $columns;
	}

	protected function get_sortable_columns() {
		$columns = array(
			'questions'					=> '질문',
			'answers'						=> '답변',
			'did_select_answer' => '답변채택',
			'vote_to_question'	=> '추천(질문)',
			'vote_to_answer'		=> '답변추천',
			'moderate_question' => '검수미완료(질문)',
			'moderate_answer'   => '검수미완료(답변)',
			'income_of_answer'  => '추천수익'
		);

		return $columns;
	}

	public function display_rows_or_placeholder() {
		foreach( $this->years as $year ) {
			for( $i = 1; $i <= $this->session; $i++ ) {
				$item = array(
					'year' => $year,
					'session' => $i
				);
				echo '<tr>';
				$this->single_row_columns( $item );
				echo '</tr>';
			}
		}
  }
  
  public function single_row_columns( $item ) {
		$info = $this->get_column_info();
		
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
      if ( method_exists( $this, 'column_' . $column_name ) ) {
				if ( $column_name == 'year' ) {
					if ( $item['session'] == 1 ) {
						$classes = '';
						if ( $primary === $column_name ) {
							$classes .= 'column-primary';
						}
						echo "<td rowspan={$this->session} class='$classes'>";
        		echo call_user_func( array( $this, 'column_' . $column_name ), $item );
        		echo "</td>";		
					}

					continue;
				}
        echo "<td>";
        echo call_user_func( array( $this, 'column_' . $column_name ), $item );
        echo "</td>";
      }
		}
	}

	public function column_year( $item ) {
		return $item['year'];
  }

	public function column_session( $item ) {
		return $item['session'];
	}

	public function column_questions( $item ) {
		global $wpdb;
		$prefix = $wpdb->prefix;

		$sql = "SELECT count(*)
						FROM {$prefix}posts
						WHERE {$prefix}posts.post_type = 'question'
						AND {$prefix}posts.post_status = 'publish'";

		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			$url = esc_url( admin_url( "edit.php?post_type=question&yas_filter=year_and_session&year={$item['year']}&session={$item['session']}" ) );
			$link = "<a href='" . $url . "' target='_blank'>" . $count . "</a>";
			return $link;
		}

		return $count;
	}

	public function column_answers( $item ) {
		global $wpdb;
		$prefix = $wpdb->prefix;

		$sql = "SELECT count(*)
						FROM {$prefix}posts
						WHERE {$prefix}posts.post_type = 'answer'
						AND {$prefix}posts.post_status = 'publish'";

		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			$url = esc_url( admin_url( "edit.php?post_type=answer&yas_filter=year_and_session&year={$item['year']}&session={$item['session']}" ) );
			$link = "<a href='" . $url . "' target='_blank'>" . $count . "</a>";
			return $link;
		}

		return $count;
	}

	public function column_did_select_answer( $item ) {
		global $wpdb;
		$prefix = $wpdb->prefix;

		$sql = "SELECT count(*)
						FROM {$prefix}posts
						LEFT JOIN {$prefix}ap_qameta qameta
						ON (qameta.post_id = {$prefix}posts.ID)
						WHERE {$prefix}posts.post_type = 'question'
						AND {$prefix}posts.post_status = 'publish'
						AND qameta.selected_id IS NOT NULL";
		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			$url = esc_url( admin_url( "edit.php?post_type=question&yas_filter=did_select_answer&year={$item['year']}&session={$item['session']}" ) );
			$link = "<a href='" . $url . "' target='_blank'>" . $count . "</a>";
			return $link;
		}

		return $count;
	}

	public function column_vote_to_question( $item ) {
		global $wpdb;
		$prefix = $wpdb->prefix;
		
		$sql = "SELECT count(*)
						FROM {$prefix}ap_qameta AS qameta
						LEFT JOIN {$prefix}posts AS posts
						ON posts.ID = qameta.post_id
						WHERE posts.post_status = 'publish'
						AND posts.post_type = 'question'
						AND (qameta.votes_up - qameta.votes_down) > 0";
		
		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			$url = esc_url( admin_url( "edit.php?post_type=question&yas_filter=vote&year={$item['year']}&session={$item['session']}" ) );
			$link = "<a href='" . $url . "' target='_blank'>" . $count . "</a>";
			return $link;
		}

		return $count;
	}

	public function column_vote_to_answer( $item ) {
		global $wpdb;
		$prefix = $wpdb->prefix;
		
		$sql = "SELECT count(*)
						FROM {$prefix}ap_qameta AS qameta
						LEFT JOIN {$prefix}posts AS posts
						ON posts.ID = qameta.post_id
						WHERE posts.post_status = 'publish'
						AND posts.post_type = 'answer'
						AND (qameta.votes_up - qameta.votes_down) > 0";
		
		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			$url = esc_url( admin_url( "edit.php?post_type=answer&yas_filter=vote&year={$item['year']}&session={$item['session']}" ) );
			$link = "<a href='" . $url . "' target='_blank'>" . $count . "</a>";
			return $link;
		}

		return $count;
	}

	// moderate means inspection check
	public function column_moderate_question( $item ) {
		global $wpdb;
		$prefix = $wpdb->prefix;

		$sql = "SELECT count(*)
						FROM {$prefix}ap_qameta AS qameta
						LEFT JOIN {$prefix}posts AS posts
						ON posts.ID = qameta.post_id
						WHERE posts.post_status = 'publish'
						AND posts.post_type = 'question'
						AND qameta.inspection_check = 0";

		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			$url = esc_url( admin_url( "edit.php?post_type=question&yas_filter=inspection_check&year={$item['year']}&session={$item['session']}" ) );
			$link = "<a href='" . $url . "' target='_blank'>" . $count . "</a>";
			return $link;
		}

		return $count;
	}

	public function column_moderate_answer( $item ) {
		global $wpdb;
		$prefix = $wpdb->prefix;

		$sql = "SELECT count(*)
						FROM {$prefix}ap_qameta AS qameta
						LEFT JOIN {$prefix}posts AS posts
						ON posts.ID = qameta.post_id
						WHERE posts.post_status = 'publish'
						AND posts.post_type = 'answer'
						AND qameta.inspection_check = 0";

		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			$url = esc_url( admin_url( "edit.php?post_type=answer&yas_filter=inspection_check&year={$item['year']}&session={$item['session']}" ) );
			$link = "<a href='" . $url . "' target='_blank'>" . $count . "</a>";
			return $link;
		}

		return $count;
	}

	public function column_income_of_answer( $item ) {
		return 20;
	}

	protected function get_default_primary_column_name() {
		return 'year';
	}

	public function column_default( $item, $column_name ) {
		/**
		 * Filters the displayed columns in the terms list table.
		 *
		 * The dynamic portion of the hook name, `$this->taxonomy`,
		 * refers to the slug of the current taxonomy.
		 *
		 * @since 2.8.0
		 *
		 * @param string $string      Blank string.
		 * @param string $column_name Name of the column.
		 * @param int    $term_id     Term ID.
		 */
		return apply_filters( "manage_{$this->taxonomy}_custom_column", '', $column_name, $item->term_id );
	}

	public function has_items() {
		// todo: populate $this->items in prepare_items()
		return true;
	}

	public function no_items() {
		echo 'no item';
	}

}