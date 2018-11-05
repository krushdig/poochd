<?php
/**
 * List User Packages.
 *
 * @since 2.0.0
 *
 * @package Listing Payments
 * @category User Packages
 * @author Astoundify
 */

namespace Astoundify\WPJobManager\ListingPayments;

// Load WP List Table Class.
if ( ! class_exists( '\WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * List Table User Packages
 *
 * @extends WP_List_Table
 */
class User_Packages_Admin_List_Packages extends \WP_List_Table {

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'package',
			'plural'   => 'packages',
			'ajax'     => false,
		) );
	}

	/**
	 * Markup for CB column.
	 *
	 * @since 2.0.0
	 *
	 * @param object $item Item.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],
			/*$2%s*/ $item->id
		);
	}


	/**
	 * Default Column function.
	 *
	 * @since 2.0.0
	 *
	 * @access public
	 * @param object $item        Item.
	 * @param string $column_name Column Name.
	 */
	public function column_default( $item, $column_name ) {
		global $wpdb;

		switch ( $column_name ) {
			case 'id' :
				return $item->id;
			case 'product_id' :
				$product = wc_get_product( $item->product_id );

				return $product ? '<a href="' . admin_url( 'post.php?post=' . absint( $product->get_id() ) . '&action=edit' ) . '">' . esc_html( $product->get_title() ) . '</a>' : __( 'n/a', 'wp-job-manager-listing-payments' );
			case 'user_id' :
				$user = get_user_by( 'id', $item->user_id );

				if ( $item->user_id && $user ) {
					return '<a href="' . admin_url( 'user-edit.php?user_id=' . absint( $item->user_id ) ) . '">' . esc_attr( $user->display_name ) . '</a><br/><span class="description">' . esc_html( $user->user_email ) . '</span>';
				} else {
					return __( 'n/a', 'wp-job-manager-listing-payments' );
				}
			case 'order_id' :
				return $item->order_id > 0 ? '<a href="' . admin_url( 'post.php?post=' . absint( $item->order_id ) . '&action=edit' ) . '">#' . absint( $item->order_id ) . ' &rarr;</a>' : __( 'n/a', 'wp-job-manager-listing-payments' );
			case 'featured_job' :
				return $item->package_featured ? '&#10004;' : '&ndash;';
			case 'duration' :
				// Translators: %d is package duration.
				return $item->package_duration ? sprintf( __( '%d Days', 'wp-job-manager-listing-payments' ), absint( $item->package_duration ) ) : '&ndash;';
			case 'limit' :
				// Translators: %s is package count.
				$package_count = $item->package_count ? sprintf( __( '%s Posted', 'wp-job-manager-listing-payments' ), absint( $item->package_count ) ) : '';
				$package_limit = $item->package_limit ? absint( $item->package_limit ) : __( 'Unlimited', 'wp-job-manager-listing-payments' );
				return '<a href="' . esc_url( admin_url( 'edit.php?post_type=' . ( 'resume' === $item->package_type ? 'resume' : 'job_listing' ) . '&package=' . absint( $item->id ) ) ) . '">' . ( $package_count ? $package_count . ' / ' . $package_limit : $package_limit ) . '</a>';
			case 'package_type' :
				return 'resume' === $item->package_type ? __( 'Resume Payment Package', 'wp-job-manager-listing-payments' ) : __( 'Listing Payment Package', 'wp-job-manager-listing-payments' );
			case 'job_actions' :
				return '<div class="actions">
					<a class="button button-icon icon-edit" href="' . esc_url( add_query_arg( array( 'action' => 'edit', 'package_id' => $item->id ), admin_url( 'users.php?page=wc_paid_listings_packages' ) ) ) . '">' . __( 'Edit', 'wp-job-manager-listing-payments' ) . '</a>
					<a class="button button-icon icon-delete" href="' . wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'package_id' => $item->id ), admin_url( 'users.php?page=wc_paid_listings_packages' ) ), 'delete', 'delete_nonce' ) . '">' . __( 'Delete', 'wp-job-manager-listing-payments' ) . '</a></div>
				</div>';
		}
	}

	/**
	 * Get Columns function.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'           => '<input type="checkbox" />',
			'user_id'      => __( 'User', 'wp-job-manager-listing-payments' ),
			'package_type' => __( 'Type', 'wp-job-manager-listing-payments' ),
			'product_id'   => __( 'Product', 'wp-job-manager-listing-payments' ),
			'limit'        => __( 'Limit', 'wp-job-manager-listing-payments' ),
			'duration'     => __( 'Duration', 'wp-job-manager-listing-payments' ),
			'featured_job' => '<span class="tips" data-tip="' . __( 'Featured?', 'wp-job-manager-listing-payments' ) . '">' . __( 'Featured?', 'wp-job-manager-listing-payments' ) . '</span>',
			'order_id'     => __( 'Order ID', 'wp-job-manager-listing-payments' ),
			'id'           => __( 'ID', 'wp-job-manager-listing-payments' ),
			'job_actions'  => __( 'Actions', 'wp-job-manager-listing-payments' ),
		);
		return $columns;
	}

	/**
	 * Get sortable columns function.
	 *
	 * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'id'           => array( 'id', false ),
			'order_id'     => array( 'order_id', false ),
			'user_id'      => array( 'user_id', true ),
			'product_id'   => array( 'product_id', false ),
			'package_type' => array( 'package_type', false ),
		);
		return $sortable_columns;
	}

	/**
	 * Specify the list of bulk actions.
	 *
	 * @since 2.0.0
	 *
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'.
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete', 'wp-job-manager-listing-payments' ),
		);
		return $actions;
	}

	/**
	 * Prepare Items function.
	 *
	 * @since 2.0.0
	 */
	public function prepare_items() {
		global $wpdb;

		$current_page          = $this->get_pagenum();
		$per_page              = 50;
		$orderby               = ! empty( $_REQUEST['orderby'] ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'user_id';
		$order                 = empty( $_REQUEST['order'] ) || 'asc' === $_REQUEST['order'] ? 'ASC' : 'DESC';
		$order_id              = ! empty( $_REQUEST['order_id'] ) ? absint( $_REQUEST['order_id'] ) : '';
		$user_id               = ! empty( $_REQUEST['user_id'] ) ? absint( $_REQUEST['user_id'] ) : '';
		$product_id            = ! empty( $_REQUEST['product_id'] ) ? absint( $_REQUEST['product_id'] ) : '';
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$where                 = array( 'WHERE 1=1' );

		if ( $order_id ) {
			$where[] = 'AND order_id=' . $order_id;
		}
		if ( $user_id ) {
			$where[] = 'AND user_id=' . $user_id;
		}
		if ( $product_id ) {
			$where[] = 'AND product_id=' . $product_id;
		}

		$where       = implode( ' ', $where );
		$max         = $wpdb->get_var( "SELECT COUNT(id) FROM {$wpdb->prefix}wcpl_user_packages $where;" );
		$this->items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wcpl_user_packages $where ORDER BY `{$orderby}` {$order} LIMIT %d, %d", ( $current_page - 1 ) * $per_page, $per_page ) );

		$this->set_pagination_args( array(
			'total_items' => $max,
			'per_page'    => $per_page,
			'total_pages' => ceil( $max / $per_page ),
		) );
	}
}
