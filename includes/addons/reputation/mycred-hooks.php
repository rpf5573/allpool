<?php
namespace Anspress\Reputation;

class Register extends \myCRED_Hook {
  private $hook_id = 'register';
  private $log = 'Register';
  
	function __construct( $hook_prefs, $type ) {
		parent::__construct( array(
			'id'       => $this->hook_id,
			'defaults' => array(
				'creds'   => 10,
				'log'     => $this->log
			)
		), $hook_prefs, $type );
  }
  
  public function run() {
    add_action( 'user_register', array( $this, 'user_register' ) );
  }
  
  public function user_register( $user_id ) {
    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['creds'], $this->prefs['log'], 0, '' );
  }

  public function preferences() {
    $prefs = $this->prefs; ?>
    <label class="subheader"> <?php echo $this->core->plural(); ?> </label>
    <ol>
      <li>
        <div class="h2">
          <input 
            type="text" 
            name="<?php echo $this->field_name( 'creds' ); ?>" 
            id="<?php echo $this->field_id( 'creds' ); ?>"
            value="<?php echo esc_attr( $prefs['creds'] ); ?>"
            size="8">
        </div>
      </li>
    </ol>

    <label class="subheader"><?php _e('Log template', 'anspress-question-answer'); ?></label>
    <ol>
      <li>
        <div class="h2">
          <input
            type="text"
            name="<?php $this->field_name('log'); ?>"
            id="<?php echo $this->field_id('log'); ?>" 
            value="<?php echo esc_attr($prefs['log']); ?>"
            class="long">
        </div>
      </li>
    </ol> <?php
  }

  public function sanitise_preferences( $data ) {
		$new_data = $data;

		// Apply defaults if any field is left empty
		$new_data['creds'] = ( !empty( $data['creds'] ) ) ? $data['creds'] : $this->defaults['creds'];
		$new_data['log'] = ( !empty( $data['log'] ) ) ? sanitize_text_field( $data['log'] ) : $this->defaults['log'];

		return $new_data;
	}
}

class Ask extends \myCRED_Hook {
  private $hook_id = 'ask';
  private $log = array(
    'do'    => 'Asked a question',
    'undo'  => 'Delete a question',
  );
  
	function __construct( $hook_prefs, $type ) {
		parent::__construct( array(
			'id'       => $this->hook_id,
			'defaults' => array(
        'do'     => array( 'creds'   => 10, 'log' => $this->log['do'] ), 
        'undo'   => array( 'creds'   => -10, 'log' => $this->log['undo'] ), 
			)
		), $hook_prefs, $type );
  }
  
  public function run() {
    add_action( 'ap_after_new_question', array( $this, 'new_question' ), 11, 2 );
    add_action( 'ap_untrash_question', array( $this, 'new_question' ), 11, 2 );
    
    add_action( 'ap_trash_question', array( $this, 'trash_question' ), 11, 2 );
    add_action( 'ap_before_delete_question', array( $this, 'trash_question' ), 11, 2 );
  }
  
