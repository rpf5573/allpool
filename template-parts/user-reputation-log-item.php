<?php
/**
 * Template for user reputation item.
 *
 * Render reputation item in authors page.
 *
 * @author  Rahul Aryan <support@anspress.io>
 * @link    https://anspress.io/
 * @since   4.0.0
 * @package AnsPress
 */

$icon_class = ap_get_reputation_icon_class( $log_entry );
?>

<tr class="ap-user-reputation-log-item ap-user-mycred-log-item">
	<td class="col-icon">
		<i class="<?php echo $icon_class; ?>"> </i>
	</td>
	<td class="col-event">
		<div class="col-event__activity"><?php echo $log_entry->entry; ?></div>
		<?php ap_reputation_ref_content( $log_entry ); ?>
	</td>
	<td class="col-date"><?php echo esc_attr( ap_human_time( $log_entry->time, false ) ); ?></td>
	<td class="col-creds"><span><?php echo $log_entry->creds; ?></span></td>
</tr>