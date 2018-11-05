<?php
/**
 * Generic helper shortcodes.
 *
 * @class Private_Messages_Shortcodes
 * @author Astoundify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Shortcodes.
 * Register shortcode other than PM dashboard shortcode.
 *
 * @since 1.0.0
 */
class Private_Messages_Shortcodes {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		// Link to new messages.
		add_shortcode( 'private_message', array( $this, 'private_message_link' ) );

		// Compose Message Form Shortcode.
		add_shortcode( 'private_message_compose', array( $this, 'private_message_compose' ) );

		// Register Script.
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );

		// AJAX Compose Message.
		add_action( 'wp_ajax_pm_compose_shortcode', array( $this, 'compose_ajax_callback' ) );

		// AJAX Get users.
		add_action( 'wp_ajax_pm_recipients_list', array( $this, 'recipients_list' ) );
	}

	/**
	 * Create an HTML link to compose a new private message.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Shortcode atts.
	 * @return string
	 */
	public function private_message_link( $atts ) {
		$atts = shortcode_atts( array(
			'to'      => is_author() ? get_queried_object_id() : null,
			'subject' => null,
			'message' => null,
			'title'   => __( 'Send a Message', 'private-messages' ),
		), $atts, 'private-message' );

		return sprintf(
			'<a href="%s" class="private-message-link button button--private-message-link">%s</a>',
			pm_get_new_message_url( $atts['to'], $atts['subject'], $atts['message'] ),
			esc_attr( $atts['title'] )
		);
	}

	/**
	 * Shortocode to display Private Message compose form
	 *
	 * Shortcode Options:
	 * "to"      : int User ID to send messages. if set, user cannot change the "to" field.
	 * "subject" : Default subject, user can change it.
	 * "message" : Default message, user can change it.
	 *
	 * @since 1.4.0
	 *
	 * @param array $atts Shortcode atts.
	 * @return string
	 */
	public function private_message_compose( $atts ) {
		$attr = shortcode_atts( array(
			'to'      => is_author() ? get_queried_object_id() : null,
			'subject' => null,
			'message' => null,
		), $atts, 'private_message_compose' );

		// Start Output Buffering.
		ob_start();

		// User not logged in, show login link.
		if ( ! is_user_logged_in() ) {
			echo Private_Messages_Templates::get_template( 'frontend/dashboard-logged-out.php' );
			return ob_get_clean();
		}

		// Recipient is set via shortcode option.
		if ( $attr['to'] ) {
			$user_data = $attr['to'] ? get_user_by( 'ID', $attr['to'] ) : false;

			// Recipient User not found.
			if ( ! $user_data ) {
				echo wpautop( __( 'Invalid recipient. Please check your shortcode.', 'private-messages' ) );
				return ob_get_clean();
			}

			// Recipient is current user.
			if ( get_current_user_id() == $attr['to'] ) {
				echo wpautop( __( 'You cannot send message to yourself.', 'private-messages' ) );
				return ob_get_clean();
			}
		}

		// Load Scripts.
		wp_enqueue_style( 'select2' );
		wp_enqueue_style( 'private-messages-frontend' );
		wp_enqueue_script( 'private-messages-compose-shortcode' );

		// Include template.
		echo Private_Messages_Templates::get_template( 'frontend/dashboard-compose-message.php', array(
			'disable_to'  => $attr['to'] ? true : false,
			'recipient'   => $attr['to'] ? $attr['to'] : pm_get_posted_field( 'pm_recipient' ),
			'subject'     => $attr['subject'] ? $attr['subject'] : pm_get_posted_field( 'pm_subject' ),
			'message'     => $attr['message'] ? $attr['message'] : pm_get_posted_field( 'pm_message' ),
		) );

		return ob_get_clean();
	}

	/**
	 * Register Scripts.
	 *
	 * @since 1.4.0
	 */
	public function scripts() {
		$pm = private_messages();
		wp_register_script( 'private-messages-compose-shortcode', $pm->plugin_url . '/assets/js/compose-shortcode.js', array( 'wp-util', 'jquery', 'select2', 'private-messages-frontend' ), PM_VERSION, true );
	}

	/**
	 * Compose Ajax Callback to Process Form Submission
	 *
	 * @since 1.4.0
	 */
	public function compose_ajax_callback() {
		if ( ! isset( $_REQUEST['fields'] ) ) {
			$data['notice'] = __( 'Invalid Request.', 'private-messages' );
			wp_send_json_error( $_FILES );
		}

		// Parse form data.
		parse_str( $_REQUEST['fields'] , $request );

		// Return Data */
		$data = array(
			'notice'    => false,
			'redirect'  => false,
		);

		// Validate Request.
		if ( ! pm_can_compose_from_dashboard() && ! wp_verify_nonce( $request['pm_message_nonce'], 'pm_message_nonce' ) ) {
			$data['notice'] = __( 'You do not have permission to create a new message.', 'private-messages' );
			wp_send_json_error( $data );
		}

		// Form Data */
		$subject      = $_REQUEST['pm_subject'];
		$message      = $_REQUEST['pm_message'];
		$recipient_id = $_REQUEST['pm_recipient'];

		// Required Fields Notice.
		if ( ! $subject || ! $message || ! $recipient_id ) {
			$data['notice'] = __( 'All fields are required.', 'private-messages' );
			wp_send_json_error( $data );
		}

		// Check recipient user.
		$recipient = get_user_by( 'ID', $recipient_id );
		if ( ! $recipient ) {
			$data['notice'] = __( 'Invalid recipient.', 'private-messages' );
			wp_send_json_error( $data );
		}

		// Create New Thread.
		$thread_id = Private_Messages_Dashboard::compose_message( array(
			'recipient' => $recipient,
			'subject'   => $subject,
			'message'   => $message,
		) );

		// Sucessfully create PM.
		if ( $thread_id ) {
			$view_message_url = add_query_arg( array(
				'pm-action'    => 'view_message',
				'view_message' => $thread_id,
				'new_message'  => false,
			), pm_get_permalink( 'dashboard' ) );
			$data['redirect'] = esc_url_raw( $view_message_url );
			wp_send_json_success( $data );
		} else { // Fail create PM entry.
			$data['notice'] = __( 'Message thread could not be created.', 'private-messages' );
			wp_send_json_error( $data );
		}
	}

	/**
	 * Create a list of recipients based on a search parameter.
	 *
	 * @since 1.3.0
	 *
	 * @return array $recipients
	 */
	public function recipients_list() {
		$output = array(
			'total_count' => 0,
			'recipients' => array(),
		);

		if ( ! empty( $_GET['q'] ) ) {
			$q = esc_attr( $_GET['q'] );

			$users_found = $this->search_for_users( $q );
			$current_user_id = get_current_user_id();

			if ( ! empty( $users_found ) ) {
				foreach ( $users_found as $user ) {
					if ( $current_user_id !== $user->ID ) {
						$output['recipients'][] = array(
							'id'         => $user->ID,
							'avatar_url' => get_avatar_url( $user->ID, array(
								'size' => 45,
							) ),
							'name'       => pm_get_user_display_name( $user ),
							'username'   => $user->user_login,
							'user'       => $user,
						);
					}
				}
			}
		}

		echo wp_json_encode( $output );
		die;
	}

	/**
	 * Search for users based on a query string.
	 *
	 * Currently searches:
	 *
	 * @todo Use a direct query to avoid 3 separate queries.
	 *
	 * @since 1.3.0
	 *
	 * @param string $q
	 * @return array $users_foudn
	 */
	public function search_for_users( $q ) {
		// search meta first
		$meta_search = new WP_User_Query( apply_filters( 'private_messages_recipient_list_search_user_meta', array(
			'fields' => 'ID',
			'meta_query' => array(
				'relation'    => 'OR',
				array(
					'key'     => 'nickname',
					'value'   => $q,
					'compare' => 'LIKE',
				),
				array(
					'key'     => 'first_name',
					'value'   => $q,
					'compare' => 'LIKE',
				),
				array(
					'key' => 'last_name',
					'value' => $q,
					'compare' => 'LIKE',
				)
			),
			'exclude' => array( get_current_user_id() ),
		) ) );
		// search main table
		$main_search = new WP_User_Query( apply_filters( 'private_messages_recipient_list_search_users', array(
			'fields' => 'ID',
			'search' => '*' . $q . '*',
			'search_columns' => array(
				'user_login',
				'user_nicename',
				'user_email',
				'user_url',
			),
			'exclude' => array( get_current_user_id() ),
		) ) );
		$users = new WP_User_Query( apply_filters( 'private_messages_recipient_list_search', array(
			'include' => array_merge( $main_search->get_results(), $meta_search->get_results() ),
		) ) );
		$users_found = $users->get_results();
		return $users_found;
	}

}

// Load class.
new Private_Messages_Shortcodes();