  public function new_question( $post_id, $_post ) {
    $user_id = $_post->post_author;
    if ( false === $user_id ) {
      $user_id = get_current_user_id();
    }

    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['do']['creds'], $this->prefs['do']['log'], $post_id, array( 'type' => 'do', 'parent' => 'question' ) );
  }

  public function trash_question( $post_id, $_post ) {
    $user_id = $_post->post_author;
    if ( false === $user_id ) {
      $user_id = get_current_user_id();
    }

    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['undo']['creds'], $this->prefs['undo']['log'], $post_id, array( 'type' => 'undo', 'parent' => 'question' ) );
  }

  public function preferences() {
    $prefs = $this->prefs; ?>
    <h3> <?php _e( 'Ask a Question', 'anspress-question-answer' ); ?> </h3>
    <ol>
      <li>
        <label for="<?php echo $this->field_id( array('do', 'creds') )?>" class="subheader"><?php echo $this->core->plural(); ?></label>
        <input 
          type="text" 
          name="<?php echo $this->field_name( array( 'do', 'creds' ) ); ?>" 
          id="<?php echo $this->field_id( array( 'do', 'creds' ) ); ?>"
          value="<?php echo esc_attr( $prefs['do']['creds'] ); ?>"
          size="8">
      </li>
      <li>
        <label class="subheader"><?php _e('Log template', 'anspress-question-answer'); ?></label>
        <input
          type="text"
          name="<?php $this->field_name( array( 'do', 'log' ) ); ?>"
          id="<?php echo $this->field_id( array( 'do', 'log' ) ); ?>" 
          value="<?php echo esc_attr( $prefs['do']['log'] ); ?>"
          class="long">
      </li>
    </ol>

    <h3> <?php _e( 'Delete a Question', 'anspress-question-answer' ); ?> </h3>
    <ol>
      <li>
        <label for="<?php echo $this->field_id( array('undo', 'creds') )?>" class="subheader"><?php echo $this->core->plural(); ?></label>
        <input 
          type="text" 
          name="<?php echo $this->field_name( array( 'undo', 'creds' ) ); ?>" 
          id="<?php echo $this->field_id( array( 'undo', 'creds' ) ); ?>"
          value="<?php echo esc_attr( $prefs['undo']['creds'] ); ?>"
          size="8">
      </li>
      <li>
        <label class="subheader"><?php _e('Log template', 'anspress-question-answer'); ?></label>
        <input
          type="text"
          name="<?php $this->field_name( array( 'undo', 'log' ) ); ?>"
          id="<?php echo $this->field_id( array( 'undo', 'log' ) ); ?>" 
          value="<?php echo esc_attr( $prefs['undo']['log'] ); ?>"
          class="long">
      </li>
    </ol>

    <?php
  }

}

class Answer extends \myCRED_Hook {
  private $hook_id = 'answer';
  private $log = array(
    'do'    => 'Posted an answer',
    'undo'  => 'Deleted an answer',
  );
  
	function __construct( $hook_prefs, $type ) {
		parent::__construct( array(
			'id'       => $this->hook_id,
			'defaults' => array(
        'do'     => array( 'creds'   => 10, 'log' => $this->log['do'] ),
        'undo'   => array( 'creds'   => -10, 'log' => $this->log['undo'] ),
			)
		), $hook_prefs, $type );
  }
  
  public function run() {
    add_action( 'ap_after_new_answer', array( $this, 'new_answer' ), 10, 2 );
    add_action( 'ap_untrash_answer', array( $this, 'new_answer' ), 10, 2 );
    
    add_action( 'ap_trash_answer', array( $this, 'trash_answer' ), 10, 2 );
    add_action( 'ap_before_delete_answer', array( $this, 'trash_answer' ), 10, 2 );
  }
  
