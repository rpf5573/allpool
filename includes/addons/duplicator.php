<?php
class AP_Duplicator {

  public static function add_copy_answer_btn( $actions = array(), $post ) {
		if ( $post->post_type == 'answer' ) {
			if ( ap_user_can_duplicate_answer() ) {
				$link = AP_Duplicator::get_duplicate_post_link( $post , 'display' );
				if ( $link ) {
					$actions['clone'] = '<a href="' . $link . '" title="">풀이 복사</a>';
				}
			}
		}
		return $actions;
	}

	public static function post_save_as_new_post($status = '') {
		if( ! ap_user_can_duplicate_answer() ){
			wp_die( '해당 유저는 풀이를 복사할 수 없습니다' );
		}
		
		if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'ap_post_save_as_new_post' == $_REQUEST['action'] ) ) ) {
			wp_die( '알수없는 에러가 발생했습니다' );
    }
    
    \PC::debug( 'called', __FUNCTION__ );
	
		// Get the original post
		$id = (isset($_GET['post']) ? $_GET['post'] : $_POST['post']);
		
		check_admin_referer('ap-post_' . $id);
		
		$post = get_post($id);
	
		// Copy the post and insert it
		if (isset($post) && $post!=null) {
      $new_id = AP_Duplicator::post_create_duplicate($post, $status);
      wp_redirect( add_query_arg( array( 'cloned' => 1, 'ids' => $post->ID), admin_url( 'post.php?action=edit&post=' . $new_id ) ) );
			exit;
		} else {
			wp_die(esc_html__('Copy creation failed, could not find original:', 'duplicate-post') . ' ' . htmlspecialchars($id));
    }
	}

  // Register Custom Status
  public static function register_dupliacted_post_status() {
    $args = array(
      'label'                     => '복사됨',
      'label_count'               => _n_noop('복사됨 (%s)', '복사됨 (%s)'),
      'internal'                  => true,
      'show_in_admin_all_list'    => true,
      'show_in_admin_status_list' => true,
      'exclude_from_search'       => false,
    );
    register_post_status( 'duplicated', $args );
  }

  public static function get_duplicate_post_link( $post = false, $context = 'display' ) {
    if ( ! ap_user_can_duplicate_answer() ) {
      return;
    }
    if ( ! $post ) {
      return;
    }
  
    $action_name = 'ap_post_save_as_new_post';
    $action = '?action='.$action_name.'&post='.$post->ID;
    $post_type_object = get_post_type_object( $post->post_type );
    if ( ! $post_type_object ) {
      return;
    }
  
    return wp_nonce_url( admin_url( "admin.php". $action ), 'ap-post_' . $post->ID );
  }
  
  public static function post_create_duplicate($post, $status = '', $parent_id = '') {
    $new_post_status = (empty($status))? $post->post_status: $status;
    $prefix = '[풀이 복사]';
    $title = $post->post_title;
  
    if ($title == ''){
      // empty title
      $title = '제목없음';
    }
    $new_post_status = 'private';
    $new_post_author = wp_get_current_user();
    $new_post_author_id = $new_post_author->ID;
    
    $menu_order = 0;
    $post_name = $post->post_name;
  
    $new_post = array(
      'menu_order' => $menu_order,
      'comment_status' => $post->comment_status,
      'ping_status' => $post->ping_status,
      'post_author' => $new_post_author_id,
      'post_content' => $post->post_content,
      'post_content_filtered' => $post->post_content_filtered,			
      'post_excerpt' => $post->post_excerpt,
      'post_mime_type' => $post->post_mime_type,
      'post_parent' => $new_post_parent = empty($parent_id)? $post->post_parent : $parent_id,
      'post_password' => $post->post_password,
      'post_status' => $new_post_status,
      'post_title' => $title,
      'post_type' => $post->post_type,
      'post_name' => $post_name
    );
  
    $new_post_id = wp_insert_post(wp_slash($new_post));
  
    // If you have written a plugin which uses non-WP database tables to save
    // information about a post you can hook this action to dupe that data.
    if($new_post_id !== 0 && !is_wp_error($new_post_id)) {
      wp_set_post_terms( $new_post_id, '풀이복사', 'ap_tag', true );
    }
    
    return $new_post_id;
  }

  public static function append_post_status_list() {
    global $post;
    $complete = '';
    $label    = '';

   if ( in_array( $post->post_type, [ 'question', 'answer' ], true ) ) {
     if ( 'duplicated' === $post->post_status ) {
        $complete = ' selected=\'selected\'';
        $label    = "'복사됨'";
     }

     // @codingStandardsIgnoreStart
     echo '<script>
       jQuery(document).ready(function(){
         jQuery("select#post_status").append("<option value=\'duplicated\' ' . $complete . '>복사됨</option>");
         jQuery("#post-status-display").text( ' . $label . ' );
       });
     </script>';
   }
  }

  public static function exclude_duplicated_answer( $sql ) {
    // $sql['where'] .= ' AND qameta.duplication_check != 1 ';
    // \PC::debug( ['sql' => $sql], __FUNCTION__ );
    return $sql;
  }

}