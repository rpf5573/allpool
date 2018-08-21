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

	private $prefix = 'wp_';

	private $term_id = '';

	private $term_name = '';

	private $term_family = '';

	private $callback_args;

  private $years = array();
  
  private $session = 0;

	public function __construct( $args = array() ) {
		global $wpdb;
		$this->prefix = $wpdb->prefix;

		if ( isset( $args['term_id'] ) ) {
			$this->term_id = $args['term_id'];
			$this->term_name = $args['term_name'];
			$this->term_family = ap_get_term_family( $this->term_id, $args['taxonomy'] );
			$this->years = ap_opt('year_filter_range');
			$this->session = count( ap_opt('session_filter_range') );

			parent::__construct( array(
				'plural' => 'yas',
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
		$columns = AP_Statistic::$slug_label;
		unset( $columns['term_name'] );
		return $columns;
	}

	protected function get_sortable_columns() {
		$columns = AP_Statistic::$slug_label;
		unset( $columns['term_name'] );
		unset( $columns['year'] );
		unset( $columns['session'] );

		return array();
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

		$sql = $this->get_question_sql( $item['year'], $item['session'] );

		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			return $this->get_link( 'question', 'term_name', $item['year'], $item['session'], $count );
		}

		return $count;
	}

	public function column_answers( $item ) {
		global $wpdb;
		$count = 0;
		
		$ids = ap_get_question_ids( $item['year'], $item['session'], $this->term_family );
		if ( ! empty($ids) ) {
			$sql = $this->get_answer_sql( $ids );

			$count = $wpdb->get_var( $sql );
			if ( $count > 0 ) {
				return $this->get_link( 'answer', 'term_name', $item['year'], $item['session'], $count );
			}
		}

		return $count;
	}

	public function column_did_select_answer( $item ) {
		global $wpdb;
		
		$sql = $this->get_question_sql( $item['year'], $item['session'] );
		$sql .= 'AND qameta.selected_id IS NOT NULL';

		$count = $wpdb->get_var( $sql );

		if ( $count > 0 ) {
			return $this->get_link( 'question', 'did_select_answer', $item['year'], $item['session'], $count );
		}

		return $count;
	}

	public function column_vote_to_question( $item ) {
		global $wpdb;
		
		$sql = $this->get_question_sql( $item['year'], $item['session'] );
		$sql .=	"AND (qameta.votes_up - qameta.votes_down) > 0";
		
		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			return $this->get_link( 'question', 'vote', $item['year'], $item['session'], $count );
		}

		return $count;
	}

	public function column_vote_to_answer( $item ) {
		global $wpdb;
		$count = 0;

		$ids = ap_get_question_ids( $item['year'], $item['session'], $this->term_family );
		if ( ! empty( $ids ) ) {
			$sql = $this->get_answer_sql( $ids );
			$sql .=	"AND (qameta.votes_up - qameta.votes_down) > 0";
			
			$count = $wpdb->get_var( $sql );
			if ( $count > 0 ) {
				return $this->get_link( 'answer', 'vote', $item['year'], $item['session'], $count );
			}
		}

		return $count;
	}

	// moderate means inspection check
	public function column_moderate_question( $item ) {
		global $wpdb;

		$sql = $this->get_question_sql( $item['year'], $item['session'] );
		$sql .=	"AND qameta.inspection_check = 0";

		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			return $this->get_link( 'question', 'inspection_check', $item['year'], $item['session'], $count );
		}

		return $count;
	}

	public function column_moderate_answer( $item ) {
		global $wpdb;
		$count = 0;

		$ids = ap_get_question_ids( $item['year'], $item['session'], $this->term_family );
		if ( ! empty( $ids ) ) {
			$sql = $this->get_answer_sql( $ids );
			$sql .=	"AND qameta.inspection_check = 0";

			$count = $wpdb->get_var( $sql );
			if ( $count > 0 ) {
				return $this->get_link( 'answer', 'inspection_check', $item['year'], $item['session'], $count );
			}
		}

		return $count;
	}

	public function column_income_of_answer( $item ) {
		return 20;
	}

	protected function get_term_name_primary_column_name() {
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

	public function get_question_sql( $year, $session ) {
		$prefix = $this->prefix;
		$sql = "SELECT count(*)
						FROM {$prefix}posts as posts
						LEFT JOIN {$prefix}term_relationships as term_relationships
						ON (posts.ID = term_relationships.object_id)
						LEFT JOIN {$prefix}ap_qameta as qameta
						ON posts.ID = qameta.post_id
						WHERE ( term_relationships.term_taxonomy_id IN ({$this->term_family}) )
						AND posts.post_type = 'question'
						AND posts.post_status = 'publish'
						AND qameta.year = {$year} 
						AND qameta.session = {$session} ";

		return $sql;
	}	

	public function get_answer_sql( $q_ids ) {
		$prefix = $this->prefix;

		$ids = implode( ',', $q_ids );
		$sql = "SELECT count(*)
						FROM {$prefix}posts as posts
						LEFT JOIN {$prefix}term_relationships as term_relationships
						ON (posts.post_parent = term_relationships.object_id)
						LEFT JOIN {$prefix}ap_qameta as qameta
						ON posts.ID = qameta.post_id
						WHERE ( term_relationships.term_taxonomy_id IN ({$this->term_family}) )
						AND posts.post_type = 'answer'
						AND posts.post_status IN ('publish', 'private')
						AND posts.post_parent IN ($ids) ";

		return $sql;
	}

	public function get_link( $type, $filter, $year, $session, $count ) {
		$url = esc_url( admin_url( "edit.php?post_type={$type}&yas_filter={$filter}&ap_year={$year}&ap_session={$session}&term_id={$this->term_id}&term_name={$this->term_name}" ) );
		$link = "<a href='" . $url . "'>" . $count . "</a>";
		return $link;
	}

}