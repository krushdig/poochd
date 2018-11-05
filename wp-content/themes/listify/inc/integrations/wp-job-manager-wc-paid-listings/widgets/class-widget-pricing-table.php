<?php
/**
 * WC Paid Listing - Pricing Table
 *
 * @since 1.0.0
 *
 * @package Listify
 * @category Widget
 * @author Astoundify
 */
class Listify_Widget_WCPL_Pricing_Table extends Listify_Widget {

	/**
	 * Register widget settings.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->widget_description = __( 'Display the pricing packages available for listings', 'listify' );
		$this->widget_id          = 'listify_widget_panel_wcpl_pricing_table';
		$this->widget_name        = __( 'Listify - Page: Pricing Table', 'listify' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => '',
				'label' => __( 'Title:', 'listify' ),
			),
			'description' => array(
				'type'  => 'text',
				'std'   => '',
				'label' => __( 'Description:', 'listify' ),
			),
			'stacked' => array(
				'type' => 'checkbox',
				'std' => 0,
				'label' => __( 'Use "stacked" display style', 'listify' ),
			),
		);

		parent::__construct();
	}

	/**
	 * Echoes the widget content.
	 *
	 * @since 1.7.0
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		$packages = $this->get_packages();
		$count = count( $packages->posts );

		if ( $count > 3 ) {
			$count = 3;
		}

		if ( ! $packages->have_posts() ) {
			return;
		}

		$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '', $instance, $this->id_base );
		$description = isset( $instance['description'] ) ? esc_attr( $instance['description'] ) : false;
		$stacked = isset( $instance['stacked'] ) && 1 === (int) $instance['stacked'] ? true : false;

		$after_title = '<h2 class="home-widget-description">' . $description . '</h2>' . $after_title;
		$layout = 'inline';

		ob_start();

		echo $before_widget; // WPCS: XSS ok.

		if ( $title ) {
			echo $before_title . $title . $after_title; // WPCS: XSS ok.
		}
?>

<ul class="job-packages <?php if ( $stacked ) : ?>job-packages--stacked<?php else : ?> job-packages--inline job-packages--count-<?php echo esc_attr( $count ); ?><?php endif; ?>">

<?php while ( $packages->have_posts() ) : $packages->the_post();
	$product = wc_get_product( get_the_ID() ); ?>

	<?php $tags = wc_get_product_tag_list( $product->get_id() ); ?>
	<?php $action_url = add_query_arg( 'choose_package', $product->get_id(), job_manager_get_permalink( 'submit_job_form' ) ); ?>

	<li class="job-package<?php if ( $stacked ) : ?> job-package--stacked<?php endif; ?>">
		<?php if ( $tags ) : ?>
			<span class="job-package-tag<?php if ( $stacked ) : ?> job-package-tag--stacked<?php endif; ?>"><span class="job-package-tag__text"><?php echo esc_attr( strip_tags( $tags ) ); ?></span></span>
		<?php endif; ?>

		<div class="job-package-header<?php if ( $stacked ) : ?> job-package-header--stacked<?php endif; ?>">
			<div class="job-package-title<?php if ( $stacked ) : ?> job-package-title--stacked<?php endif; ?>">
				<?php echo esc_attr( $product->get_title() ); ?>
			</div>
			<div class="job-package-price<?php if ( $stacked ) : ?> job-package-price--stacked<?php endif; ?>">
				<?php echo $product->get_price_html(); // WPCS: XSS ok. ?>
			</div>

			<div class="job-package-purchase<?php if ( $stacked ) : ?> job-package-purchase--stacked<?php endif; ?>">
				<a href="<?php echo esc_url( $action_url ); ?>" class="button"><?php esc_html_e( 'Get Started Now &rarr;', 'listify' ); ?></a>
			</div>
		</div>

		<div class="job-package-includes<?php if ( $stacked ) : ?> job-package-includes--stacked<?php endif; ?>">
			<?php
				$content = $product->get_description();
				$content = (array) explode( "\n", $content );
			?>
			<ul>
				<li><?php echo implode( '</li><li>', $content ); // WPCS: XSS ok. ?></li>
			</ul>
		</div>

		<div class="job-package-purchase<?php if ( $stacked ) : ?> job-package-purchase--stacked<?php endif; ?>">
			<a href="<?php echo esc_url( $action_url ); ?>" class="button"><?php esc_html_e( 'Get Started Now &rarr;', 'listify' ); ?></a>
		</div>
	</li>

<?php endwhile; ?>

</ul>

<?php
		echo $after_widget; // WPCS: XSS ok.

		echo apply_filters( $this->widget_id, ob_get_clean() ); // WPCS: XSS ok.
	}

	/**
	 * Find packagees available for purchase.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	private function get_packages() {
		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'suppress_filters' => false,
			'tax_query'      => array(
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => array( 'job_package', 'job_package_subscription' ),
				),
			),
			'orderby' => 'menu_order',
			'order' => 'asc',
			'lang' => defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : substr( get_locale(), 0, 2 ),
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'no_found_rows' => true,
		);

		if ( listify_has_integration( 'wp-job-manager-claim-listing' ) ) {
			$args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key'     => '_use_for_claims',
					'value'   => '',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => '_use_for_claims',
					'value'   => 'yes',
					'compare' => '!=',
				),
			);
		}

		$packages = new WP_Query( apply_filters( 'listify_pricing_table_packages', $args ) );

		return $packages;
	}

}
