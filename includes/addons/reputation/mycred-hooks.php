<?php
namespace Anspress\Reputation;

class Register extends \myCRED_Hook {
  private $hook_id = 'register';
  private $log = '회원가입';
  
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
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['creds'], $this->log, 0, '' );
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
            name="<?php echo $this->field_name('log'); ?>"
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
    'new'      => '질문 생성',
    'trash'    => '질문 휴지통으로 이동',
    'delete'   => '질문 영구 삭제',
    'untrash'  => '질문 복구',
  );
  
	function __construct( $hook_prefs, $type ) {
		parent::__construct( array(
			'id'       => $this->hook_id,
			'defaults' => array(
        'new'     => array( 'creds'   => 10, 'log' => $this->log['new'] ),
        'trash'   => array( 'creds'   => -10, 'log' => $this->log['trash'] ),
        'delete'  => array( 'creds'   => 0, 'log' => $this->log['delete'] ),
        'untrash'  => array( 'creds'   => -10, 'log' => $this->log['untrash'] ),
			)
		), $hook_prefs, $type );
  }
  
  public function run() {
    add_action( 'ap_after_new_question', array( $this, 'new_question' ), 11, 2 );
    add_action( 'ap_untrash_question', array( $this, 'untrash_question' ), 11, 2 );
    
    add_action( 'ap_trash_question', array( $this, 'trash_question' ), 11, 2 );
    add_action( 'ap_before_delete_question', array( $this, 'delete_question' ), 11, 2 );
  }
  
  public function new_question( $post_id, $_post ) {
    $user_id = $_post->post_author;
    if ( false === $user_id ) {
      $user_id = get_current_user_id();
    }

    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['new']['creds'], $this->log['new'], $post_id, array( 'action' => 'new', 'ptype' => 'question', 'post_title' => $_post->post_title, 'post_content' => esc_html( ap_truncate_chars( strip_tags( $_post->post_content ), 200 ) ) ) );
  }

  public function untrash_question( $post_id, $_post ) {
    $user_id = $_post->post_author;
    if ( false === $user_id ) {
      $user_id = get_current_user_id();
    }

    // sync with new
    $this->prefs['untrash']['creds'] = $this->prefs['new']['creds'];

    $by = ( is_admin() && ! wp_doing_ajax() ) ? ' - 관리자 권한' : '';

    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['untrash']['creds'], $this->log['untrash'] . $by, $post_id, array( 'action' => 'untrash', 'ptype' => 'question', 'post_title' => $_post->post_title, 'post_content' => esc_html( ap_truncate_chars( strip_tags( $_post->post_content ), 200 ) ) ) );
  }

  public function trash_question( $post_id, $_post ) {
    $user_id = $_post->post_author;
    if ( false === $user_id ) {
      $user_id = get_current_user_id();
    }

    // sync with delete
    $this->prefs['trash']['creds'] = -($this->prefs['new']['creds']);

    $by = ( is_admin() && ! wp_doing_ajax() ) ? ' - 관리자 권한' : '';

    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['trash']['creds'], $this->log['trash'] . $by, $post_id, array( 'action' => 'trash', 'ptype' => 'question', 'post_title' => $_post->post_title, 'post_content' => esc_html( ap_truncate_chars( strip_tags( $_post->post_content ), 200 ) ), 'test_test' => "Aaaaaaaaaskdjakjsdhajksdhjakshdjkahsahsaaaasasaasasasaasasasasasasasasasas" ) );
  }

  public function delete_question( $post_id, $_post ) {
    $user_id = $_post->post_author;
    if ( false === $user_id ) {
      $user_id = get_current_user_id();
    }

    // 관리자가 영구삭제한거는 로그에 남기지 않는다. 어차피 휴지통에 들어가면, 유저 입장에서는 삭제로 보니까!
    if ( is_admin() && ! wp_doing_ajax() ) return;
    if ( $this->core->exclude_user( $user_id ) ) return;

    $this->prefs['delete']['creds'] = -($this->prefs['new']['creds']);

    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['delete']['creds'], $this->log['delete'], $post_id, array( 'action' => 'delete', 'ptype' => 'question', 'post_title' => $_post->post_title, 'post_content' => esc_html( ap_truncate_chars( strip_tags( $_post->post_content ), 200 ) ) ) );
  }

  public function preferences() {
    $prefs = $this->prefs; ?>
    <h3> <?php echo "질문 생성"; ?> </h3>
    <ol>
      <li>
        <label for="<?php echo $this->field_id( array('new', 'creds') )?>" class="subheader"><?php echo $this->core->plural(); ?></label>
        <input 
          type="text" 
          name="<?php echo $this->field_name( array( 'new', 'creds' ) ); ?>" 
          id="<?php echo $this->field_id( array( 'new', 'creds' ) ); ?>"
          value="<?php echo esc_attr( $prefs['new']['creds'] ); ?>"
          size="8">
      </li>
    </ol>
    <?php
  }

}

