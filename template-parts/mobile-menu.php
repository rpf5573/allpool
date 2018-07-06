<nav id="mobile-menu" class="mobile-menu d-none"> <?php
	$args = array(
		'theme_location' => 'mobile-no-login',
		'menu_id'        => '',
		'menu_class'		 =>	''
	);
	if ( is_user_logged_in() ) {
		$args['theme_location'] = 'mobile-logged-in';
	}
	wp_nav_menu( $args ); ?>
</nav>