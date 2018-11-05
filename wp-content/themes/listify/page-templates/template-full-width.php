<?php
/**
 * Template Name: Layout: Full Width
 *
 * @package Listify
 */

get_header(); 

?>


<div class="full-sear">
	<div <?php echo apply_filters( 'listify_cover', 'homepage-cover page-cover entry-cover entry-cover--home entry-cover--' . get_theme_mod( 'home-hero-overlay-style', 'default' ), array( 'size' => 'full' ) ); ?>>
				<div class="cover-wrapper container">
					<?php
						the_widget(
							'Listify_Widget_Search_Listings',
							apply_filters( 'listify_widget_search_listings_default', array(
								'title' => get_the_title(),
								'description' => strip_shortcodes( get_the_content() ),
							) ),
							array(
								'before_widget' => '<div class="listify_widget_search_listings">',
								'after_widget'  => '</div>',
								'before_title'  => '<div class="home-widget-section-title"><h1 class="home-widget-title">',
								'after_title'   => '</h1></div>',
								'widget_id'     => 'search-12391',
							)
						);
					?>
				</div>

				<?php if ( 'video' == $style && function_exists( 'the_custom_header_markup' ) ) : ?>
					<div class="custom-header-video">
						<div class="custom-header-media">
							<?php
								add_filter( 'theme_mod_external_header_video', 'listify_header_video' );
								the_custom_header_markup();
								remove_filter( 'theme_mod_external_header_video', 'listify_header_video' );
							?>
						</div>
					</div>
				<?php endif; ?>

			</div>
			</div>

	<?php do_action( 'listify_page_before' ); ?>

	<div id="primary" class="container">
		<div class="content-area">

			<main id="main" class="site-main" role="main">

				<?php if ( listify_has_integration( 'woocommerce' ) ) : ?>
					<?php wc_print_notices(); ?>
				<?php endif; ?>

				<?php while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'content', 'page' ); ?>

					<?php comments_template(); ?>
				<?php endwhile; ?>

			</main>

		</div>
	</div>

<?php get_footer(); ?>
<style>
.full-sear .job_types, .full-sear .in-use {
	display: none !important;
}
.full-sear .search_jobs {
    width: 80%;
    float: left;
}
.full-sear .update_results {
    float: right;
    width: 17%;
}
.full-sear .listify_widget_search_listings {
    text-align: center;
}
.full-sear .listify_widget_search_listings h1 {
    display: inline-block;
}
.full-sear .search_jobs .chosen-single {
    text-align: left;
}
.full-sear .search_jobs .chosen-drop {
    text-align: left;
}
</style>