  public function new_answer( $post_id, $_post ) {
    $user_id = $_post->post_author;
    if ( false === $user_id ) {
      $user_id = get_current_user_id();
    }

    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['do']['creds'], $this->prefs['do']['log'], $post_id, array( 'type' => 'do', 'parent' => 'answer' ) );
  }

  public function trash_answer( $post_id, $_post ) {
    $user_id = $_post->post_author;
    if ( false === $user_id ) {
      $user_id = get_current_user_id();
    }

    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['undo']['creds'], $this->prefs['undo']['log'], $post_id, array( 'type' => 'undo', 'parent' => 'answer' ) );
  }


  public function preferences() {
    $prefs = $this->prefs; ?>
    <h3> <?php _e( 'Give an answer', 'anspress-question-answer' ); ?> </h3>
    <ol>
      <li>
        <label for="<?php echo $this->field_id( array('do', 'creds') )?>" class="subheader"><?php echo $this->core->plural(); ?></label>
        <input 
          type="text" 
          name="<?php echo $this->field_name( array( 'do', 'creds' ) ); ?>" 
          id="<?php echo $this->field_id( array( 'do', 'creds' ) ); ?>"
          value="<?php echo esc_attr( $prefs['do']['creds'] ); ?>"
          size="8">
      </li>
      <li>
        <label class="subheader"><?php _e('Log template', 'anspress-question-answer'); ?></label>
        <input
          type="text"
          name="<?php $this->field_name( array( 'do', 'log' ) ); ?>"
          id="<?php echo $this->field_id( array( 'do', 'log' ) ); ?>" 
          value="<?php echo esc_attr( $prefs['do']['log'] ); ?>"
          class="long">
      </li>
    </ol>

    <h3> <?php _e( 'Delete an answer', 'anspress-question-answer' ); ?> </h3>
    <ol>
      <li>
        <label for="<?php echo $this->field_id( array('undo', 'creds') )?>" class="subheader"><?php echo $this->core->plural(); ?></label>
        <input 
          type="text" 
          name="<?php echo $this->field_name( array( 'undo', 'creds' ) ); ?>" 
          id="<?php echo $this->field_id( array( 'undo', 'creds' ) ); ?>"
          value="<?php echo esc_attr( $prefs['undo']['creds'] ); ?>"
          size="8">
      </li>
      <li>
        <label class="subheader"><?php _e('Log template', 'anspress-question-answer'); ?></label>
        <input
          type="text"
          name="<?php $this->field_name( array( 'undo', 'log' ) ); ?>"
          id="<?php echo $this->field_id( array( 'undo', 'log' ) ); ?>" 
          value="<?php echo esc_attr( $prefs['undo']['log'] ); ?>"
          class="long">
      </li>
    </ol>

    <?php
  }

}

class Select_Answer extends \myCRED_Hook {
  private $hook_id = 'select_answer';
  private $log = array(
    'do'    => 'Selected an answer as best',
    'undo'  => 'Unselected an answer as best',
  );
  
	function __construct( $hook_prefs, $type ) {
		parent::__construct( array(
			'id'       => $this->hook_id,
			'defaults' => array(
        'do'     => array( 'creds'   => 10, 'log' => $this->log['do'] ),
        'undo'   => array( 'creds'   => -10, 'log' => $this->log['undo'] ),
			)
		), $hook_prefs, $type );
  }
  
  public function run() {
    add_action( 'ap_select_answer', array( $this, 'select_answer' ), 10, 2 );
    add_action( 'ap_unselect_answer', array( $this, 'unselect_answer' ), 10, 2 );
  }

  public function select_answer( $_post ) {
    $question = get_post( $_post->post_parent );
    // Award select answer points to question author only.
    if ( get_current_user_id() == $question->post_author ) {
      $user_id = $question->post_author;
      if ( $this->core->exclude_user( $user_id ) ) return;
      $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['do']['creds'], $this->prefs['do']['log'], $question->ID, array( 'type' => 'do', 'parent' => 'question' ) );
    }
  }

  public function unselect_answer( $_post ) {
    // remove reputaion from selector(author of the question)
    $question = get_post( $_post->post_parent );
    // Award select answer points to question author only.
    if ( get_current_user_id() == $question->post_author ) {
      $user_id = $question->post_author;
      if ( $this->core->exclude_user( $user_id ) ) return;
      $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['undo']['creds'], $this->prefs['undo']['log'], $question->ID, array( 'type' => 'undo', 'parent' => 'question' ) );
    }
  }

  public function preferences() {
    $prefs = $this->prefs; ?>
    <h3> <?php _e( 'Select best answer', 'anspress-question-answer' ); ?> </h3>
    <ol>
      <li>
        <label for="<?php echo $this->field_id( array('do', 'creds') )?>" class="subheader"><?php echo $this->core->plural(); ?></label>
        <input 
          type="text" 
          name="<?php echo $this->field_name( array( 'do', 'creds' ) ); ?>" 
          id="<?php echo $this->field_id( array( 'do', 'creds' ) ); ?>"
          value="<?php echo esc_attr( $prefs['do']['creds'] ); ?>"
          size="8">
      </li>
      <li>
        <label class="subheader"><?php _e('Log template', 'anspress-question-answer'); ?></label>
        <input
          type="text"
          name="<?php $this->field_name( array( 'do', 'log' ) ); ?>"
          id="<?php echo $this->field_id( array( 'do', 'log' ) ); ?>" 
          value="<?php echo esc_attr( $prefs['do']['log'] ); ?>"
          class="long">
      </li>
    </ol>

    <h3> <?php _e( 'Unselect best answer', 'anspress-question-answer' ); ?> </h3>
    <ol>
      <li>
        <label for="<?php echo $this->field_id( array('undo', 'creds') )?>" class="subheader"><?php echo $this->core->plural(); ?></label>
        <input 
          type="text" 
          name="<?php echo $this->field_name( array( 'undo', 'creds' ) ); ?>" 
          id="<?php echo $this->field_id( array( 'undo', 'creds' ) ); ?>"
          value="<?php echo esc_attr( $prefs['undo']['creds'] ); ?>"
          size="8">
      </li>
      <li>
        <label class="subheader"><?php _e('Log template', 'anspress-question-answer'); ?></label>
        <input
          type="text"
          name="<?php $this->field_name( array( 'undo', 'log' ) ); ?>"
          id="<?php echo $this->field_id( array( 'undo', 'log' ) ); ?>" 
          value="<?php echo esc_attr( $prefs['undo']['log'] ); ?>"
          class="long">
      </li>
    </ol>

    <?php
  }

}

