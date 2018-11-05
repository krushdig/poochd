<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package Listify
 */
?>

	</div><!-- #content -->

</div><!-- #page -->

<div class="footer-wrapper">

	<?php if ( ! listify_is_job_manager_archive() ) : ?>

		<?php get_template_part( 'content', 'aso' ); ?>

		<?php if ( is_active_sidebar( 'widget-area-footer-1' ) || is_active_sidebar( 'widget-area-footer-2' ) || is_active_sidebar( 'widget-area-footer-3' ) ) : ?>

			<footer class="site-footer-widgets">
				<div class="container">
					<div class="row">

						<div class="footer-widget-column col-xs-12 col-sm-12 col-lg-5">
							<?php dynamic_sidebar( 'widget-area-footer-1' ); ?>
						</div>

						<div class="footer-widget-column col-xs-12 col-sm-6 col-lg-4">
							<?php dynamic_sidebar( 'widget-area-footer-2' ); ?>
						</div>

						<div class="footer-widget-column col-xs-12 col-sm-6 col-lg-3">
							<?php dynamic_sidebar( 'widget-area-footer-3' ); ?>
						</div>

					</div>
				</div>
			</footer>

		<?php endif; ?>

	<?php endif; ?>

	<footer id="colophon" class="site-footer">
		<div class="container">

			<div class="site-social">
				<?php wp_nav_menu( array(
					'theme_location' => 'social',
					'menu_class' => 'nav-menu-social',
					'fallback_cb' => '',
					'depth' => 1,
				) ); ?>
			</div>
			
			<div class="site-info">
				<?php echo listify_partial_copyright_text(); ?>
			</div><!-- .site-info -->
			
			<div class="footer_menu">
			<?php
							wp_nav_menu( array(
								'theme_location' => 'footer-regions'
							) );
						?>
			<!--	<ul>
					<li><a href="#">All Cities</a></li>
					<li><a href="#">Birmingham</a></li>
					<li><a href="#">Brighton</a></li>
					<li><a href="#">Bristol</a></li>
					<li><a href="#">Cardiff</a></li>
					<li><a href="#">Edinburgh</a></li>
					<li><a href="#">Glasgow</a></li>
					<li><a href="#">Leeds</a></li>
					<li><a href="#">Liverpool</a></li>
					<li><a href="#">London</a></li>
					<li><a href="#">Manchester</a></li>
					<li><a href="#">Newcastle upon Tyne</a></li>
					<li><a href="#">Nottingham</a></li>
					<li><a href="#">Sheffield</a></li>
					<li><a href="#">Southampton</a></li>        
				</ul>-->
			</div>

		</div>
	</footer><!-- #colophon -->

</div>

<div id="ajax-response"></div>

<?php wp_footer(); ?>

<script type='text/javascript' src='https://poochd.co.uk/wp-content/themes/listify/js/jquery.preloadinator.min.js'></script>
<script type='text/javascript' src='https://poochd.co.uk/wp-content/themes/listify/js/slick.min.js'></script>
<script>
	jQuery('.js-preloader').preloadinator({
		minTime: 2000
	});
</script>
<script>
jQuery('.sevice-responsive').slick({
  dots: true,
  infinite: false,
  speed: 300,
  slidesToShow: 4,
  slidesToScroll: 1,
  responsive: [
    {
      breakpoint: 1024,
      settings: {
        slidesToShow: 3,
        slidesToScroll: 3,
        infinite: true,
        dots: true
      }
    },
    {
      breakpoint: 600,
      settings: {
        slidesToShow: 2,
        slidesToScroll: 2
      }
    },
    {
      breakpoint: 480,
      settings: {
        slidesToShow: 1,
        slidesToScroll: 1
      }
    }
  ]
});
</script>
</body>
</html>

<?php

if(is_user_logged_in() ){?>

<style>
	#menu-item-637{
		
		display: none;
	}
a.popup-trigger-ajax {
    display: none;
}

</style>
	
	
<?php } ?>
