<div class="ui mini modal ap-user-info-edit-modal --password">
  <div class="header">
    비밀번호 변경
  </div>
  <div class="content"> <?php 
    $args = array(
      'show_links' => false
    );
    $form = tml_get_form( 'lostpassword' );
    $field = $form->get_field( 'redirect_to' );
    $field->set_value( ap_user_link( $template_args['user_data']->ID ) . '/?confirm_email=true' );
    echo $form->render( $args ); ?>
  </div>
</div>