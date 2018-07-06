<?php
/**
 * Display question list header
 * Shows sorting, search, tags, category filter form. Also shows a ask button.
 *
 * @package AnsPress
 * @author  Rahul Aryan <support@anspress.io>
 */
?>

<div class="ap-list-head clearfix">
	<?php ap_template_part( 'question', 'filter' ); ?>
</div>