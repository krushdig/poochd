<?php
/**
 * Reviews for WP Job Manager.
 *
 * @since unknown
 *
 * @package Listify
 * @category Integration
 * @author Astoundify
 */

/**
 * Reviews for WP Job Manager.
 *
 * @since unknown
 */
class Listify_WP_Job_Manager_Reviews extends Listify_Integration {

	/**
	 * Register integration.
	 *
	 * @since unknown
	 */
	public function __construct() {
		$this->has_customizer = true;
		$this->includes = array();
		$this->integration = 'wp-job-manager-reviews';

		parent::__construct();
	}

	/**
	 * Hook in to WordPress.
	 *
	 * @since unknown
	 */
	public function setup_actions() {
		add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_scripts' ), 11 );

		// Filter the listing's average rating.
		add_filter( 'listify_get_listing_rating_average', array( $this, 'rating_average' ), 10, 2 );

		// Filter the listing's rating count.
		add_filter( 'listify_get_listing_rating_count', array( $this, 'rating_count' ), 10, 2 );

		// Filter the listing's best rating.
		add_filter( 'listify_get_listing_rating_best' , array( $this, 'rating_best' ) , 10, 2 );

		// Filter the listing's worst rating.
		add_filter( 'listify_get_listing_rating_worst' , array( $this, 'rating_worst' ) , 10, 2 );
	}

	/**
	 * Remove built in styles.
	 *
	 * @since unknown
	 */
	public function dequeue_scripts() {
		wp_dequeue_style( 'wp-job-manager-reviews' );
	}

	/**
	 * Filter the rating average based on WP Job Manager - Reviews.
	 *
	 * @since 2.0.0
	 *
	 * @param int                    $average The current average.
	 * @param object Listify_Listing $listing The current listing.
	 * @return int
	 */
	public function rating_average( $average, $listing ) {
		$wpjmr = wpjmr();

		return $wpjmr->listing->reviews_average( $listing->get_id() );
	}

	/**
	 * Filter the rating count based on WP Job Manager - Reviews.
	 *
	 * @since 2.0.0
	 *
	 * @param int                    $average The current count.
	 * @param object Listify_Listing $listing The current listing.
	 * @return int
	 */
	public function rating_count( $average, $listing ) {
		$wpjmr = wpjmr();

		return $wpjmr->listing->reviews_count( $listing->get_id() );
	}

	/**
	 * Filter the best rating based on WP Job Manager - Reviews.
	 *
	 * @since 2.0.0
	 *
	 * @param int                    $average The current count.
	 * @param object Listify_Listing $listing The current listing.
	 * @return int
	 */
	public function rating_best( $best, $listing ) {
		// Get reviews.
		$wpjmr = wpjmr();
		$reviews = $wpjmr->reviews->get_reviews_by_id( $listing->get_id() );
		if ( ! $reviews ) {
			return $best;
		}

		// Max star:
		$max = absint( get_option( 'wpjmr_star_count' ) );

		// Get best.
		foreach ( $reviews as $comment ) {

			// Get review average.
			$review = $wpjmr->review->get_review_average( $comment->comment_ID );
			if ( $review ) {

				// Cannot higher that max.
				$review = $review > $max ? $max : $review;

				// Set as best if higher that current value.
				$best = $best < $review ? $review : $best;

				// Break loop if already the max value.
				if ( $best === $max ) {
					break;
				}
			}
		}

		return $best;
	}

	/**
	 * Filter the worst rating based on WP Job Manager - Reviews.
	 *
	 * @since 2.0.0
	 *
	 * @param int                    $average The current count.
	 * @param object Listify_Listing $listing The current listing.
	 * @return int
	 */
	public function rating_worst( $worst, $listing ) {
		$wpjmr = wpjmr();
		$reviews = $wpjmr->reviews->get_reviews_by_id( $listing->get_id() );
		if ( ! $reviews ) {
			return $worst;
		}

		// Max star:
		$max = absint( get_option( 'wpjmr_star_count' ) );
		$worst = $max; // Set to max.

		// Get worst.
		foreach ( $reviews as $comment ) {

			// Get review average.
			$review = $wpjmr->review->get_review_average( $comment->comment_ID );
			if ( $review ) {

				$worst = $worst > $review ? $review : $worst; // 1 is min review.

				// Break loop if already the min value (1).
				if ( 1 === $worst ) {
					break;
				}
			}
		}

		return $worst;
	}

}

$GLOBALS['listify_job_manager_reviews'] = new Listify_WP_Job_Manager_Reviews();
