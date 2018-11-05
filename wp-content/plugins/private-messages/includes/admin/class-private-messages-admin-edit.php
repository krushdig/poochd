<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Handles PM Edit Screen.
 *
 * @since 1.0.0
 *
 * @category Class
 * @author   Astoundify
 */
class Private_Messages_Admin_Edit {

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Post action.
		add_filter( 'post_row_actions', array( $this, 'post_row_actions' ), 10, 2 );

		// Columns.
		add_filter( 'manage_private-messages_posts_columns',  array( $this, 'manage_posts_columns' ) );
		add_action( 'manage_private-messages_posts_custom_column',  array( $this, 'manage_posts_custom_column' ), 5, 2 );

		// Add meta boxes.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		// Admin scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 5 );
	}

	/**
	 * Post Row Actions
	 *
	 * @since 1.10.0
	 */
	public function post_row_actions( $actions, $post ) {
		if ( 'private-messages' === $post->post_type ) {
			unset( $actions['inline hide-if-no-js'] );
			unset( $actions['view'] );
		}
		return $actions;
	}

	/**
	 * Manage Post Columns.
	 *
	 * @since 1.10.0
	 *
	 * @param array $columns PM Post Columns.
	 * @return array
	 */
	public function manage_posts_columns( $columns ) {
		$new_columns = array(
			'title'    => $columns['title'],
			'author'   => $columns['author'],
			'recipient' => esc_html__( 'Recipient', 'private-messages' ),
			'date'     => $columns['date'],
		);
		return $new_columns;
	}

	/**
	 * Manage Post Custom Column
	 *
	 * @since 1.10.0
	 *
	 * @param string $column  Column ID.
	 * @param int    $post_id Post ID.
	 */
	public function manage_posts_custom_column( $column, $post_id ) {
		switch ( $column ) {
			case 'recipient':
				$recipient_id = get_post_meta( $post_id, '_pm_recipient', true );
				$recipient = $recipient_id ? get_userdata( $recipient_id ) : false;
				if ( $recipient ) {
					$name = pm_get_user_display_name( $recipient );
					if ( current_user_can( 'edit_user', $recipient_id ) ) {
						echo '<a href="' . esc_url( get_edit_user_link( $recipient_id ) ) . '">' . $name . '</a>';
					} else {
						echo $name;
					}
				}
			break;
		}
	}

	/**
	 * Add Meta Boxes.
	 *
	 * @since 1.0.0
	 */
	public function add_meta_boxes() {
		global $pagenow;

		// Remove meta boxes.
		remove_meta_box( 'submitdiv', 'private-messages', 'side' ); // Submit/Update.
		remove_meta_box( 'authordiv', 'private-messages', 'normal' ); // Author.
		remove_meta_box( 'commentsdiv', 'private-messages', 'normal' ); // Comment meta box.
		remove_meta_box( 'commentstatusdiv', 'private-messages', 'normal' ); // Discussion meta box.

		// PM Recipient Meta Box.
		add_meta_box(
			$id       = 'pm_info',
			$title    = __( 'Info', 'private-messages' ),
			$callback = array( $this, 'meta_box_pm_info' ),
			$screen   = 'private-messages',
			$context  = 'side',
			$priority = 'high'
		);

		// PM Messages Meta Box: Only in new post.
		if ( $pagenow !== 'post-new.php' ) {

			add_meta_box(
				$id       = 'pm_messages',
				$title    = __( 'Messages', 'private-messages' ),
				$callback = array( $this, 'meta_box_pm_messages' ),
				$screen   = 'private-messages',
				$context  = 'normal',
				$priority = 'default'
			);
		}
	}

	/**
	 * Recipient Meta Box.
	 *
	 * @since 1.0.0
	 *
	 * @param object $post WP Post.
	 */
	public function meta_box_pm_info( $post ) {
		$author = $post->post_author ? get_userdata( $post->post_author ) : false;
		$recipient = $post->_pm_recipient ? get_userdata( $post->_pm_recipient ) : false
		?>

		<p><strong><?php esc_html_e( 'Author:', 'private-messages' ); ?></strong></p>
		<?php if ( $author ) : ?>
			<p><?php echo pm_get_user_display_name( $author ); ?> <?php echo $author->user_email ? "({$author->user_email})" : '';?></p>
		<?php else : ?>
			<p><?php esc_html_e( 'No author found.', 'private-messages' ); ?></p>
		<?php endif; ?>

		<p><strong><?php esc_html_e( 'Recipient:', 'private-messages' ); ?></strong></p>
		<?php if ( $recipient ) : ?>
			<p><?php echo pm_get_user_display_name( $recipient ); ?> <?php echo $recipient->user_email ? "({$recipient->user_email})" : '';?></p>
		<?php else : ?>
			<p><?php esc_html_e( 'No recipient found.', 'private-messages' ); ?></p>
		<?php endif; ?>

		<?php
	}

	/**
	 * PM Messages Meta Box.
	 *
	 * @since 1.0.0
	 *
	 * @param object $post WP Post.
	 */
	public function meta_box_pm_messages( $post ) {
		$messages = get_comments( array(
			'post_id'  => $post->ID,
		) );
		?>

		<?php if ( empty( $messages ) ) : ?>

			<h2><?php _e( 'There are currently no messages in this conversation.', 'private-messages' ); ?></h2>

		<?php else : ?>

			<table class="form-table">
				<tbody>

					<?php foreach ( $messages as $message ) : ?>
						<tr>
							<th scope="row">
								<?php echo $message->comment_author; ?><br>
								<span class="description">
									<?php echo date_i18n( get_option( 'date_format' ), strtotime( $message->comment_date ) ); ?><br>
									<?php echo date_i18n( get_option( 'time_format' ), strtotime( $message->comment_date ) ); ?>
								</span>
							</th>
							<td valign="top">
								<?php echo apply_filters( 'the_content', $message->comment_content ); ?>
								<?php pm_message_attachments_html( pm_get_message( $message ) ); ?>
							</td>
						</tr>
					<?php endforeach; ?>

				</tbody>
			</table>
			
		<?php endif; ?>

		<?php
	}

	/**
	 * Enqueue script to post edit screen function.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		// Only load scripts in PM edit post screen.
		global $post_type;
		if ( 'edit.php' !== $hook_suffix || 'private-messages' !== $post_type ) {
			return;
		}

		// Get assets URL.
		$pm  = private_messages();
		$url = $pm->plugin_url;
		$ver = $pm->version;

		// CSS.
		wp_enqueue_style( 'private-messages-admin-edit', $url . '/assets/css/admin-edit.css', array(), $ver );
	}

}

new Private_Messages_Admin_Edit();
