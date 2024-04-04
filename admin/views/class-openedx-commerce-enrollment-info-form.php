<?php
/**
 * Openedx plugin admin enrollment info form
 *
 * @category   Views
 * @package    WordPress
 * @subpackage Openedx_Commerce
 * @since      1.0.0
 */

namespace OpenedX_Commerce\admin\views;

use OpenedX_Commerce\model\Openedx_Commerce_Log;
use OpenedX_Commerce\utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * The Enrollment Info Form code for the form.
 */
class Openedx_Commerce_Enrollment_Info_Form {

	/**
	 * The name for the Open edX enrollment custom post type.
	 *
	 * @var     string
	 * @access  public
	 * @since   1.9.0
	 */
	public $post_type = 'openedx_enrollment';

	/**
	 * The log manager.
	 *
	 * @var    Openedx_Commerce_Log
	 * @access private
	 * @since  1.1.1
	 */
	private $log_manager;

	/**
	 * Constructor function.
	 *
	 * @param Openedx_Commerce_Enrollment $enrollment_request The enrollment request object.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct( $enrollment_request ) {
		$this->render_enrollment_info_form( $enrollment_request );
		$this->replace_admin_meta_boxes();
		$this->register_log_manager();
	}

	/**
	 * Register log manager
	 *
	 * @return void
	 */
	public function register_log_manager() {
		$this->log_manager = new Openedx_Commerce_Log();
	}