class Answer extends \myCRED_Hook {
  private $hook_id = 'answer';
  private $log = array(
    'new'      => '답변 생성',
    'trash'    => '답변 휴지통으로 이동',
    'delete'   => '답변 영구 삭제',
    'untrash'  => '답변 복구',
  );
  
	function __construct( $hook_prefs, $type ) {
		parent::__construct( array(
			'id'       => $this->hook_id,
			'defaults' => array(
        'new'     => array( 'creds'   => 10, 'log' => $this->log['new'] ),
        'trash'   => array( 'creds'   => -10, 'log' => $this->log['trash'] ),
        'delete'  => array( 'creds'   => -10, 'log' => $this->log['delete'] ),
        'untrash'  => array( 'creds'   => -10, 'log' => $this->log['untrash'] ),
			)
		), $hook_prefs, $type );
  }
  
  public function run() {
    add_action( 'ap_after_new_answer', array( $this, 'new_answer' ), 11, 2 );
    add_action( 'ap_untrash_answer', array( $this, 'untrash_answer' ), 11, 2 );
    
    add_action( 'ap_trash_answer', array( $this, 'trash_answer' ), 11, 2 );
    add_action( 'ap_before_delete_answer', array( $this, 'delete_answer' ), 11, 2 );
  }
  
  public function new_answer( $post_id, $_post ) {
    $user_id = $_post->post_author;
    if ( false === $user_id ) {
      $user_id = get_current_user_id();
    }

    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['new']['creds'], $this->log['new'], $post_id, array( 'action' => 'new', 'ptype' => 'answer', 'post_title' => $_post->post_title, 'post_content' => esc_html( ap_truncate_chars( strip_tags( $_post->post_content ), 200 ) ) ) );
  }

  public function untrash_answer( $post_id, $_post ) {
    $user_id = $_post->post_author;
    if ( false === $user_id ) {
      $user_id = get_current_user_id();
    }

    // sync with new
    $this->prefs['untrash']['creds'] = $this->prefs['new']['creds'];

    $by = ( is_admin() && ! wp_doing_ajax() ) ? ' - 관리자 권한' : '';

    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['untrash']['creds'], $this->log['untrash'] . $by, $post_id, array( 'action' => 'untrash', 'ptype' => 'answer', 'post_title' => $_post->post_title, 'post_content' => esc_html( ap_truncate_chars( strip_tags( $_post->post_content ), 200 ) ) ) );
  }

  public function trash_answer( $post_id, $_post ) {
    $user_id = $_post->post_author;
    if ( false === $user_id ) {
      $user_id = get_current_user_id();
    }

    // sync with delete
    $this->prefs['trash']['creds'] = -($this->prefs['new']['creds']);

    $by = ( is_admin() && ! wp_doing_ajax() ) ? ' - 관리자 권한' : '';

    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['trash']['creds'], $this->log['trash'] . $by, $post_id, array( 'action' => 'trash', 'ptype' => 'answer', 'post_title' => $_post->post_title, 'post_content' => esc_html( ap_truncate_chars( strip_tags( $_post->post_content ), 200 ) ) ) );
  }

  public function delete_answer( $post_id, $_post ) {
    $user_id = $_post->post_author;
    if ( false === $user_id ) {
      $user_id = get_current_user_id();
    }

    $this->prefs['delete']['creds'] = -($this->prefs['new']['creds']);

    if ( is_admin() && ! wp_doing_ajax() ) return; // 관리자가 영구삭제한거는 로그에 남기지 않는다. 어차피 휴지통에 들어가면, 유저 입장에서는 삭제로 보니까!
    if ( $this->core->exclude_user( $user_id ) ) return;

    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['delete']['creds'], $this->log['delete'], $post_id, array( 'action' => 'delete', 'ptype' => 'answer', 'post_title' => $_post->post_title, 'post_content' => esc_html( ap_truncate_chars( strip_tags( $_post->post_content ), 200 ) ) ) );
  }

  public function preferences() {
    $prefs = $this->prefs; ?>
    <h3> <?php echo "답변 생성"; ?> </h3>
    <ol>
      <li>
        <label for="<?php echo $this->field_id( array('new', 'creds') )?>" class="subheader"><?php echo $this->core->plural(); ?></label>
        <input 
          type="text" 
          name="<?php echo $this->field_name( array( 'new', 'creds' ) ); ?>" 
          id="<?php echo $this->field_id( array( 'new', 'creds' ) ); ?>"
          value="<?php echo esc_attr( $prefs['new']['creds'] ); ?>"
          size="8">
      </li>
    </ol>
    <?php
  }

}

