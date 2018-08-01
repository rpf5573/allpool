<?php
ob_start();
?>
	<div class="dimmed-background" style="display:none"></div>
<?php
	$html_dimmed_background = ob_get_clean();
	$trim_html_dimmed_background = preg_replace($regexNewline, '', $html_dimmed_background);
?>
	<script type="text/javascript">
		jQuery(function($) {
			if ( $('.dimmed-background').length == 0 ) {
				$('body').append('<?=$trim_html_dimmed_background?>');
			}
		});
	</script>
<?php
return $trim_html_dimmed_background;