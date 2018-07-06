<nav id="site-nav" class="site-nav" role="navigation" aria-label="<?php esc_attr_e( 'Site Navigation', 'anspress-question-answer' ); ?>"> 
	<div class="l-left"> <?php
	if ( has_nav_menu( 'top' ) ) {
		wp_nav_menu( array(
			'theme_location' 	=> 'top',
			'menu_id'        	=> 'site-menu',
			'menu_class'		 	=>	'no-style main-menu',
		) );
	} ?>
	</div>
	<div class="l-right"> <?php
		if ( ! is_user_logged_in() ) {
			ap_template_part( 'login', 'trigger' );
		} else {
			ap_template_part( 'user', 'nav' );
		} ?>
	</div> 
</nav>