class Select_Answer extends \myCRED_Hook {
  private $hook_id = 'select_answer';
  private $log = array(
    'do'    => '답변 채택',
    'undo'  => '답변 채택 취소',
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
    add_action( 'ap_select_answer', array( $this, 'select_answer' ), 20, 2 );
    add_action( 'ap_unselect_answer', array( $this, 'unselect_answer' ), 10, 2 ); // called only by admin
  }

  public function select_answer( $_post, $question_id ) {
    
    $question = get_post( $question_id );

    $by = '';
    if ( is_admin() && ! wp_doing_ajax() ) {
      $by = ' - 관리자 권한';

      // 만약에 다른 답변을 베스트 답변으로 바꾸려고 하는거려면, 이걸 유저 로그에 남길 필요는 없다고 생각한다. 그래서 anspress-admin.php의 save_best_answer_selection()를 보면, 
      // 일단 질문을 업데이트 하는 과정에서 단순이 질문의 내용을 바꾸는 경우에는 이미 채택된 답변을 또 채택한답시고 ap_set_selected_answer()가 호출되지는 않아. 다만, 베스트답변을 다른 답변으로 바꿨을때 에는
      // 이 select_answer하고, unselect_answer가 호출되도 될것같아 괜찮아 괜찮아... 유저는 당황하겠지만, 뭐,, 내 코드가 짧아지는걸! 그리고 이런 일이 거의 일어나지 않을거니까!
    }

    $user_id = $question->post_author;
    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['do']['creds'], $this->log['do'] . $by, $question->ID, array( 'action' => 'do', 'ptype' => 'question', 'post_title' => $question->post_title, 'post_content' => esc_html( ap_truncate_chars( strip_tags( $question->post_content ), 200 ) ) ) );
  }

  public function unselect_answer( $_post, $question_id ) {
    $question = get_post( $question_id );

    $this->prefs['undo']['creds'] = -($this->prefs['do']['creds']);

    $by = '';
    if ( is_admin() && ! wp_doing_ajax() ) {
      $by = ' - 관리자 권한';
    }

    $user_id = $question->post_author;
    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['undo']['creds'], $this->log['undo'] . $by, $question->ID, array( 'action' => 'undo', 'ptype' => 'question', 'post_title' => $question->post_title, 'post_content' => esc_html( ap_truncate_chars( strip_tags( $question->post_content ), 200 ) ) ) );
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
    </ol>
    <?php
  }

}

class Best_Answer extends \myCRED_Hook {
  private $hook_id = 'best_answer';
  private $log = array(
    'do'    => '베스트 답변으로 채택됨',
    'undo'  => '베스트 답변 취소',
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
    add_action( 'ap_unselect_answer', array( $this, 'unselected_best_answer' ), 10, 2 );
  }

