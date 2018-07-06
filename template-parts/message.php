<?php
$class = 'info';
if ( $template_args['type'] == 'error' || $template_args['type'] == 'fatal' ) {
  $class = 'nagative';
}
?>
<div class="ui message <?php echo $class; ?>">
  <i class="close icon"></i>
  <div class="header"> <?php 
    echo $template_args['header']; ?>
  </div>
  <p> <?php
    echo $template_args['body']; ?>
  </p>
</div>

<?php 
if ( $template_args['type'] == 'fatal' ) {
  wp_die();
}
?>