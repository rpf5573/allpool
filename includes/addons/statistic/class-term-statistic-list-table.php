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

if(!class_exists('WP_List_Table')){
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class AP_Term_Statistic_List_Table extends WP_List_Table {
	private $level;

	private $callback_args;

	private $taxonomy = 'question_category';

	private $per_page = 100;

	/**
	 * Constructor.
	 *
	 * @since 3.1.0
	 *
	 * @see WP_List_Table::__construct() for more information on default arguments.
	 *
	 * @global string $post_type
	 * @global string $taxonomy
	 * @global string $action
	 * @global object $tax
	 *
	 * @param array $args An associative array of arguments.
	 */
	public function __construct( $args = array() ) {
		global $post_type, $action, $tax;

		parent::__construct( array(
			'plural' => 'tags',
			'singular' => 'tag',
      'screen' => isset( $args['screen'] ) ? $args['screen'] : null,
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
		$columns = array(
			'name'        			=> '이름',
			'questions'					=> '질문',
			'answers'						=> '답변',
			'did_select_answer' => '답변채택',
			'vote_to_answer'		=> '답변추천',
			'moderating'				=> '검수미완료',
			'income_of_answer'	=> '추천수익'
		);

		return $columns;
	}

	/**
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		$columns = array(
			'name'        			=> '이름',
			'questions'					=> '질문',
			'answers'						=> '답변',
			'did_select_answer' => '답변채택',
			'vote_to_answer'		=> '답변추천',
			'moderating'				=> '검수미완료',
			'income_of_answer'  => '추천수익'
		);

		return $columns;
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
		$terms = get_terms( $taxonomy, $args );

		if ( empty( $terms ) || ! is_array( $terms ) ) {
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . $this->get_column_count() . '">';
			$this->no_items();
			echo '</td></tr>';
			return;
		}

		if ( is_taxonomy_hierarchical( $taxonomy ) && ! isset( $args['orderby'] ) ) {

			$children = _get_term_hierarchy( $taxonomy );
			// Some funky recursion to get the job done( Paging & parents mainly ) is contained within, Skip it for non-hierarchical taxonomies for performance sake
			$this->_rows( $taxonomy, $terms, $children, $offset, $number, $count );
		} else {
			foreach ( $terms as $term ) {
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
	public function column_name( $tag ) {
		$taxonomy = $this->taxonomy;

		$pad = str_repeat( '&#8212; ', max( 0, $this->level ) );

		/**
		 * Filters display of the term name in the terms list table.
		 *
		 * The default output may include padding due to the term's
		 * current level in the term hierarchy.
		 *
		 * @since 2.5.0
		 *
		 * @see WP_Terms_List_Table::column_name()
		 *
		 * @param string $pad_tag_name The term name, padded if not top-level.
		 * @param WP_Term $tag         Term object.
		 */
		$name = $pad . ' ' . $tag->name;

		$link = '#';

		$out .= '<div class="name"> <strong>';
		$out .= '<a href="' . $link . '">';
		$out .= $name . '</a>';
		$out .= '</strong> </div>';

		return $out;
	}

	public function column_questions( $tag ) {
		global $wpdb;
		$query = "SELECT *
							FROM wp_posts
							LEFT JOIN wp_term_relationships
							ON (wp_posts.ID = wp_term_relationships.object_id)
							WHERE 1=1
							AND ( wp_term_relationships.term_taxonomy_id IN ({$tag->term_id}) )
							AND wp_posts.post_type = 'question'
							AND wp_posts.post_status = 'publish'
							GROUP BY wp_posts.ID DESC 
							LIMIT 0, 6000";

		$results = $wpdb->get_results( $query );

		return count( $results );
	}

	public function column_answers( $tag ) {
		$query = "SELECT count(*)
							FROM wp_posts
							LEFT JOIN wp_term_relationships
							ON (wp_posts.ID = wp_term_relationships.object_id)
							WHERE ( wp_term_relationships.term_taxonomy_id IN ({$tag->term_id}) )
							AND wp_posts.post_type = 'answer'
							AND wp_posts.post_status = 'publish'
							GROUP BY wp_posts.ID";

		return 10;
	}

	public function column_did_select_answer( $tag ) {
		return 20;
	}

	public function column_vote_to_answer( $tag ) {
		return 20;
	}

	public function column_moderating( $tag ) {
		return 20;
	}

	public function column_income_of_answer( $tag ) {
		return 20;
	}

	/**
	 * Gets the name of the default primary column.
	 *
	 * @since 4.3.0
	 *
	 * @return string Name of the default primary column, in this case, 'name'.
	 */
	protected function get_default_primary_column_name() {
		return 'name';
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

}