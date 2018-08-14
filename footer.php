</div> <!-- .site-content -->
  <footer class="site-footer" role="contentinfo"> <?php
    ap_template_part( 'site', 'info' ); ?>
  </footer> <!-- .site-footer -->

</div> <!-- #page --> <?php 

ap_template_part( 'mobile-menu' );

/* ------ Modal ------ */
// this should be included earlyer than wp_footer()
// because of social login icon of naver
if ( ! ap_is_login_related_page() ) {
  ap_template_part( 'login', 'modal' );
} 

wp_footer(); ?>

</body>
</html>