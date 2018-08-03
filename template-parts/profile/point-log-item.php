<?php
/**
 * Template for user point item.
 *
 * Render point item in authors page.
 *
 * @author  Rahul Aryan <support@anspress.io>
 * @link    https://anspress.io/
 * @since   4.0.0
 * @package AnsPress
 */

$icon_class = ap_get_point_icon_class( $log_entry );
?>

<tr class="ap-user-point-log-item ap-user-mycred-log-item">
	<td class="col-icon">
		<i class="<?php echo $icon_class; ?>"> </i>
	</td>
	<td class="col-event">
		<div class="col-event__activity"><?php echo $log_entry->entry; ?></div>
		<div class="col-event__ref">
			<?php ap_point_ref_content( $log_entry ); ?>
		</div>
	</td>
	<td class="col-date"><?php echo esc_attr( ap_human_time( $log_entry->time, false ) ); ?></td>
	<td class="col-creds"><span class="ap-user-point-creds mini-creds"><?php echo $log_entry->creds; ?></span></td>
</tr>