class Best_Answer extends \myCRED_Hook {
  private $hook_id = 'best_answer';
  private $log = array(
    'do'    => 'Answer selected as best',
    'undo'  => 'Answer unselected as best',
  );
  
	function __construct( $hook_prefs, $type ) {
		parent::__construct( array(
			'id'       => $this->hook_id,
			'defaults' => array(
        'do'     => array( 'creds'   => 10, 'log' => $this->log['do'] ),
        'undo'   => array( 'creds'   => -10, 'log' => $this->log['undo'] ),
			)
		), $hook_prefs, $type );
  }
  
  public function run() {
    add_action( 'ap_select_answer', array( $this, 'selected_best_answer' ), 10, 2 );
    add_action( 'ap_unselect_answer', array( $this, 'cancelled_best_answer' ), 10, 2 );
  }

  public function selected_best_answer( $_post ) {
    $user_id = $_post->post_author;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['do']['creds'], $this->prefs['do']['log'], $_post->ID, array( 'type' => 'do', 'parent' => 'answer' ) );
  }

  public function cancelled_best_answer( $_post ) {
    $user_id = $_post->post_author;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['undo']['creds'], $this->prefs['undo']['log'], $_post->ID, array( 'type' => 'undo', 'parent' => 'answer' ) );
  }

  public function preferences() {
    $prefs = $this->prefs; ?>
    <h3> <?php _e( 'Selected Best Answer', 'anspress-question-answer' ); ?> </h3>
    <ol>
      <li>
        <label for="<?php echo $this->field_id( array('do', 'creds') )?>" class="subheader"><?php echo $this->core->plural(); ?></label>
        <input 
          type="text" 
          name="<?php echo $this->field_name( array( 'do', 'creds' ) ); ?>" 
          id="<?php echo $this->field_id( array( 'do', 'creds' ) ); ?>"
          value="<?php echo esc_attr( $prefs['do']['creds'] ); ?>"
          size="8">
      </li>
      <li>
        <label class="subheader"><?php _e('Log template', 'anspress-question-answer'); ?></label>
        <input
          type="text"
          name="<?php $this->field_name( array( 'do', 'log' ) ); ?>"
          id="<?php echo $this->field_id( array( 'do', 'log' ) ); ?>" 
          value="<?php echo esc_attr( $prefs['do']['log'] ); ?>"
          class="long">
      </li>
    </ol>

    <h3> <?php _e( 'Cancelled Best Answer', 'anspress-question-answer' ); ?> </h3>
    <ol>
      <li>
        <label for="<?php echo $this->field_id( array('undo', 'creds') )?>" class="subheader"><?php echo $this->core->plural(); ?></label>
        <input 
          type="text" 
          name="<?php echo $this->field_name( array( 'undo', 'creds' ) ); ?>" 
          id="<?php echo $this->field_id( array( 'undo', 'creds' ) ); ?>"
          value="<?php echo esc_attr( $prefs['undo']['creds'] ); ?>"
          size="8">
      </li>
      <li>
        <label class="subheader"><?php _e('Log template', 'anspress-question-answer'); ?></label>
        <input
          type="text"
          name="<?php $this->field_name( array( 'undo', 'log' ) ); ?>"
          id="<?php echo $this->field_id( array( 'undo', 'log' ) ); ?>" 
          value="<?php echo esc_attr( $prefs['undo']['log'] ); ?>"
          class="long">
      </li>
    </ol>

    <?php
  }

}

