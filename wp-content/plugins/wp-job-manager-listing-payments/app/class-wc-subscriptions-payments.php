<?php
/**
 * WooCommerce Subscription.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category Core
 * @author Astoundify
 */

namespace Astoundify\WPJobManager\ListingPayments;

/**
 * WooCommerce Subscription Class.
 *
 * @since 2.0.0
 */
class WC_Subscriptions_Payments {

	/**
	 * Object Class Instance.
	 *
	 * @since 2.0.0
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Get the class instance
	 *
	 * @since 2.0.0
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			return new self;
		}
		return self::$instance;
	}

	/**
	 * Constructor Class.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		// Add job and resume as valid subscription.
		add_filter( 'woocommerce_is_subscription', array( $this, 'woocommerce_is_subscription' ), 10, 2 );

		// Subscription Synchronisation.
		// activate sync (process meta) for job package and resume package.
		if ( class_exists( 'WC_Subscriptions_Synchroniser' ) && method_exists( 'WC_Subscriptions_Synchroniser', 'save_subscription_meta' ) ) {

			// Job package.
			add_action( 'woocommerce_process_product_meta_job_package_subscription', 'WC_Subscriptions_Synchroniser::save_subscription_meta', 10 );

			// Resume package.
			add_action( 'woocommerce_process_product_meta_resume_package_subscription', 'WC_Subscriptions_Synchroniser::save_subscription_meta', 10 );
		}

		// Prevent listing linked to product(subs) never expire automatically.
		add_action( 'added_post_meta', array( $this, 'updated_post_meta' ), 10, 4 );
		add_action( 'updated_post_meta', array( $this, 'updated_post_meta' ), 10, 4 );

		// When listing expires, adjust user package usage and delete package & user package meta in listing.
		add_action( 'publish_to_expired', array( $this, 'check_expired_listing' ) );

		// Change user package usage when trash/untrash listing.
		add_action( 'wp_trash_post', array( $this, 'wp_trash_post' ) );
		add_action( 'untrash_post', array( $this, 'untrash_post' ) );

		/* === SUBS ENDED. === */

		// Subscription Paused (on Hold).
		add_action( 'woocommerce_subscription_status_on-hold', array( $this, 'subscription_ended' ) );

		// Subscription Ended.
		add_action( 'woocommerce_scheduled_subscription_expiration', array( $this, 'subscription_ended' ) );

		// When a subscription ends after remaining unpaid.
		add_action( 'woocommerce_scheduled_subscription_end_of_prepaid_term', array( $this, 'subscription_ended' ) );

		// When the subscription status changes to cancelled.
		add_action( 'woocommerce_subscription_status_cancelled', array( $this, 'subscription_ended' ) );

		// Subscription is expired.
		add_action( 'woocommerce_subscription_status_expired', array( $this, 'subscription_ended' ) );

		/* === SUBS STARTS. === */

		// Subscription starts ( status changes to active ).
		add_action( 'woocommerce_subscription_status_active', array( $this, 'subscription_activated' ) );

		/* === SUBS RENEWED. === */

		// When the subscription is renewed.
		add_action( 'woocommerce_subscription_renewal_payment_complete', array( $this, 'subscription_renewed' ) );

		/* === SUBS SWITCHED (UPGRADE/DOWNGRADE). === */

		// When the subscription is switched and a new subscription is created.
		add_action( 'woocommerce_subscriptions_switched_item', array( $this, 'subscription_switched' ), 10, 3 );

