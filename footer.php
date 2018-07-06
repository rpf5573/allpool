</div> <!-- .site-content -->
  <footer class="site-footer" role="contentinfo"> <?php
    ap_template_part( 'site', 'info' ); ?>
  </footer> <!-- .site-footer -->

</div> <!-- #page --> <?php 

ap_template_part( 'mobile-menu' );

wp_footer();

/* ------ Modal ------ */
if ( ! ap_is_login_related_page() ) {
  ap_template_part( 'login', 'modal' );
} ?>

</body>
</html>