  public function selected_best_answer( $_post, $question_id ) {

    $by = '';
    if ( is_admin() && ! wp_doing_ajax() ) {
      $by = ' - 관리자 권한';
    }

    $user_id = $_post->post_author;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['do']['creds'], $this->log['do'], $_post->ID, array( 'action' => 'do', 'ptype' => 'answer', 'post_title' => $_post->post_title, 'post_content' => esc_html( ap_truncate_chars( strip_tags( $_post->post_content ), 200 ) ) ) );
  }

  public function unselected_best_answer( $_post, $question_id ) {
    $this->prefs['undo']['creds'] = -($this->prefs['do']['creds']);

    $by = '';
    if ( is_admin() && ! wp_doing_ajax() ) {
      $by = ' - 관리자 권한';
    }

    $user_id = $_post->post_author;  
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['undo']['creds'], $this->log['undo'] . $by, $_post->ID, array( 'action' => 'undo', 'ptype' => 'answer', 'post_title' => $_post->post_title, 'post_content' => esc_html( ap_truncate_chars( strip_tags( $_post->post_content ), 200 ) ) ) );
  }

  public function preferences() {
    $prefs = $this->prefs; ?>
    <h3> <?php echo $this->log['do']; ?> </h3>
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
    </ol>
    <?php
  }

}

class Vote_Up extends \myCRED_Hook {
  private $hook_id = 'vote_up';
  private $log = array(
    'do'    => '추천을 받음',
    'undo'  => '추천 취소',
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
    // add_action( 'ap_undo_vote_up', array( $this, 'undo_vote_up' ), 10, 2 );
  }
  
  public function vote_up( $post_id ) {
    $_post = get_post( $post_id );
    $user_id = $_post->post_author;
    $post_type = get_post_type( $_post );

    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['do']['creds'], $this->log['do'], $post_id, array( 'action' => 'do', 'ptype' => $post_type, 'post_title' => $_post->post_title, 'post_content' => esc_html( ap_truncate_chars( strip_tags( $_post->post_content ), 200 ) ) ) );
  }

  public function undo_vote_up( $post_id ) {
    $_post = get_post( $post_id );
    $user_id = $_post->post_author;
    $post_type = get_post_type( $_post );

    $this->prefs['undo']['creds'] = -($this->prefs['do']['creds']);

    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['undo']['creds'], $this->log['undo'], $post_id, array( 'action' => 'undo', 'ptype' => $post_type, 'post_title' => $_post->post_title, 'post_content' => esc_html( ap_truncate_chars( strip_tags( $_post->post_content ), 200 ) ) ) );
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
          name="<?php echo $this->field_name( array( 'do', 'log' ) ); ?>"
          id="<?php echo $this->field_id( array( 'do', 'log' ) ); ?>" 
          value="<?php echo esc_attr( $prefs['do']['log'] ); ?>"
          class="long">
      </li>
    </ol>
    <?php
  }

}

class Vote_Down extends \myCRED_Hook {
  private $hook_id = 'vote_down';
  private $log = array(
    'do'    => '비추천을 받음',
    'undo'  => '비추천 취소',
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
    // add_action( 'ap_undo_vote_down', array( $this, 'undo_vote_down' ), 10, 2 );
  }
  
  public function vote_down( $post_id ) {
    $_post = get_post( $post_id );
    $user_id = $_post->post_author;
    $post_type = get_post_type( $_post );

    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['do']['creds'], $this->log['do'], $post_id, array( 'action' => 'do', 'ptype' => $post_type, 'post_title' => $_post->post_title, 'post_content' => esc_html( ap_truncate_chars( strip_tags( $_post->post_content ), 200 ) ) ) );
  }

  public function undo_vote_down( $post_id ) {
    $_post = get_post( $post_id );
    $user_id = $_post->post_author;
    $post_type = get_post_type( $_post );

    $this->prefs['undo']['creds'] = -($this->prefs['do']['creds']);

    if ( $this->core->exclude_user( $user_id ) ) return;
    $this->core->add_creds( $this->hook_id, $user_id, $this->prefs['undo']['creds'], $this->log['undo'], $post_id, array( 'action' => 'undo', 'ptype' => $post_type, 'post_title' => $_post->post_title, 'post_content' => esc_html( ap_truncate_chars( strip_tags( $_post->post_content ), 200 ) ) ) );
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
          name="<?php echo $this->field_name( array( 'do', 'log' ) ); ?>"
          id="<?php echo $this->field_id( array( 'do', 'log' ) ); ?>" 
          value="<?php echo esc_attr( $prefs['do']['log'] ); ?>"
          class="long">
      </li>
    </ol>
    <?php
  }

}

?>