class Vote_Up extends \myCRED_Hook {
  private $hook_id = 'vote_up';
  private $log = array(
    'do'    => 'Received up vote',
    'undo'  => 'Unreceived up vote',
  );
  
	function __construct( $hook_prefs, $type ) {
		parent::__construct( array(
			'id'       => $this->hook_id,
			'defaults' => array(
        'do'     => array( 'creds'   => 10, 'log' => $this->log['do'] ),
        'undo'   => array( 'creds'   => -10, 'log' => $this->log['undo'] ),
			)
		), $hook_prefs, $type );
  }
  
  public function run() {
    add_action( 'ap_vote_up', array( $this, 'vote_up' ), 10, 2 );
    add_action( 'ap_undo_vote_up', array( $this, 'undo_vote_up' ), 10, 2 );
  }
  
  public function vote_up( $post_id ) {
    $_post = get_post( $post_id );
    $user_id = $_post->post_author;
    $post_type = get_post_type( $_post );

    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['do']['creds'], $this->prefs['do']['log'], $post_id, array( 'type' => 'do', 'parent' => $post_type ) );
  }

  public function undo_vote_up( $post_id ) {
    $_post = get_post( $post_id );
    $user_id = $_post->post_author;
    $post_type = get_post_type( $_post );

    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['undo']['creds'], $this->prefs['undo']['log'], $post_id, array( 'type' => 'undo', 'parent' => $post_type ) );
  }

  public function preferences() {
    $prefs = $this->prefs; ?>
    <h3> <?php _e( 'Vote up', 'anspress-question-answer' ); ?> </h3>
    <ol>
      <li>
        <label for="<?php echo $this->field_id( array('do', 'creds') )?>" class="subheader"><?php echo $this->core->plural(); ?></label>
        <input 
          type="text" 
          name="<?php echo $this->field_name( array( 'do', 'creds' ) ); ?>" 
          id="<?php echo $this->field_id( array( 'do', 'creds' ) ); ?>"
          value="<?php echo esc_attr( $prefs['do']['creds'] ); ?>"
          size="8">
      </li>
      <li>
        <label class="subheader"><?php _e('Log template', 'anspress-question-answer'); ?></label>
        <input
          type="text"
          name="<?php $this->field_name( array( 'do', 'log' ) ); ?>"
          id="<?php echo $this->field_id( array( 'do', 'log' ) ); ?>" 
          value="<?php echo esc_attr( $prefs['do']['log'] ); ?>"
          class="long">
      </li>
    </ol>

    <h3> <?php _e( 'Undo vote up', 'anspress-question-answer' ); ?> </h3>
    <ol>
      <li>
        <label for="<?php echo $this->field_id( array('undo', 'creds') )?>" class="subheader"><?php echo $this->core->plural(); ?></label>
        <input 
          type="text" 
          name="<?php echo $this->field_name( array( 'undo', 'creds' ) ); ?>" 
          id="<?php echo $this->field_id( array( 'undo', 'creds' ) ); ?>"
          value="<?php echo esc_attr( $prefs['undo']['creds'] ); ?>"
          size="8">
      </li>
      <li>
        <label class="subheader"><?php _e('Log template', 'anspress-question-answer'); ?></label>
        <input
          type="text"
          name="<?php $this->field_name( array( 'undo', 'log' ) ); ?>"
          id="<?php echo $this->field_id( array( 'undo', 'log' ) ); ?>" 
          value="<?php echo esc_attr( $prefs['undo']['log'] ); ?>"
          class="long">
      </li>
    </ol>

    <?php
  }

}

