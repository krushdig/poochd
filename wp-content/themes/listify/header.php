<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package Listify
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--<meta http-equiv="Content-Security-Policy" content="block-all-mixed-content">-->
	<!--<link href="<?php //echo get_template_directory_uri(); ?>/fonts/Dosis-Light.ttf" rel="stylesheet">-->
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	
	<link rel='stylesheet' id='listify-css'  href='https://poochd.co.uk/wp-content/themes/listify/css/slick.css' type='text/css' media='all' />
	<link rel='stylesheet' id='listify-css'  href='https://poochd.co.uk/wp-content/themes/listify/css/slick-theme.css' type='text/css' media='all' />
	<?php wp_head(); ?>
</head>


<body <?php body_class(); ?>>
	<!--<div class="preloader js-preloader flex-center">
		<div class="dots">
			<div class="dot"></div>
			<div class="dot"></div>
			<div class="dot"></div>
		</div>
	</div>-->
<div id="page" class="hfeed site">

	<header id="masthead" class="site-header<?php if ( is_front_page() ) :?> site-header--<?php echo get_theme_mod( 'home-header-style', 'default' ); ?><?php endif; ?>">
		<div class="header_top">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<div class="upper_menu">
							<!--<ul class="upper_menu_c">
								<li><a href="#"><i class="fa fa-envelope" aria-hidden="true"></i>info@poochd.co.uk</a></li>
								<li><a href="#"><i class="fa fa-volume-control-phone" aria-hidden="true"></i>0333 444 0224</a></li>
							</ul>-->
							
							<?php if ( is_active_sidebar( 'top-bar-1' ) ) : ?>				
									<?php dynamic_sidebar( 'top-bar-1' ); ?>				
							<?php endif; ?>	
						<!--	<ul class="upper_menu_login">
								<li><a href="#"><i class="fa fa-user" aria-hidden="true"></i>login/Signup</a></li>
							</ul>-->
							<?php wp_nav_menu(array( 'menu_class' => 'upper_menu_login','theme_location' => 'header_navigation')); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="primary-header">
			<div class="container">
				<div class="primary-header-inner">
					<div class="site-branding">
						<?php echo listify_partial_site_branding(); ?>
					</div>

					<div class="primary nav-menu">
						<?php
							wp_nav_menu( array(
								'theme_location' => 'primary',
								'container_class' => 'nav-menu-container',
							) );
						?>
					</div>
				</div>

				<?php if ( get_theme_mod( 'nav-search', true ) ) : ?>
				<div id="search-header" class="search-overlay">
					<div class="container">
						<?php locate_template( array( 'searchform-header.php', 'searchform.php' ), true, false ); ?>
						<a href="#search-header" data-toggle="#search-header" class="ion-close search-overlay-toggle"></a>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>

		<nav id="site-navigation" class="main-navigation<?php if ( is_front_page() ) : ?> main-navigation--<?php echo get_theme_mod( 'home-header-style', 'default' ); ?><?php endif; ?>">
			<div class="container">
				<a href="#" class="navigation-bar-toggle">
					<i class="ion-navicon-round"></i>
					<span class="mobile-nav-menu-label"><?php echo listify_get_theme_menu_name( 'primary' ); ?></span>
				</a>

				<div class="navigation-bar-wrapper">
					<?php
						wp_nav_menu( array(
							'theme_location' => 'primary',
							'container_class' => 'primary nav-menu',
							'menu_class' => 'primary nav-menu',
						) );
						
						if ( listify_theme_mod( 'nav-secondary', true ) ) {
							wp_nav_menu( array(
								'theme_location' => 'secondary',
								'container_class' => 'secondary nav-menu',
								'menu_class' => 'secondary nav-menu',
							) );
						}
					?>
				</div>

				<?php if ( 'none' !== get_theme_mod( 'nav-search', 'left' ) ) : ?>
					<a href="#search-navigation" data-toggle="#search-navigation" class="ion-search search-overlay-toggle"></a>

					<div id="search-navigation" class="search-overlay">
						<?php locate_template( array( 'searchform-header.php', 'searchform.php' ), true, false ); ?>

						<a href="#search-navigation" data-toggle="#search-navigation" class="ion-close search-overlay-toggle"></a>
					</div>
				<?php endif; ?>
			</div>
		</nav><!-- #site-navigation -->
	</header><!-- #masthead -->

	<?php do_action( 'listify_content_before' ); ?>

	<div id="content" class="site-content">