		// When the subscription is switched and only the item is changed.
		add_action( 'woocommerce_subscription_item_switched', array( $this, 'subscription_item_switched' ), 10, 4 );
	}

	/*
	 * Utility Functions.
	 ************************
	 */

	/**
	 * Get subscription type for pacakge by ID.
	 *
	 * @since 2.0.0
	 *
	 * @param  int $product_id WC Product ID.
	 * @return string
	 */
	public function get_package_subscription_type( $product_id ) {
		$subscription_type = get_post_meta( $product_id, '_package_subscription_type', true );
		return empty( $subscription_type ) ? 'package' : $subscription_type;
	}

	/*
	 * Hooks Starts Here.
	 ************************
	 */

	/**
	 * Is this a subscription product?
	 *
	 * @since 2.0.0
	 *
	 * @param bool $is_subscription Is package a subscription.
	 * @param int  $product_id      WC Product ID.
	 * @return bool
	 */
	public function woocommerce_is_subscription( $is_subscription, $product_id ) {
		$product = wc_get_product( $product_id );
		if ( $product && $product->is_type( array( 'job_package_subscription', 'resume_package_subscription' ) ) ) {
			$is_subscription = true;
		}
		return $is_subscription;
	}

	/**
	 * Prevent listings linked to subscriptions from expiring.
	 *
	 * @since 2.0.0
	 *
	 * @param int    $meta_id     Meta ID.
	 * @param int    $object_id   Post ID.
	 * @param string $meta_key    Meta Key.
	 * @param mixed  $meta_value  Meta Value.
	 */
	public function updated_post_meta( $meta_id, $object_id, $meta_key, $meta_value ) {
		if ( 'job_listing' === get_post_type( $object_id ) && '' !== $meta_value && '_job_expires' === $meta_key ) {
			$_package_id = get_post_meta( $object_id, '_package_id', true );
			$package     = wc_get_product( $_package_id );

			if ( $package && 'job_package_subscription' === $package->get_type() && 'listing' === $package->get_package_subscription_type() ) {
				update_post_meta( $object_id, '_job_expires', '' ); // Never expire automatically.
			}
		}
	}


	/**
	 * If a listing is expired, the pack may need it's listing count changing.
	 *
	 * @since 2.0.0
	 *
	 * @param object $post WP_Post.
	 */
	public function check_expired_listing( $post ) {
		global $wpdb;

		if ( 'job_listing' === $post->post_type || 'resume' === $post->post_type ) {
			$package_product_id = get_post_meta( $post->ID, '_package_id', true );
			$package_id         = get_post_meta( $post->ID, '_user_package_id', true );
			$package_product    = get_post( $package_product_id );

			if ( $package_product_id ) {
				$subscription_type = $this->get_package_subscription_type( $package_product_id );

				if ( 'listing' === $subscription_type ) {
					$new_count = $wpdb->get_var( $wpdb->prepare( "SELECT package_count FROM {$wpdb->prefix}wcpl_user_packages WHERE id = %d;", $package_id ) );
					$new_count --;

					$wpdb->update(
						"{$wpdb->prefix}wcpl_user_packages",
						array(
							'package_count'  => max( 0, $new_count ),
						),
						array(
							'id' => $package_id,
						)
					);

					// Remove package meta after adjustment.
					delete_post_meta( $post->ID, '_package_id' );
					delete_post_meta( $post->ID, '_user_package_id' );
				}
			}
		}
	}

	/**
	 * If a listing gets trashed/deleted, the pack may need it's listing count changing.
	 *
	 * @since 2.0.0
	 *
	 * @param int $id Post ID.
	 */
	public function wp_trash_post( $id ) {
		global $wpdb;

		if ( $id > 0 ) {
			$post_type = get_post_type( $id );

			if ( 'job_listing' === $post_type || 'resume' === $post_type ) {
				$package_product_id = get_post_meta( $id, '_package_id', true );
				$package_id         = get_post_meta( $id, '_user_package_id', true );
				$package_product    = get_post( $package_product_id );

				if ( $package_product_id ) {
					$subscription_type = $this->get_package_subscription_type( $package_product_id );

					if ( 'listing' === $subscription_type ) {
						$new_count = $wpdb->get_var( $wpdb->prepare( "SELECT package_count FROM {$wpdb->prefix}wcpl_user_packages WHERE id = %d;", $package_id ) );
						$new_count --;

						$wpdb->update(
							"{$wpdb->prefix}wcpl_user_packages",
							array(
								'package_count'  => max( 0, $new_count ),
							),
							array(
								'id' => $package_id,
							)
						);
					}
				}
			}
		}
	}

	/**
	 * If a listing gets restored, the pack may need it's listing count changing.
	 *
	 * @since 2.0.0
	 *
	 * @param int $id Post ID.
	 */
	public function untrash_post( $id ) {
		global $wpdb;

		if ( $id > 0 ) {
			$post_type = get_post_type( $id );

			if ( 'job_listing' === $post_type || 'resume' === $post_type ) {
				$package_product_id = get_post_meta( $id, '_package_id', true );
				$package_id         = get_post_meta( $id, '_user_package_id', true );
				$package_product    = get_post( $package_product_id );

				if ( $package_product_id ) {
					$subscription_type = $this->get_package_subscription_type( $package_product_id );

					if ( 'listing' === $subscription_type ) {
						$package  = $wpdb->get_row( $wpdb->prepare( "SELECT package_count, package_limit FROM {$wpdb->prefix}wcpl_user_packages WHERE id = %d;", $package_id ) );
						$new_count = $package->package_count + 1;

						$wpdb->update(
							"{$wpdb->prefix}wcpl_user_packages",
							array(
								'package_count'  => min( $package->package_limit, $new_count ),
							),
							array(
								'id' => $package_id,
							)
						);
					}
				}
			}
		}
	}

	/**
	 * Subscription has expired - cancel job packs.
	 *
	 * @since 2.0.0
	 *
	 * @param object $subscription Subscription Object.
	 */
	public function subscription_ended( $subscription ) {
		global $wpdb;

		foreach ( $subscription->get_items() as $item ) {
			$subscription_type = $this->get_package_subscription_type( $item['product_id'] );
			$legacy_id         = isset( $subscription->order->id ) ? $subscription->order->id : $subscription->id;
			$user_package      = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wcpl_user_packages WHERE order_id IN ( %d, %d ) AND product_id = %d;", $subscription->id, $legacy_id, $item['product_id'] ) );

			if ( $user_package ) {
				// Delete the package.
				$wpdb->delete(
					"{$wpdb->prefix}wcpl_user_packages",
					array(
						'id' => $user_package->id,
					)
				);

				// Expire listings posted with package.
				if ( 'listing' === $subscription_type ) {
					$listing_ids = astoundify_wpjmlp_get_listings_for_package( $user_package->id );

					foreach ( $listing_ids as $listing_id ) {
						$listing = array(
							'ID'          => $listing_id,
							'post_status' => 'expired',
						);
						wp_update_post( $listing );

						// Make a record of the subscription ID in case of re-activation.
						update_post_meta( $listing_id, '_expired_subscription_id', $subscription->id );
					}
				}
			}
		}

		delete_post_meta( $subscription->id, 'wc_paid_listings_subscription_packages_processed' );
	}

	/**
	 * Subscription activated.
	 *
	 * @since 2.0.0
	 *
	 * @param object $subscription Subscription object.
	 */
	public function subscription_activated( $subscription ) {
		global $wpdb;

		if ( get_post_meta( $subscription->id, 'wc_paid_listings_subscription_packages_processed', true ) ) {
			return;
		}

		// Remove any old packages for this subscription.
		$legacy_id = isset( $subscription->order->id ) ? $subscription->order->id : $subscription->id;
		$wpdb->delete( "{$wpdb->prefix}wcpl_user_packages", array(
			'order_id' => $legacy_id,
		) );
		$wpdb->delete( "{$wpdb->prefix}wcpl_user_packages", array(
			'order_id' => $subscription->id,
		) );

		foreach ( $subscription->get_items() as $item ) {
			$product           = wc_get_product( $item['product_id'] );
			$subscription_type = $this->get_package_subscription_type( $item['product_id'] );

			// Give user packages for this subscription.
			if ( $product->is_type( array( 'job_package_subscription', 'resume_package_subscription' ) ) && $subscription->get_user_id() && ! isset( $item['switched_subscription_item_id'] ) ) {

				// Give packages to user.
				for ( $i = 0; $i < $item['qty']; $i ++ ) {
					$user_package_id = astoundify_wpjmlp_give_user_package( $subscription->get_user_id(), $product->get_id(), $subscription->id );
				}

				/**
				 * If the subscription is associated with listings, see if any,
				 * already match this ID and approve them (useful on re-activation of a sub).
				 */
				if ( 'listing' === $subscription_type ) {
					$listing_ids = (array) $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key=%s AND meta_value=%s", '_expired_subscription_id', $subscription->id ) );
				} else {
					$listing_ids = array();
				}

				$listing_ids[] = isset( $item['job_id'] ) ? $item['job_id'] : '';
				$listing_ids[] = isset( $item['resume_id'] ) ? $item['resume_id'] : '';
				$listing_ids   = array_unique( array_filter( array_map( 'absint', $listing_ids ) ) );

				foreach ( $listing_ids as $listing_id ) {
					if ( in_array( get_post_status( $listing_id ), array( 'pending_payment', 'expired' ), true ) ) {
						astoundify_wpjmlp_approve_listing_with_package( $listing_id, $subscription->get_user_id(), $user_package_id );
						delete_post_meta( $listing_id, '_expired_subscription_id' );
					}
				}
			}
		}

		update_post_meta( $subscription->id, 'wc_paid_listings_subscription_packages_processed', true );
	}

	/**
	 * Subscription renewed - renew the job pack.
	 *
	 * @since 2.0.0
	 *
	 * @param object $subscription Subscription object.
	 */
	public function subscription_renewed( $subscription ) {
		global $wpdb;

		foreach ( $subscription->get_items() as $item ) {
			$product           = wc_get_product( $item['product_id'] );
			$subscription_type = $this->get_package_subscription_type( $item['product_id'] );
			$legacy_id         = isset( $subscription->order->id ) ? $subscription->order->id : $subscription->id;

			// Renew packages which refresh every term.
			if ( 'package' === $subscription_type ) {
				if ( ! $wpdb->update(
					"{$wpdb->prefix}wcpl_user_packages",
					array(
						'package_count'  => 0,
					),
					array(
						'order_id'   => $subscription_id,
						'product_id' => $item['product_id'],
					)
				) ) {
					astoundify_wpjmlp_give_user_package( $subscription->get_user_id(), $item['product_id'], $subscription->id );
				}
			} else { // Otherwise the listings stay active, but we can ensure they are synced in terms of featured status etc.
				$user_package_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}wcpl_user_packages WHERE order_id IN ( %d, %d ) AND product_id = %d;", $subscription->id, $legacy_id, $item['product_id'] ) );
				if ( $user_package_ids ) {
					foreach ( $user_package_ids as $user_package_id ) {
						$package = astoundify_wpjmlp_get_user_package( $user_package_id );
						$listing_ids = astoundify_wpjmlp_get_listings_for_package( $user_package_id );
						if ( $listing_ids ) {
							foreach ( $listing_ids as $listing_id ) {
								// Featured or not?
								update_post_meta( $listing_id, '_featured', $package->is_listing_featured() ? 1 : 0 );
							}
						}
					}
				}
			}
		} // End foreach().
	}

	/**
	 * When switching a subscription we need to update old listings.
	 * No need to give the user a new package; that is still handled by the orders class.
	 *
	 * @since 2.0.0
	 *
	 * @param object $order             WC Order.
	 * @param object $subscription      WC Subscription.
	 * @param int    $new_order_item_id New order Item ID.
	 * @param int    $old_order_item_id Old order Item ID.
	 */
	public function subscription_item_switched( $order, $subscription, $new_order_item_id, $old_order_item_id ) {
		global $wpdb;

		$new_order_item = \WC_Subscriptions_Order::get_item_by_id( $new_order_item_id );
		$old_order_item = \WC_Subscriptions_Order::get_item_by_id( $old_order_item_id );

		$new_subscription = (object) array(
			'id'           => $subscription->id,
			'subscription' => $subscription,
			'product_id'   => $new_order_item['product_id'],
			'product'      => wc_get_product( $new_order_item['product_id'] ),
			'type'         => $this->get_package_subscription_type( $new_order_item['product_id'] ),
		);

		$old_subscription = (object) array(
			'id'           => $subscription->id,
			'subscription' => $subscription,
			'product_id'   => $old_order_item['product_id'],
			'product'      => wc_get_product( $old_order_item['product_id'] ),
			'type'         => $this->get_package_subscription_type( $old_order_item['product_id'] ),
		);

		$this->switch_package( $subscription->get_user_id(), $new_subscription, $old_subscription );
	}

	/**
	 * When switching a subscription we need to update old listings.
	 * No need to give the user a new package; that is still handled by the orders class.
	 *
	 * @since 2.0.0
	 *
	 * @param object $subscription    WC Subscription.
	 * @param array  $new_order_item  New order Item ID.
	 * @param array  $old_order_item  Old order Item ID.
	 */
	public function subscription_switched( $subscription, $new_order_item, $old_order_item ) {
		global $wpdb;

		$new_subscription = (object) array(
			'id'         => $subscription->id,
			'product_id' => $new_order_item['product_id'],
			'product'    => wc_get_product( $new_order_item['product_id'] ),
			'type'       => $this->get_package_subscription_type( $new_order_item['product_id'] ),
		);

		$old_subscription = (object) array(
			'id'         => $wpdb->get_var( $wpdb->prepare( "SELECT order_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_id = %d ", $new_order_item['switched_subscription_item_id'] ) ),
			'product_id' => $old_order_item['product_id'],
			'product'    => wc_get_product( $old_order_item['product_id'] ),
			'type'       => $this->get_package_subscription_type( $old_order_item['product_id'] ),
		);

		$this->switch_package( $subscription->get_user_id(), $new_subscription, $old_subscription );
	}

	/**
	 * Handle Switch Event.
	 *
	 * @since 2.0.0
	 *
	 * @param int    $user_id          User ID.
	 * @param object $new_subscription New Subscription.
	 * @param object $old_subscription Old Subscription.
	 */
	public function switch_package( $user_id, $new_subscription, $old_subscription ) {
		global $wpdb;

		// Get the user package.
		$legacy_id    = isset( $old_subscription->subscription->order->id ) ? $old_subscription->subscription->order->id : $old_subscription->id;
		$user_package = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wcpl_user_packages WHERE order_id IN ( %d, %d ) AND product_id = %d;", $old_subscription->id, $legacy_id, $old_subscription->product_id ) );

		if ( $user_package ) {
			// If invalid, abort.
			if ( ! $new_subscription->product->is_type( array( 'job_package_subscription', 'resume_package_subscription' ) ) ) {
				return false;
			}

			// Give new package to user.
			$switching_to_package_id = astoundify_wpjmlp_give_user_package( $user_id, $new_subscription->product_id, $new_subscription->id );

			// Upgrade?
			$is_upgrade = ( 0 === $new_subscription->product->get_limit() || $new_subscription->product->get_limit() >= $user_package->package_count );

			// Delete the old package.
			$wpdb->delete( "{$wpdb->prefix}wcpl_user_packages", array(
				'id' => $user_package->id,
			) );

			// Update old listings.
			if ( 'listing' === $new_subscription->type && $switching_to_package_id ) {
				$listing_ids = astoundify_wpjmlp_get_listings_for_package( $user_package->id );

				foreach ( $listing_ids as $listing_id ) {
					// If we are not upgrading, expire the old listing.
					if ( ! $is_upgrade ) {
						$listing = array(
							'ID'          => $listing_id,
							'post_status' => 'expired',
						);
						wp_update_post( $listing );
					} else {
						astoundify_wpjmlp_increase_package_count( $user_id, $switching_to_package_id );
						// Change the user package ID and package ID.
						update_post_meta( $listing_id, '_user_package_id', $switching_to_package_id );
						update_post_meta( $listing_id, '_package_id', $new_subscription->product_id );
					} // End if().

					// Featured or not.
					update_post_meta( $listing_id, '_featured', $new_subscription->product->is_listing_featured() ? 1 : 0 );

					// Fire action.
					do_action( 'astoundify_wpjmlp_switched_subscription', $listing_id, $user_package );
				} // End foreach().
			} // End if().
		} // End if().
	}
}