class Vote_Down extends \myCRED_Hook {
  private $hook_id = 'vote_down';
  private $log = array(
    'do'    => 'Received down vote',
    'undo'  => 'Unreceived down vote',
  );
  
	function __construct( $hook_prefs, $type ) {
		parent::__construct( array(
			'id'       => $this->hook_id,
			'defaults' => array(
        'do'     => array( 'creds'   => -10, 'log' => $this->log['do'] ), 
        'undo'   => array( 'creds'   => 10, 'log' => $this->log['undo'] ), 
			)
		), $hook_prefs, $type );
  }
  
  public function run() {
    add_action( 'ap_vote_down', array( $this, 'vote_down' ), 10, 2 );
    add_action( 'ap_undo_vote_down', array( $this, 'undo_vote_down' ), 10, 2 );
  }
  
  public function vote_down( $post_id ) {
    $_post = get_post( $post_id );
    $user_id = $_post->post_author;
    $post_type = get_post_type( $_post );

    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['do']['creds'], $this->prefs['do']['log'], $post_id, array( 'type' => 'do', 'parent' => $post_type ) );
  }

  public function undo_vote_down( $post_id ) {
    $_post = get_post( $post_id );
    $user_id = $_post->post_author;
    $post_type = get_post_type( $_post );

    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['undo']['creds'], $this->prefs['undo']['log'], $post_id, array( 'type' => 'undo', 'parent' => $post_type ) );
  }

  public function preferences() {
    $prefs = $this->prefs; ?>
    <h3> <?php _e( 'Vote down', 'anspress-question-answer' ); ?> </h3>
    <ol>
      <li>
        <label for="<?php echo $this->field_id( array('do', 'creds') )?>" class="subheader"><?php echo $this->core->plural(); ?></label>
        <input
          type="text" 
          name="<?php echo $this->field_name( array( 'do', 'creds' ) ); ?>" 
          id="<?php echo $this->field_id( array( 'do', 'creds' ) ); ?>"
          value="<?php echo esc_attr( $prefs['do']['creds'] ); ?>"
          size="8">
      </li>
      <li>
        <label class="subheader"><?php _e('Log template', 'anspress-question-answer'); ?></label>
        <input
          type="text"
          name="<?php $this->field_name( array( 'do', 'log' ) ); ?>"
          id="<?php echo $this->field_id( array( 'do', 'log' ) ); ?>" 
          value="<?php echo esc_attr( $prefs['do']['log'] ); ?>"
          class="long">
      </li>
    </ol>

    <h3> <?php _e( 'Undo vote down', 'anspress-question-answer' ); ?> </h3>
    <ol>
      <li>
        <label for="<?php echo $this->field_id( array('undo', 'creds') )?>" class="subheader"><?php echo $this->core->plural(); ?></label>
        <input 
          type="text" 
          name="<?php echo $this->field_name( array( 'undo', 'creds' ) ); ?>" 
          id="<?php echo $this->field_id( array( 'undo', 'creds' ) ); ?>"
          value="<?php echo esc_attr( $prefs['undo']['creds'] ); ?>"
          size="8">
      </li>
      <li>
        <label class="subheader"><?php _e('Log template', 'anspress-question-answer'); ?></label>
        <input
          type="text"
          name="<?php $this->field_name( array( 'undo', 'log' ) ); ?>"
          id="<?php echo $this->field_id( array( 'undo', 'log' ) ); ?>" 
          value="<?php echo esc_attr( $prefs['undo']['log'] ); ?>"
          class="long">
      </li>
    </ol>

    <?php
  }

}

?>