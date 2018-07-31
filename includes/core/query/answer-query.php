<?php

class Answers_Query extends WP_Query {

	/**
	 * Answer query arguments
	 *
	 * @var array
	 */
	public $args = array();

	/**
	 * Initialize class
	 *
	 * @param array $args Query arguments.
	 * @access public
	 * @since  2.0
	 * @since  4.1.2 Fixed: pagination issue.
	 */
	public function __construct( $args = array() ) {
		global $answers;
		$paged    = (int) max( 1, get_query_var( 'ap_paged', 1 ) );
		$defaults = array(
			'question_id'            => get_question_id(),
			'ap_query'               => true,
			'ap_current_user_ignore' => false,
			'ap_answers_query'       => true,
			'showposts'              => ap_opt( 'answers_per_page' ),
			'paged'                  => $paged,
			'only_best_answer'       => false,
			'ignore_selected_answer' => false,
			'post_status'            => [ 'publish' ],
			'ap_order_by'            => ap_opt( 'answers_sort' ),
		);

		if ( get_query_var( 'answer_id' ) ) {
			$defaults['p'] = get_query_var( 'answer_id' );
		}

		$this->args                = wp_parse_args( $args, $defaults );
		$this->args['ap_order_by'] = sanitize_title( $this->args['ap_order_by'] );

		// Show trash posts to super admin.
		if ( ap_is_admin() ) {
			$this->args['post_status'][] = 'trash';
		}

		if ( isset( $this->args['question_id'] ) ) {
			$question_id = $this->args['question_id'];
		}

		if ( ! isset( $this->args['author'] ) && empty( $question_id ) && empty( $this->args['p'] ) ) {
			$this->args = [];
		} else {
			$this->args['post_parent'] = $question_id;
			$this->args['post_type']   = 'answer';
			$args                      = $this->args;

			/**
			 * Initialize parent class
			 */
			parent::__construct( $args );
		}
	}

	public function get_answers() {
		return parent::get_posts();
	}

	public function next_answer() {
		return parent::next_post();
	}

	/**
	 * Undo the pointer to next
	 */
	public function reset_next() {

		$this->current_post--;
		$this->post = $this->posts[ $this->current_post ];

		return $this->post;
	}

	public function the_answer() {
		global $post;
		$this->in_the_loop = true;

		if ( $this->current_post == -1 ) {
		  do_action_ref_array( 'ap_query_loop_start', array( &$this ) );
		}

		$post = $this->next_answer();

		setup_postdata( $post );
		anspress()->current_answer = $post;
	}

	public function have_answers() {
		return parent::have_posts();
	}

	public function rewind_answers() {
		parent::rewind_posts();
	}

	public function is_main_query() {
		return $this == anspress()->answers;
	}

	public function reset_answers_data() {
		parent::reset_postdata();

		if ( ! empty( $this->post ) ) {
			anspress()->current_answer = $this->post;
		}
	}

	/**
	 * Utility method to get all the ids in this request
	 *
	 * @return array of mdia ids
	 */
	public function get_ids() {
		if ( $this->ap_ids ) {
			return;
		}

		$this->ap_ids = [
			'post_ids'   => [],
			'attach_ids' => [],
			'user_ids'   => [],
		];

		foreach ( (array) $this->posts as $_post ) {
			$this->ap_ids['post_ids'][] = $_post->ID;
			$this->ap_ids['attach_ids'] = array_filter( array_merge( explode( ',', $_post->attach ), $this->ap_ids['attach_ids'] ) );

			if ( ! empty( $_post->post_author ) ) {
				$this->ap_ids['user_ids'][] = $_post->post_author;
			}

			// Add activities user_id to array.
			if ( ! empty( $_post->activities ) && ! empty( $_post->activities['user_id'] ) ) {
				$this->ap_ids['user_ids'][] = $_post->activities['user_id'];
			}
		}

		// Unique ids only.
		foreach ( (array) $this->ap_ids as $k => $ids ) {
			$this->ap_ids[ $k ] = array_unique( $ids );
		}
	}

	/**
	 * Pre fetch current users vote on all answers
	 *
	 * @since 3.1.0
	 * @since 4.1.2 Prefetch posts activity.
	 */
	public function pre_fetch() {
		$this->get_ids();
		ap_user_votes_pre_fetch( $this->ap_ids['post_ids'] );
		ap_post_attach_pre_fetch( $this->ap_ids['attach_ids'] );

		if ( ! empty( $this->ap_ids['user_ids'] ) ) {
			ap_post_author_pre_fetch( $this->ap_ids['user_ids'] );
		}

		do_action( 'ap_pre_fetch_answer_data', $this->ap_ids );
	}
}