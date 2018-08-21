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

class AP_Term_Statistic_List_Table extends AP_List_Table {

	private $prefix = 'wp_';

	private $level;

	private $callback_args;

	private $taxonomy;

	private $per_page = 100;

	private $terms = array();

	private $terms_with_inline_family = array();

	/**
	 * Constructor.
	 *
	 * @since 3.1.0
	 *
	 * @see WP_List_Table::__construct() for more information on term_name arguments.
	 *
	 * @global string $post_type
	 * @global string $taxonomy
	 * @global string $action
	 * @global object $tax
	 *
	 * @param array $args An associative array of arguments.
	 */
	public function __construct( $taxonomy = 'question_category' ) {
		global $post_type, $action, $tax, $wpdb;

		$this->prefix = $wpdb->prefix;
		$this->taxonomy = $taxonomy;

		parent::__construct( array(
			'plural' => 'terms',
			'singular' => 'term',
      'screen' => null,
      'ajax'  => false,
		) );

		$action    = $this->screen->action;
		$post_type = $this->screen->post_type;
		$taxonomy  = $this->taxonomy;

		if ( empty( $taxonomy ) )
			$taxonomy = 'post_tag';
		if ( ! taxonomy_exists( $taxonomy ) )
			wp_die( __( 'Invalid taxonomy.' ) );

		$tax = get_taxonomy( $taxonomy );

		// @todo Still needed? Maybe just the show_ui part.
		if ( empty( $post_type ) || !in_array( $post_type, get_post_types( array( 'show_ui' => true ) ) ) )
			$post_type = 'post';

	}

	/**
	 */
	public function prepare_items() {

		$this->terms = get_terms( array(
			'taxonomy' => $this->taxonomy,
			'hide_empty' => false,
		) );

		foreach( $this->terms as $term ) {
			$children = get_term_children( $term->term_id, $this->taxonomy );
			$children[] = $term->term_id;
			$this->terms_with_inline_family[$term->term_id] = $children;
		}
		
    $columns = $this->get_columns();
    $hidden = array();
		$sortable = $this->get_sortable_columns();
		
		$this->_column_headers = array($columns, $hidden, $sortable);
		$current_page = $this->get_pagenum();

		$this->callback_args = array(
			'page'			=> $this->get_pagenum(),
			'number'		=> $this->per_page,
		);

		$this->set_pagination_args( array(
			'total_items' => wp_count_terms( $this->taxonomy ),
			'per_page' => $this->per_page,
		) );

	}

	/**
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = AP_Statistic::$slug_label;
		unset( $columns['year'] );
		unset( $columns['session'] );

		return $columns;
	}

	/**
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		$columns = AP_Statistic::$slug_label;
		unset( $columns['term_name'] );
		unset( $columns['year'] );
		unset( $columns['session'] );

		return array();
	}

	/**
	 */
	public function display_rows_or_placeholder() {
		$taxonomy = $this->taxonomy;

		$args = wp_parse_args( $this->callback_args, array(
			'page' => 1,
			'number' => 20,
			'search' => '',
			'hide_empty' => 0
		) );

		$page = $args['page'];

		// Set variable because $args['number'] can be subsequently overridden.
		$number = $args['number'];

		$args['offset'] = $offset = ( $page - 1 ) * $number;

		// Convert it to table rows.
		$count = 0;

		if ( is_taxonomy_hierarchical( $taxonomy ) && ! isset( $args['orderby'] ) ) {
			// We'll need the full set of terms then.
			$args['number'] = $args['offset'] = 0;
		}

		if ( empty( $this->terms ) || ! is_array( $this->terms ) ) {
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
			$this->no_items();
			echo '</td></tr>';
			return;
		}

		if ( is_taxonomy_hierarchical( $taxonomy ) && ! isset( $args['orderby'] ) ) {
			$children = _get_term_hierarchy( $taxonomy );
			// Some funky recursion to get the job done( Paging & parents mainly ) is contained within, Skip it for non-hierarchical taxonomies for performance sake
			$this->_rows( $taxonomy, $this->terms, $children, $offset, $number, $count );
		} else {
			foreach ( $this->terms as $term ) {
				$this->single_row( $term );
			}
		}
	}

	/**
	 * @param string $taxonomy
	 * @param array $terms
	 * @param array $children
	 * @param int   $start
	 * @param int   $per_page
	 * @param int   $count
	 * @param int   $parent
	 * @param int   $level
	 */
	private function _rows( $taxonomy, $terms, &$children, $start, $per_page, &$count, $parent = 0, $level = 0 ) {
		$end = $start + $per_page;
		foreach ( $terms as $key => $term ) {

			if ( $count >= $end )
				break;

			if ( $term->parent != $parent && empty( $_REQUEST['s'] ) )
				continue;

			// If the page starts in a subtree, print the parents.
			if ( $count == $start && $term->parent > 0 && empty( $_REQUEST['s'] ) ) {
				$my_parents = $parent_ids = array();
				$p = $term->parent;
				while ( $p ) {
					$my_parent = get_term( $p, $taxonomy );
					$my_parents[] = $my_parent;
					$p = $my_parent->parent;
					if ( in_array( $p, $parent_ids ) ) // Prevent parent loops.
						break;
					$parent_ids[] = $p;
				}
				unset( $parent_ids );

				$num_parents = count( $my_parents );
				while ( $my_parent = array_pop( $my_parents ) ) {
					echo "\t";
					$this->single_row( $my_parent, $level - $num_parents );
					$num_parents--;
				}
			}

			if ( $count >= $start ) {
				echo "\t";
				$this->single_row( $term, $level );
			}

			++$count;

			unset( $terms[$key] );

			if ( isset( $children[$term->term_id] ) && empty( $_REQUEST['s'] ) )
				$this->_rows( $taxonomy, $terms, $children, $start, $per_page, $count, $term->term_id, $level + 1 );
		}
	}

