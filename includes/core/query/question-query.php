<?php

class Question_Query extends WP_Query {
	/**
	 * Store post type.
	 *
	 * @var string
	 */
	private $post_type;

	public $count_request;

	/**
	 * Initialize class.
	 *
	 * @param array|string $args Query args.
	 * @since unknown
	 * @since 4.1.5 Include future questions if user have privilege.
	 */
	public function __construct( $args = [] ) {
		 
		$paged = get_query_var( 'ap_paged' );
		 
		if ( is_front_page() ) {
			$paged = ( isset( $_GET['ap_paged'] ) ) ? (int) $_GET['ap_paged'] : 1; // input var ok.
		} else if ( ! $paged ) {
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		}

		if ( isset( $args['post_parent'] ) ) {
			$post_parent = $args['post_parent'];
		} else {
			$post_parent = ( get_query_var( 'parent' ) ) ? get_query_var( 'parent' ) : false;
		}

		$defaults = array(
			'showposts'              => (int) ap_opt( 'question_per_page' ),
			'paged'                  => $paged,
			'ap_query'               => true,
			'ap_order_by'            => 'active',
			'ap_question_query'      => true,
			'post_status'            => [ 'publish' ],
			'ap_current_user_ignore' => false,
		);

		$this->args                = wp_parse_args( $args, $defaults );
		$this->args['ap_order_by'] = sanitize_title( $this->args['ap_order_by'] );

		// Show trash posts in admin page
		if ( ap_is_admin() && is_admin() ) {
			$this->args['post_status'][] = 'trash';
		}

		$this->args['post_status'] = array_unique( $this->args['post_status'] );

		if ( $post_parent ) {
			$this->args['post_parent'] = $post_parent;
		}

		if ( '' !== get_query_var( 'ap_s' ) ) {
			$this->args['s'] = ap_sanitize_unslash( 'ap_s', 'query_var' );
		}

		$this->args['post_type'] = 'question';

		parent::__construct( $this->args );
	}

	/**
	 * Get posts.
	 */
	public function get_questions() {
		return parent::get_posts();
	}

	/**
	 * Update loop index to next post.
	 */
	public function next_question() {
		return parent::next_post();
	}

	/**
	 * Undo the pointer to next.
	 */
	public function reset_next() {
		$this->current_post--;
		$this->post = $this->posts[ $this->current_post ];
		return $this->post;
	}

	/**
	 * Set current question in loop.
	 */
	public function the_question() {
		global $post;
		$this->in_the_loop = true;

		if ( -1 === $this->current_post ) {
			do_action_ref_array( 'ap_query_loop_start', array( &$this ) );
		}

		$post = $this->next_question(); // override ok.

		setup_postdata( $post );
		anspress()->current_question = $post;
	}

	/**
	 * Check if loop have questions.
	 *
	 * @return boolean
	 */
	public function have_questions() {
		return parent::have_posts();
	}

	/**
	 * Rewind questions in loop.
	 */
	public function rewind_questions() {
		parent::rewind_posts();
	}

	/**
	 * Check if main question query.
	 *
	 * @return boolean
	 */
	public function is_main_query() {
		return anspress()->questions === $this;
	}

	/**
	 * Reset current question in loop.
	 */
	public function reset_questions_data() {
		parent::reset_postdata();

		if ( ! empty( $this->post ) ) {
			anspress()->current_question = $this->post;
		}
	}

	/**
	 * Utility method to get all the ids in this request
	 *
	 * @return array of question ids
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
			$this->ap_ids['attach_ids'] = array_merge( explode( ',', $_post->attach ), $this->ap_ids['attach_ids'] );
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
	 * @since 4.1.2 Prefetch post activities.
	 */
	public function pre_fetch() {
		$this->get_ids();
		ap_user_votes_pre_fetch( $this->ap_ids['post_ids'] );
		ap_post_attach_pre_fetch( $this->ap_ids['attach_ids'] );

		// Pre fetch users.
		if ( ! empty( $this->ap_ids['user_ids'] ) ) {
			ap_post_author_pre_fetch( $this->ap_ids['user_ids'] );
		}

		do_action( 'ap_pre_fetch_question_data', $this->ap_ids );
	}
}