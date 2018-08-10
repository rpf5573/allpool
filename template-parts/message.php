<?php
$class = 'info';
if ( isset( $template_args['type'] ) ) {
  if ( $template_args['type'] == 'error' || $template_args['type'] == 'fatal' ) {
    $class = 'negative';
  }
}
?>
<div class="ui message <?php echo $class; ?>">
  <i class="close icon"></i>
  <div class="header"> <?php 
    if ( isset( $template_args['header'] ) ) {
      echo $template_args['header'];
    } ?>
  </div>
  <p> <?php
    if ( isset( $template_args['body'] ) ) {
      echo $template_args['body'];
    } ?>
  </p>
</div>

<?php
if ( isset( $template_args['type'] ) ) {
  if ( $template_args['type'] == 'fatal' ) {
    wp_die();
  }
}
?>