	/**
	 * @global string $taxonomy
	 * @param WP_Term $tag Term object.
	 * @param int $level
	 */
	public function single_row( $tag, $level = 0 ) {
		$tag = sanitize_term( $tag, $this->taxonomy );

		$this->level = $level;

		echo '<tr id="tag-' . $tag->term_id . '">';
		$this->single_row_columns( $tag );
		echo '</tr>';
	}

	/**
	 * @param WP_Term $tag Term object.
	 * @return string
	 */
	public function column_cb( $tag ) {
		return '&nbsp;';
	}

	/**
	 * @param WP_Term $tag Term object.
	 * @return string
	 */
	public function column_term_name( $tag ) {
		$taxonomy = $this->taxonomy;

		$pad = str_repeat( '&#8212; ', max( 0, $this->level ) );

		$name = $pad . ' ' . $tag->name;

		$link = '#';

		$args = wp_json_encode(
			[
				'__nonce' 	=> wp_create_nonce( 'statistic_' . $tag->term_id ),
				'action' 		=> 'open_yas_table_modal',
				'term_id'		=> $tag->term_id,
				'term_name'	=> $tag->name,
				'taxonomy'  => $this->taxonomy
			],
			JSON_UNESCAPED_UNICODE
		);

		$out = '<div class="name"> <strong>';
		$out .= '<a class="yas-table-open-btn" apquery="' . esc_js( $args ) . '">';
		$out .= $name . '</a>';
		$out .= '</strong> </div>';

		return $out;
	}

	public function column_questions( $term ) {
		global $wpdb;
		$type = 'question';

		$sql = $this->get_sql( $type, $term );

		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			return $this->get_link( $type, 'term_name', $term, $count );
		}

		return $count;
	}

	public function column_answers( $term ) {
		global $wpdb;
		$type = 'answer';

		$sql = $this->get_sql( $type, $term );

		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			return $this->get_link( $type, 'term_name', $term, $count );
		}

		return $count;
	}

	public function column_did_select_answer( $term ) {
		global $wpdb;
		$type = 'question';

		$sql = $this->get_sql( $type, $term );
		$sql .= "AND qameta.selected_id IS NOT NULL";

		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			return $this->get_link( $type, 'did_select_answer', $term, $count );
		}

		return $count;
	}

	public function column_vote_to_question( $term ) {
		global $wpdb;
		$type = 'question';
		
		$sql = $this->get_sql( $type, $term );
		$sql .= "AND (qameta.votes_up - qameta.votes_down) > 0";
		
		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			return $this->get_link( $type, 'vote', $term, $count );
		}

		return $count;
	}

	public function column_vote_to_answer( $term ) {
		global $wpdb;
		$type = 'answer';

		$sql = $this->get_sql( $type, $term );
		$sql .=	"AND (qameta.votes_up - qameta.votes_down) > 0";
		
		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			return $this->get_link( $type, 'vote', $term, $count );
		}

		return $count;
	}

	public function column_moderate_question( $term ) {
		global $wpdb;
		$type = 'question';

		$sql = $this->get_sql( $type, $term );
		$sql .= "AND qameta.inspection_check = 0";

		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			return $this->get_link( $type, 'inspection_check', $term, $count );
		}

		return $count;
	}

	public function column_moderate_answer( $term ) {
		global $wpdb;
		$type = 'answer';

		$sql = $this->get_sql( $type, $term );
		$sql .= "AND qameta.inspection_check = 0";

		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			return $this->get_link( $type, 'inspection_check', $term, $count );
		}

		return $count;
	}

	public function column_income_of_answer( $tag ) {
		return 20;
	}

	/**
	 * Gets the name of the term_name primary column.
	 *
	 * @since 4.3.0
	 *
	 * @return string Name of the term_name primary column, in this case, 'name'.
	 */
	protected function get_term_name_primary_column_name() {
		return 'term_name';
	}

	/**
	 * @param WP_Term $tag Term object.
	 * @param string $column_name
	 * @return string
	 */
	public function column_default( $tag, $column_name ) {
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
		return apply_filters( "manage_{$this->taxonomy}_custom_column", '', $column_name, $tag->term_id );
	}

	/**
	 *
	 * @return bool
	 */
	public function has_items() {
		// todo: populate $this->items in prepare_items()
		return true;
	}

	/**
	 */
	public function no_items() {
		echo get_taxonomy( $this->taxonomy )->labels->not_found;
	}

	public function get_sql( $type, $term ) {
		$prefix = $this->prefix;
		$terms = implode( ',', $this->terms_with_inline_family[$term->term_id] );
		$sql = "SELECT count(*)
						FROM {$prefix}posts as posts
						LEFT JOIN {$prefix}term_relationships as term_relationships";
		if ( $type == 'question' ) {
			$sql .= " ON (posts.ID = term_relationships.object_id)";
		} else {
			$sql .= " ON (posts.post_parent = term_relationships.object_id)";
		}
		$sql .= " LEFT JOIN {$prefix}ap_qameta as qameta
						ON posts.ID = qameta.post_id
						WHERE ( term_relationships.term_taxonomy_id IN ({$terms}) )
						AND posts.post_type = '$type'
						AND posts.post_status IN ('publish', 'private') ";

		return $sql;
	}

	public function get_link( $type, $filter, $term, $count ) {
		$url = esc_url( admin_url( "edit.php?post_type={$type}&term_filter={$filter}&term_id={$term->term_id}&term_name={$term->name}&taxonomy={$this->taxonomy}" ) );
		$link = "<a href='" . $url . "'>" . $count . "</a>";
		return $link;
	}

}