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
	private $level;

	private $callback_args;

	private $taxonomy = 'question_category';

	private $per_page = 100;

	private $terms = array();

	private $terms_with_inline_family = array();

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
			'plural' => 'terms',
			'singular' => 'term',
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
		$columns = array(
			'name'        			=> '이름',
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
			'vote_to_question'	=> '추천(질문)',
			'vote_to_answer'		=> '답변추천',
			'moderate_question' => '검수미완료(질문)',
			'moderate_answer'   => '검수미완료(답변)',
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
	public function column_name( $tag ) {
		$taxonomy = $this->taxonomy;

		$pad = str_repeat( '&#8212; ', max( 0, $this->level ) );

		$name = $pad . ' ' . $tag->name;

		$link = '#';

		$args = wp_json_encode(
			[
				'__nonce' 	=> wp_create_nonce( 'statistic_' . $tag->term_id ),
				'action' => 'open_yas_table_modal',
				'term_id'		=> $tag->term_id,
				'term_name'	=> $tag->name
			]
		);
		$out = '<div class="name"> <strong>';
		$out .= '<a class="yas-table-open-btn" apquery="' . esc_js( $args ) . '">';
		$out .= $name . '</a>';
		$out .= '</strong> </div>';

		return $out;
	}

	public function column_questions( $tag ) {
		global $wpdb;
		$prefix = $wpdb->prefix;

		$terms = implode( ',', $this->terms_with_inline_family[$tag->term_id] );
		$sql = "SELECT count(*)
						FROM {$prefix}posts
						LEFT JOIN {$prefix}term_relationships
						ON ({$prefix}posts.ID = {$prefix}term_relationships.object_id)
						WHERE ( {$prefix}term_relationships.term_taxonomy_id IN ({$terms}) )
						AND {$prefix}posts.post_type = 'question'
						AND {$prefix}posts.post_status = 'publish'";

		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			$url = esc_url( admin_url( "edit.php?post_type=question&term_id={$tag->term_id}&term_name={$tag->name}&term_filter=category" ) );
			$link = "<a href='" . $url . "' target='_blank'>" . $count . "</a>";
			return $link;
		}

		return $count;
	}

	public function column_answers( $tag ) {
		global $wpdb;
		$prefix = $wpdb->prefix;

		$terms = implode( ',', $this->terms_with_inline_family[$tag->term_id] );
		$sql = "SELECT count(*)
							FROM {$prefix}posts
							LEFT JOIN {$prefix}term_relationships
							ON ({$prefix}posts.post_parent = {$prefix}term_relationships.object_id)
							WHERE ( {$prefix}term_relationships.term_taxonomy_id IN ($terms) )
							AND {$prefix}posts.post_type = 'answer'
							AND {$prefix}posts.post_status = 'publish'";

		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			$url = esc_url( admin_url( "edit.php?post_type=answer&term_id={$tag->term_id}&term_name={$tag->name}&term_filter=category" ) );
			$link = "<a href='" . $url . "' target='_blank'>" . $count . "</a>";
			return $link;
		}

		return $count;
	}

	public function column_did_select_answer( $tag ) {
		global $wpdb;
		$prefix = $wpdb->prefix;

		$terms = implode( ',', $this->terms_with_inline_family[$tag->term_id] );
		$sql = "SELECT count(*)
						FROM {$prefix}posts
						LEFT JOIN {$prefix}term_relationships
						ON ({$prefix}posts.ID = {$prefix}term_relationships.object_id)
						LEFT JOIN {$prefix}ap_qameta qameta
						ON (qameta.post_id = {$prefix}posts.ID)
						WHERE ( {$prefix}term_relationships.term_taxonomy_id IN ({$terms}) )
						AND {$prefix}posts.post_type = 'question'
						AND {$prefix}posts.post_status = 'publish'
						AND qameta.selected_id IS NOT NULL";
		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			$url = esc_url( admin_url( "edit.php?post_type=question&term_id={$tag->term_id}&term_name={$tag->name}&term_filter=did_select_answer" ) );
			$link = "<a href='" . $url . "' target='_blank'>" . $count . "</a>";
			return $link;
		}

		return $count;
	}

	public function column_vote_to_question( $tag ) {
		global $wpdb;
		$prefix = $wpdb->prefix;

		$terms = implode( ',', $this->terms_with_inline_family[$tag->term_id] );
		
		$sql = "SELECT count(*)
						FROM {$prefix}ap_qameta AS qameta
						LEFT JOIN {$prefix}posts AS posts
						ON posts.ID = qameta.post_id
						LEFT JOIN {$prefix}term_relationships AS term_relationships
						ON (posts.ID = term_relationships.object_id)
						WHERE posts.post_status = 'publish'
						AND posts.post_type = 'question'
						AND term_relationships.term_taxonomy_id IN ($terms)
						AND (qameta.votes_up - qameta.votes_down) > 0";
		
		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			$url = esc_url( admin_url( "edit.php?post_type=question&term_id={$tag->term_id}&term_name={$tag->name}&term_filter=vote" ) );
			$link = "<a href='" . $url . "' target='_blank'>" . $count . "</a>";
			return $link;
		}

		return $count;
	}

	public function column_vote_to_answer( $tag ) {
		global $wpdb;
		$prefix = $wpdb->prefix;

		$terms = implode( ',', $this->terms_with_inline_family[$tag->term_id] );
		
		$sql = "SELECT count(*)
						FROM {$prefix}ap_qameta AS qameta
						LEFT JOIN {$prefix}posts AS posts
						ON posts.ID = qameta.post_id
						LEFT JOIN {$prefix}term_relationships AS term_relationships
						ON (posts.post_parent = term_relationships.object_id)
						WHERE posts.post_status = 'publish'
						AND posts.post_type = 'answer'
						AND term_relationships.term_taxonomy_id IN ($terms)
						AND (qameta.votes_up - qameta.votes_down) > 0";
		
		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			$url = esc_url( admin_url( "edit.php?post_type=answer&term_id={$tag->term_id}&term_name={$tag->name}&term_filter=vote" ) );
			$link = "<a href='" . $url . "' target='_blank'>" . $count . "</a>";
			return $link;
		}

		return $count;
	}

	public function column_moderate_question( $tag ) {
		global $wpdb;
		$prefix = $wpdb->prefix;

		$terms = implode( ',', $this->terms_with_inline_family[$tag->term_id] );
		$sql = "SELECT count(*)
						FROM {$prefix}ap_qameta AS qameta
						LEFT JOIN {$prefix}posts AS posts
						ON posts.ID = qameta.post_id
						LEFT JOIN {$prefix}term_relationships AS term_relationships
						ON (posts.ID = term_relationships.object_id)
						WHERE posts.post_status = 'publish'
						AND posts.post_type = 'question'
						AND term_relationships.term_taxonomy_id IN ($terms)
						AND qameta.inspection_check = 0";

		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			$url = esc_url( admin_url( "edit.php?post_type=question&term_id={$tag->term_id}&term_name={$tag->name}&term_filter=inspection_check" ) );
			$link = "<a href='" . $url . "' target='_blank'>" . $count . "</a>";
			return $link;
		}

		return $count;
	}

	public function column_moderate_answer( $tag ) {
		global $wpdb;
		$prefix = $wpdb->prefix;

		$terms = implode( ',', $this->terms_with_inline_family[$tag->term_id] );
		$sql = "SELECT count(*)
						FROM {$prefix}ap_qameta AS qameta
						LEFT JOIN {$prefix}posts AS posts
						ON posts.ID = qameta.post_id
						LEFT JOIN {$prefix}term_relationships AS term_relationships
						ON (posts.post_parent = term_relationships.object_id)
						WHERE posts.post_status = 'publish'
						AND posts.post_type = 'answer'
						AND term_relationships.term_taxonomy_id IN ($terms)
						AND qameta.inspection_check = 0";

		$count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			$url = esc_url( admin_url( "edit.php?post_type=answer&term_id={$tag->term_id}&term_name={$tag->name}&term_filter=inspection_check" ) );
			$link = "<a href='" . $url . "' target='_blank'>" . $count . "</a>";
			return $link;
		}

		return $count;
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