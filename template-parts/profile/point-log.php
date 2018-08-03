<?php
/**
 * Template for user reputations item.
 *
 * Render reputation item in authors page.
 *
 * @author  Rahul Aryan <support@anspress.io>
 * @link    https://anspress.io/
 * @since   4.0.0
 * @package AnsPress
 */
?>
<div class="ap-user-point-log ap-user-mycred-log"> <?php
	$paged    = (int) max( 1, get_query_var( 'ap_paged', 1 ) );
	$_REQUEST['page'] = $paged;
	$args = array(
		'user_id' => $template_args['user_id'],
		'ctype'   => 'mycred_point',
		'number'	=> 10
	);

	$log = new myCRED_Query_Log( $args ); ?>
	<table>
		<tbody class="log-table"> <?php
			if ( $log->have_entries() ) {
				foreach ( $log->results as $log_entry ) {
					$log_entry->data = maybe_unserialize( $log_entry->data );
					include ap_template_part_location( 'profile/point', 'log-item' );
				}
			}
			// No log entry
			else { ?>
				<p> <?php _e( '포인트기록이 없습니다', 'anspress-question-answer' ); ?> </p> <?php
			} ?>

		</tbody>
	</table> <?php
	ap_pagination( $paged, $log->max_num_pages, '?paged=%#%', false );
	
	$log->reset_query(); ?>

</div>