	/**
	 * Print openedx enrollment edit metabox
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_enrollment_info_form( $post ) {

		if ( $this->post_type !== $post->post_type ) {
			return;
		}

		$post_id = $post->ID;

		$course_id = get_post_meta( $post_id, 'course_id', true );
		$email     = get_post_meta( $post_id, 'email', true );
		$mode      = get_post_meta( $post_id, 'mode', true );
		$is_active = get_post_meta( $post_id, 'is_active', true );
		$order_id  = get_post_meta( $post_id, 'order_id', true );
		if ( $order_id ) {
			$order_url = admin_url( 'post.php?post=' . intval( $order_id ) ) . '&action=edit';
		}
		$mode_options = utils\get_enrollment_options();

		$openedx_new_enrollment = false;
		if ( ! $course_id && ! $email ) {
			$openedx_new_enrollment = true;
		}

		wp_nonce_field( 'openedx_commerce_enrollment_form', 'openedx_commerce_enrollment_form_nonce' );

		?>
		<div id="namediv" class="postbox">
			
			<fieldset>
				<h2 class="">Open edX enrollment request</h2>
				<input type="hidden" name="openedx_new_enrollment" value="<?php echo wp_kses( $openedx_new_enrollment, array( 'true', 'false' ) ); ?>">
				<table class="form-table">
					<tbody>
						<tr>
							<td class="first"><label for="openedx_enrollment_course_id">Course ID</label></td>
							<td>
								<input type="text" id="openedx_enrollment_course_id" name="openedx_enrollment_course_id" value="<?php echo esc_attr( $course_id ); ?>"
								<?php
								if ( '' !== $course_id ) {
									?>
									readonly
									<?php
								}
								?>
								>
								<span class="openedx-tooltip-icon">?</span>
								<span class="openedx-tooltip-text"><?php esc_html_e( 'The id of the course to be used for the enroll, e.g. course-v1:edX+DemoX+Demo_Course.', 'openedx-commerce' ); ?></span>
							</td>
						</tr>
						<tr>
							<td class="first"><label>User Email</label></td>
							<td>
								<div style="width: 20%; display: inline-table;">
									<input type="email" id="openedx_enrollment_email" name="openedx_enrollment_email" value="<?php echo esc_attr( $email ); ?>"> 
								</div>
								<span class="openedx-tooltip-icon">?</span>
								<span class="openedx-tooltip-text"><?php esc_html_e( 'The email of the user to be used for the enroll.', 'openedx-commerce' ); ?></span>
								<button name="enrollment_sync" class="button save_order button-secondary sync_button"><span><?php esc_html_e( 'Read from Open edX', 'openedx-commerce' ); ?></span></button>
							</td>
						</tr>
						<tr class="gray_zone first_zone">
							<td class="first"><label for="openedx_enrollment_mode">Course Mode</label></td>
							<td>
								<select id="openedx_enrollment_mode" name="openedx_enrollment_mode">
									<?php foreach ( $mode_options as $value => $label ) : ?>
										<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $mode, $value ); ?>>
											<?php echo esc_html( $label ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<span class="openedx-tooltip-icon">?</span>
								<span class="openedx-tooltip-text"><?php esc_html_e( 'The mode of your enrollment request. Make sure to set a mode that your course has.', 'openedx-commerce' ); ?></span>
							</td>
						</tr>

						<tr class="gray_zone">
							<td class="first">
								<label for="openedx_enrollment_is_active">Request Type</label>
							</td>
							<td>

								<select id="openedx_enrollment_is_active" name="openedx_enrollment_request_type">
									<option value="enroll" 
									<?php
									if ( $is_active || $openedx_new_enrollment ) {
										echo ( 'selected="selected"' );
									}
									?>
									>
									<?php
									esc_html_e( 'Enroll', 'openedx-commerce' );
									?>
									</option>
									<option value="unenroll" 
									<?php
									if ( ! $is_active && ! $openedx_new_enrollment ) {
										echo ( 'selected="selected"' );
									}
									?>
									>
									<?php
									esc_html_e( 'Un-enroll', 'openedx-commerce' );
									?>
									</option>
								</select>
								<span class="openedx-tooltip-icon">?</span>
								<span class="openedx-tooltip-text"><?php esc_html_e( 'The type of your request. If you select Enroll, you will create an enrollment, and if you select Un-enroll, you will set a soft unenrollment (enrollment with status inactive).', 'openedx-commerce' ); ?></span>
							</td>
						</tr>
						<tr class="gray_zone">
							<td class="first"><label for="openedx_enrollment_order_id">WC Order ID</label></td>
							<td>
								<div style="width: 18.5%; display: inline-table;">
									<input type="text" id="openedx_enrollment_order_id" name="openedx_enrollment_order_id" value="<?php echo esc_attr( $order_id ); ?>" pattern="\d*" />
								</div>
								<div style="width: 6%; display: inline-table;">
									<?php
									if ( isset( $order_url ) ) {
										echo '<a href="' . esc_url( $order_url ) . '" class="button view_order_button" style="' . ( empty( $order_id ) ? 'pointer-events: none; opacity: 0.6;' : '' ) . '">View Order</a>';
									} else {
										echo '<a href="" class="button view_order_button" style="pointer-events: none; opacity: 0.6;">View Order</a>';
									}
									?>
								</div>
								<span class="openedx-tooltip-icon">?</span>
								<span class="openedx-tooltip-text"><?php esc_html_e( 'The id of the order associated with this request.', 'openedx-commerce' ); ?></span>
							</td>
						</tr>

						<tr class="gray_zone">
							<td class="checkbox-td">		
								<input class="action-checkbox" type="checkbox" id="openedx_enrollment_force" name="openedx_enrollment_force" value="openedx_force">
								<label for="openedx_enrollment_force"><?php esc_html_e( 'Use the "force" flag', 'openedx-commerce' ); ?></label>
								<span class="openedx-tooltip-icon">?</span>
								<span class="openedx-tooltip-text"><?php esc_html_e( "Disregard the course's enrollment end dates.", 'openedx-commerce' ); ?></span>
							</td>
							<td>
								<input class="action-checkbox" type="checkbox" id="openedx_enrollment_allowed" name="openedx_enrollment_allowed" value="openedx_allowed">
								<label for="openedx_enrollment_allowed"><?php esc_html_e( "Create course enrollment allowed if the user doesn't exist", 'openedx-commerce' ); ?></label>
								<span class="openedx-tooltip-icon">?</span>
								<span class="openedx-tooltip-text"><?php esc_html_e( 'Creates a register in the table Course Enrollment Allowed if the email we use in the request is not a user in our Open edX platform yet.', 'openedx-commerce' ); ?></span>
							</td>
						</tr>

						<tr class="gray_zone">
							<td class="first">
								<button name="enrollment_process" class="button save_order button-primary"><span><?php esc_html_e( 'Save and update Open edX', 'openedx-commerce' ); ?></span></button>
							</td>
							<td>
								<button name="save_no_process" class="button save_order button-secondary"><span><?php esc_html_e( 'Save in WordPress', 'openedx-commerce' ); ?></span></button>
							</td>
						</tr>
					</tbody>
				</table>
			</fieldset>
		</div>

		<script>
			
		</script>

		<?php
	}


	/**
	 * Render logs box
	 *
	 * @param WP_Post $post Current post object.
	 */
	public function render_logs_box( $post ) {
		$post_id = $post->ID;
		$logs    = $this->log_manager->get_logs( $post_id );
		?>

		<style>

		</style>
		<div class="openedx_logs_box">
			<?php
			echo wp_kses(
				$logs,
				array(
					'div'    => array(
						'class' => array(),
					),
					'strong' => array(),
					'br'     => array(),
				)
			);
			?>
		</div>

		<?php
	}

	/**
	 * Replace admin meta boxes
	 *
	 * @return void
	 */
	public function replace_admin_meta_boxes() {
		remove_meta_box( 'submitdiv', $this->post_type, 'side' );
		add_meta_box( 'openedx_enrollment_request_actions', 'Enrollment Operation Logs', array( $this, 'render_logs_box' ), $this->post_type, 'normal', 'high' );
	}
}
