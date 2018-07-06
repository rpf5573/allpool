<?php
/**
 * The template for displaying archive pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */
get_header();
?>

<div class="content-area">
  <main id="main" class="site-main" role="main"> <?php
    if ( have_posts() ) :
      while( have_posts() ) : the_post();
        the_content();
      endwhile;
    endif; ?>
  </main> <!-- #main -->
</div> <!-- content-area -->

<?php get_footer();