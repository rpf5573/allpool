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

$icon_class = ap_get_point_icon_class( $log_entry );
?>

<tr class="ap-point-item">
	<td class="col-icon">
		<i class="<?php echo $icon_class; ?>"> </i>
	</td>
	<td class="col-event ap-point-event">
		<div class="ap-point-activity"><?php echo $log_entry->entry; ?></div>
		<?php ap_point_ref_content( $log_entry ); ?>
	</td>
	<td class="col-date ap-point-date"><?php echo esc_attr( ap_human_time( $log_entry->time, false ) ); ?></td>
	<td class="col-points ap-point-points"><span><?php echo $log_entry->creds; ?></span></td>
</tr>
