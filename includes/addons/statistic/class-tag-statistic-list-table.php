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

if(!class_exists('AP_List_Table')) {
  require_once( 'class-ap-list-table.php' );
}

// YaS => Year and Session
class AP_Tag_Statistic_List_Table extends AP_List_Table {

  private $prefix = 'wp_';
  
  private $tags = array();

  private $taxonomy = 'ap_tag';

	private $callback_args;

	public function __construct( $args = array() ) {
		global $wpdb;
		$this->prefix = $wpdb->prefix;

		parent::__construct( array(
      'plural' => 'tags',
      'singular' => 'tag',
      'screen' => isset( $args['screen'] ) ? $args['screen'] : null,
      'ajax'  => false,
    ) );
  }

	public function prepare_items() {
    $columns = $this->get_columns();
    $hidden = array();
		$sortable = $this->get_sortable_columns();
		
    $this->_column_headers = array($columns, $hidden, $sortable);

    $this->tags = get_terms( array(
      'taxonomy' => $this->taxonomy,
      'hide_empty' => false
    ) );
	}

	public function get_columns() {
		$columns = array(
      'tag_name'  => '태그',
      'questions' => '질문',
      'answers'   => '답변'
    );
		return $columns;
	}

	protected function get_sortable_columns() {
		return array();
	}

	public function display_rows_or_placeholder() {
    
    if ( is_array( $this->tags ) && ! empty( $this->tags ) ) {
      foreach( $this->tags as $tag ) {
        $this->single_row_columns( $tag );
      }
    }
  }
  
  public function single_row_columns( $tag ) {
		$info = $this->get_column_info();
		
    list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();
    
    

    echo "<tr>";
		foreach ( $columns as $column_name => $column_display_name ) {
      if ( method_exists( $this, 'column_' . $column_name ) ) {
        echo "<td>";
        echo call_user_func( array( $this, 'column_' . $column_name ), $tag );
        echo "</td>";
      }
    }
    echo "</tr>";
  }
  
  public function column_tag_name( $tag ) {
		global $wpdb;

		return $tag->name;
	}

	public function column_questions( $tag ) {
		global $wpdb;

    $sql = $this->get_question_sql( $tag );
    $count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			return $this->get_link( 'question', 'tag_name', $tag, $count );
		}

		return $count;
	}

	public function column_answers( $tag ) {
		global $wpdb;
    
		$sql = $this->get_answer_sql( $tag );
		
    $count = $wpdb->get_var( $sql );
		if ( $count > 0 ) {
			return $this->get_link( 'answer', 'tag_name', $tag, $count );
		}

		return $count;
	}

	protected function get_term_name_primary_column_name() {
		return 'tag_name';
	}

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

	public function has_items() {
		// todo: populate $this->items in prepare_items()
		return true;
	}

	public function no_items() {
		echo 'no item';
	}

	public function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', 'width-auto', $this->_args['plural'] );
	}

	public function get_question_sql( $tag ) {
		$prefix = $this->prefix;
		$sql = " SELECT count(*)
						FROM {$prefix}posts as posts
						LEFT JOIN {$prefix}term_relationships as term_relationships
						ON (posts.ID = term_relationships.object_id)
						WHERE ( term_relationships.term_taxonomy_id = {$tag->term_id} )
						AND posts.post_type = 'question'
						AND posts.post_status IN ('publish', 'private') ";

		return $sql;
	}	

	public function get_answer_sql( $tag ) {
		$prefix = $this->prefix;
		$sql = "SELECT count(*)
						FROM {$prefix}posts as posts
						LEFT JOIN {$prefix}term_relationships as term_relationships
						ON (posts.ID = term_relationships.object_id)
						WHERE ( term_relationships.term_taxonomy_id = {$tag->term_id} )
						AND posts.post_type = 'answer'
						AND posts.post_status IN('publish','private')" ;

		return $sql;
	}

	public function get_link( $type, $filter, $tag, $count ) {
		$url = esc_url( admin_url( "edit.php?post_type={$type}&tag_filter={$filter}&tag_id={$tag->term_id}&tag_name={$tag->name}" ) );
		$link = "<a href='" . $url . "'>" . $count . "</a>";
		return $link;
	}

}