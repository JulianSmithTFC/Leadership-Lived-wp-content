<?php
/**
 * Constant Contact Settings class.
 *
 * @package ConstantContact
 * @subpackage Settings
 * @author Constant Contact
 * @since 1.0.0
 *
 * phpcs:disable WebDevStudios.All.RequireAuthor -- Don't require author tag in docblocks.
 */

/**
 * Powers our settings and options page, as well as injecting our optins to the front-end.
 *
 * @since 1.0.0
 */
class ConstantContact_Settings {

	/**
	 * Option key, and option page slug.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $key = 'ctct_options_settings';

	/**
	 * Settings page metabox id.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $metabox_id = 'ctct_option_metabox_settings';

	/**
	 * Settings options page.
	 *
	 * @var string
	 */
	private $options_page;

	/**
	 * Parent plugin class.
	 *
	 * @since 1.0.0
	 * @var object
	 */
	protected $plugin;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param object $plugin Parent plugin instance.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks() {

		add_action( 'cmb2_admin_init', [ $this, 'add_options_page_metabox' ] );

		add_filter( 'cmb2_override_option_get_' . $this->key, [ $this, 'get_override' ], 10, 2 );

		add_filter( 'cmb2_override_option_save_' . $this->key, [ $this, 'update_override' ], 10, 2 );

		$this->inject_optin_form_hooks();

		add_filter( 'preprocess_comment', [ $this, 'process_optin_comment_form' ] );
		add_filter( 'authenticate', [ $this, 'process_optin_login_form' ], 10, 3 );

		add_action( 'cmb2_save_field__ctct_logging', [ $this, 'maybe_init_logs' ], 10, 2 );
		add_filter( 'ctct_custom_spam_message', [ $this, 'get_spam_error_message' ], 10, 2 );
	}

	/**
	 * Hook in all our form opt-in injects, decide to show or not when we are at the display point.
	 *
	 * @since 1.0.0
	 */
	public function inject_optin_form_hooks() {

		add_action( 'login_form', [ $this, 'optin_form_field_login' ] );
		add_action( 'comment_form', [ $this, 'optin_form_field_comment' ] );

		add_action( 'register_form', [ $this, 'optin_form_field_registration' ] );
		add_action( 'signup_extra_fields', [ $this, 'optin_form_field_registration' ] );
		add_action( 'login_head', [ $this, 'optin_form_field_login_css' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'scripts' ] );

		if ( ! $this->privacy_policy_status() ) {
			add_action( 'admin_footer', [ $this, 'privacy_notice_markup' ] );
		}
	}

	/**
	 * Add some login page CSS.
	 *
	 * @since 1.2.0
	 */
	public function optin_form_field_login_css() {
		?>
		<style>
		.login .ctct-disclosure {
			margin: 0 0 15px;
		}
		</style>
		<?php
	}

	/**
	 * Enqueue our styles.
	 *
	 * @since 1.0.0
	 */
	public function scripts() {
		wp_enqueue_style( 'constant-contact-forms-admin' );
	}

	/**
	 * Add menu options page.
	 *
	 * @since 1.0.0
	 */
	public function add_options_page() {

		$this->options_page = add_submenu_page(
			'edit.php?post_type=ctct_forms',
			esc_html__( 'Constant Contact Forms Settings', 'constant-contact-forms' ),
			esc_html__( 'Settings', 'constant-contact-forms' ),
			'manage_options',
			$this->key,
			[ $this, 'admin_page_display' ]
		);

		// Include CMB CSS in the head to avoid FOUC.
		add_action( "admin_print_styles-{$this->options_page}", [ 'CMB2_hookup', 'enqueue_cmb_css' ] );
	}

	/**
	 * Admin page markup. Mostly handled by CMB2.
	 *
	 * @since 1.0.0
	 */
	public function admin_page_display() {
		?>
		<div class="wrap cmb2-options-page <?php echo esc_attr( $this->key ); ?>">
			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			<?php
			if ( function_exists( 'cmb2_metabox_form' ) ) {
				cmb2_metabox_form( $this->metabox_id, $this->key );
			}

			$this->plugin->check->maybe_display_debug_info();
			?>
		</div>
		<?php
	}

	/**
	 * Are we on the settings page?
	 *
	 * @since 1.0.0
	 *
	 * @return boolean If we are on the settings page or not.
	 */
	public function on_settings_page() {

		global $pagenow;

		return ( 'edit.php' === $pagenow && isset( $_GET['page'] ) && 'ctct_options_settings' === $_GET['page'] ); // Input var okay.
	}

	/**
	 * Add the options metabox to the array of metaboxes.
	 *
	 * @since 1.0.0
	 */
	public function add_options_page_metabox() {

		add_action( "cmb2_save_options-page_fields_{$this->metabox_id}", [ $this, 'settings_notices' ], 10, 2 );

		$cmb = new_cmb2_box( [
			'id'           => $this->metabox_id,
			'title'        => esc_html__( 'Constant Contact Forms Settings', 'constant-contact-forms' ),
			'object_types' => [ 'options-page' ],
			'option_key'   => 'ctct_options_settings',
			'menu_title'   => esc_html__( 'Settings', 'constant-contact-forms' ),
			'parent_slug'  => 'edit.php?post_type=ctct_forms',
		] );

		$this->do_lists_field( $cmb );
	}

	/**
	 * Helper to show our lists field for settings.
	 *
	 * @since 1.0.0
	 *
	 * @param object $cmb CMB fields object.
	 */
	public function do_lists_field( $cmb ) {

		$cmb->add_field( [
			'name' => esc_html__( 'Google Analytics&trade; tracking opt-in.', 'constant-contact-forms' ),
			'id'   => '_ctct_data_tracking',
			'type' => 'checkbox',
			'desc' => __( 'Allow Constant Contact to use Google Analytics&trade; to track your usage across the Constant Contact Forms plugin.<br/> NOTE &mdash; Your website and users will not be tracked. See our <a href="https://www.endurance.com/privacy"> Privacy Statement</a> information about what is and is not tracked.', 'constant-contact-forms' ),
		] );

		if ( constant_contact()->api->is_connected() ) {

			$cmb->add_field( [
				'name'       => esc_html__( 'Disable E-mail Notifications', 'constant-contact-forms' ),
				'desc'       => sprintf(
					/* Translators: Placeholder is for a <br /> HTML tag. */
					esc_html__( 'This option will disable e-mail notifications for forms with a selected list and successfully submit to Constant Contact.%s Notifications are sent to the email address listed under Wordpress "General Settings".', 'constant-contact-forms' ), '<br/>'
				),
				'id'         => '_ctct_disable_email_notifications',
				'type'       => 'checkbox',
				'before_row' => '<hr/>',
			] );

			$cmb->add_field( [
				'name'       => esc_html__( 'Bypass Constant Contact cron scheduling', 'constant-contact-forms' ),
				'desc'       => esc_html__( 'This option will send form entries to Constant Contact right away instead of holding for one minute delay.', 'constant-contact-forms' ),
				'id'         => '_ctct_bypass_cron',
				'type'       => 'checkbox',
				'before_row' => '<hr/>',
			] );

			$lists = constant_contact()->builder->get_lists();

			if ( $lists && is_array( $lists ) ) {

				$before_optin = sprintf(
					'<hr/><h2>%s</h2>',
					esc_html__( 'Advanced Opt-in', 'constant-contact-forms' )
				);

				$cmb->add_field( [
					'name'       => esc_html__( 'Opt-in Location', 'constant-contact-forms' ),
					'id'         => '_ctct_optin_forms',
					'type'       => 'multicheck',
					'options'    => $this->get_optin_show_options(),
					'before_row' => $before_optin,
				] );

				$lists[0] = esc_html__( 'Select a list', 'constant-contact-forms' );

				$cmb->add_field( [
					'name'             => esc_html__( 'Add subscribers to', 'constant-contact-forms' ),
					'id'               => '_ctct_optin_list',
					'type'             => 'select',
					'show_option_none' => false,
					'default'          => esc_html__( 'Select a list', 'constant-contact-forms' ),
					'options'          => $lists,
				] );

				$business_name = get_bloginfo( 'name' ) ?: esc_html__( 'Business Name', 'constant-contact-forms' );
				$business_addr = '';

				$disclosure_info = $this->plugin->api->get_disclosure_info( true );
				if ( ! empty( $disclosure_info ) ) {
					$business_name = $disclosure_info['name'] ?: $business_name;
					$business_addr = isset( $disclosure_info['address'] ) ?: '';
				}

				$cmb->add_field( [
					'name'    => esc_html__( 'Opt-in Affirmation', 'constant-contact-forms' ),
					'id'      => '_ctct_optin_label',
					'type'    => 'text',
					// translators: placeholder will hold site owner's business name.
					'default' => sprintf( esc_html__( 'Yes, I would like to receive emails from %s. Sign me up!', 'constant-contact-forms' ), $business_name ),
				] );

				if ( empty( $disclosure_info ) ) {
					$cmb->add_field( [
						'name'       => esc_html__( 'Disclosure Name', 'constant-contact-forms' ),
						'id'         => '_ctct_disclose_name',
						'type'       => 'text',
						'default'    => $business_name,
						'attributes' => ! empty( $business_name ) ? [ 'readonly' => 'readonly' ] : [],
					] );

					$cmb->add_field( [
						'name'       => esc_html__( 'Disclosure Address', 'constant-contact-forms' ),
						'id'         => '_ctct_disclose_address',
						'type'       => 'text',
						'default'    => $business_addr,
						'attributes' => ! empty( $business_addr ) ? [ 'readonly' => 'readonly' ] : [],
					] );
				}
			}
		}

		$before_recaptcha = sprintf(
			'<hr/><h2>%s</h2>%s',
			esc_html__( 'Google reCAPTCHA', 'constant-contact-forms' ),
			'<div class="discover-recaptcha">' . __( 'Learn more and get an <a href="https://www.google.com/recaptcha/intro/" target="_blank">API site key</a>', 'constant-contact-forms' ) . '</div>'
		);

		$cmb->add_field( [
			'name'            => esc_html__( 'Site Key', 'constant-contact-forms' ),
			'id'              => '_ctct_recaptcha_site_key',
			'type'            => 'text',
			'before_row'      => $before_recaptcha,
			'sanitization_cb' => [ $this, 'sanitize_recaptcha_api_key_string' ],
			'attributes'      => [
				'maxlength' => 50,
			],
		] );

		$cmb->add_field( [
			'name'            => esc_html__( 'Secret Key', 'constant-contact-forms' ),
			'id'              => '_ctct_recaptcha_secret_key',
			'type'            => 'text',
			'sanitization_cb' => [ $this, 'sanitize_recaptcha_api_key_string' ],
			'attributes'      => [
				'maxlength' => 50,
			],
		] );

		$before_global_css = sprintf(
			'<hr /><h2>%s</h2>',
			esc_html__( 'Global Form CSS Settings', 'constant-contact-forms' )
		);

		$cmb->add_field( [
			'name'        => esc_html__( 'CSS Classes', 'constant-contact_forms' ),
			'id'          => '_ctct_form_custom_classes',
			'type'        => 'text',
			'description' => esc_html__(
					'Provide custom classes for the form separated by a single space.',
					'constant-contact-forms'
			),
			'before_row'  => $before_global_css,
		] );

		$cmb->add_field( [
			'name'             => esc_html__( 'Label Placement', 'constant-contact-forms' ),
			'id'               => '_ctct_form_label_placement',
			'type'             => 'select',
			'default'          => 'top',
			'show_option_none' => false,
			'options'          => [
				'top'    => esc_html__( 'Top', 'constant-contact-forms' ),
				'left'   => esc_html__( 'Left', 'constant-contact-forms' ),
				'right'  => esc_html__( 'Right', 'constant-contact-forms' ),
				'bottom' => esc_html__( 'Bottom', 'constant-contact-forms' ),
				'hidden' => esc_html__( 'Hidden', 'constant-contact-forms' ),
			],
			'description'      => esc_html__(
				'Choose the position for the labels of the form elements.',
				'constant-contact-forms'
			),
		] );

		$before_debugging = sprintf(
			'<hr/><h2>%s</h2>',
			esc_html__( 'Support', 'constant-contact-forms' )
		);
		$cmb->add_field( [
			'name'       => esc_html__( 'Enable logging for debugging purposes.', 'constant-contact-forms' ),
			'desc'       => esc_html__( 'This option will turn on some logging functionality that can be used to deduce sources of issues with the use of Constant Contact Forms plugin.', 'constant-contact-forms' ),
			'id'         => '_ctct_logging',
			'type'       => 'checkbox',
			'before_row' => $before_debugging,
		] );

		$this->add_spam_error_fields( $cmb );
	}

	/**
	 * Get array of options for our 'optin show' settings.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of options.
	 */
	public function get_optin_show_options() {

		$optin_options = [
			'comment_form' => esc_html__( 'Add a checkbox to the comment field in your posts', 'constant-contact-forms' ),
			'login_form'   => esc_html__( 'Add a checkbox to the main WordPress login page', 'constant-contact-forms' ),
		];

		if ( get_option( 'users_can_register' ) ) {
			$optin_options['reg_form'] = esc_html__( 'Add a checkbox to the WordPress user registration page', 'constant-contact-forms' );
		}

		return $optin_options;
	}

	/**
	 * Based on a type of form we pass in, check if the saved option
	 * for that form is checked or not in the admin.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type Allowed values: 'login_form', 'comment_form', 'reg_form'.
	 * @return boolean If should show or not.
	 */
	public function check_if_optin_should_show( $type ) {

		$available_areas = ctct_get_settings_option( '_ctct_optin_forms', [] );

		if ( ! is_array( $available_areas ) ) {
			return false;
		}

		// Otherwise, check to see if our check is in the array.
		return in_array( $type, $available_areas, true );
	}

	/**
	 * Potentially add our opt-in form to the login form. We have three almost
	 * identical functions here. This allows us to hook them all in by themselves
	 * and determine whether or not they should have been hooked in when we get
	 * to displaying them, rather than on potentially pages we dont care about.
	 *
	 * @since 1.0.0
	 */
	public function optin_form_field_login() {
		if ( $this->check_if_optin_should_show( 'login_form' ) ) {
			$this->optin_form_field();
		}
	}

	/**
	 * Potentially add our opt-in form to comment forms.
	 *
	 * @since 1.0.0
	 */
	public function optin_form_field_comment() {
		if ( $this->check_if_optin_should_show( 'comment_form' ) ) {
			$this->optin_form_field();
		}
	}

	/**
	 * Potentially add our opt-in form to the registration form.
	 *
	 * @since 1.0.0
	 */
	public function optin_form_field_registration() {
		if ( $this->check_if_optin_should_show( 'reg_form' ) ) {
			$this->optin_form_field();
		}
	}

	/**
	 * Opt in field checkbox.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function optin_form_field() {
		if ( ! constant_contact()->api->is_connected() ) {
			return;
		}

		$saved_label = ctct_get_settings_option( '_ctct_optin_label', '' );
		$list        = ctct_get_settings_option( '_ctct_optin_list', '' );


		$label = $saved_label ?: esc_html__( 'Sign up to our newsletter.', 'constant-contact-forms' );

		?>
		<p class="ctct-optin-wrapper" style="padding: 0 0 1em 0;">
			<label for="ctct_optin">
				<input type="checkbox" value="<?php echo esc_attr( $list ); ?>" class="checkbox" id="ctct_optin" name="ctct_optin_list" />
				<?php echo esc_attr( $label ); ?>
			</label>
			<?php echo constant_contact()->display->get_disclose_text(); ?>
			<?php wp_nonce_field( 'ct_ct_add_to_optin', 'ct_ct_optin' ); ?>
		</p>
		<?php

	}

	/**
	 * Sends contact to CTCT if optin checked.
	 *
	 * @since 1.0.0
	 *
	 * @throws Exception
	 *
	 * @param array $comment_data Comment form data.
	 * @return array Comment form data.
	 */
	public function process_optin_comment_form( $comment_data ) {

		if ( ! isset( $_POST['ctct_optin_list'] ) ) {
			return $comment_data;
		}

		if ( ! isset( $_POST['ct_ct_optin'] ) ) {
			return $comment_data;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ct_ct_optin'] ) ), 'ct_ct_add_to_optin' ) ) {
			constant_contact_maybe_log_it( 'Nonces', 'process_optin_comment_form() nonce failed to verify.' );
			return $comment_data;
		}

		return $this->_process_comment_data_for_optin( $comment_data );
	}

	/**
	 * Process our comment data and send to CC.
	 *
	 * @since 1.0.0
	 *
	 * @param array $comment_data Array of comment data.
	 * @return array Passed in comment data
	 */
	public function _process_comment_data_for_optin( $comment_data ) {

		if ( isset( $comment_data['comment_author_email'] ) && $comment_data['comment_author_email'] ) {

			$name    = isset( $comment_data['comment_author'] ) ? $comment_data['comment_author'] : '';
			$website = isset( $comment_data['comment_author_url'] ) ? $comment_data['comment_author_url'] : '';

			if ( ! isset( $_POST['ctct_optin_list'] ) ) {
				return $comment_data;
			}

			$list = sanitize_text_field( wp_unslash( $_POST['ctct_optin_list'] ) );

			$args = [
				'list'       => $list,
				'email'      => sanitize_email( $comment_data['comment_author_email'] ),
				'first_name' => sanitize_text_field( $name ),
				'last_name'  => '',
				'website'    => sanitize_text_field( $website ),
			];

			constantcontact_api()->add_contact( $args );
		}

		return $comment_data;
	}

	/**
	 * Sends contact to CTCT if optin checked.
	 *
	 * @since 1.0.0
	 *
	 * @throws Exception
	 *
	 * @param array  $user User.
	 * @param string $username Login name.
	 * @param string $password User password.
	 * @return object|array CTCT return API for contact or original $user array.
	 */
	public function process_optin_login_form( $user, $username, $password ) {

		if ( ! isset( $_POST['ctct_optin_list'] ) ) {
			return $user;
		}

		if ( ! isset( $_POST['ct_ct_optin'] ) ) {
			return $user;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ct_ct_optin'] ) ), 'ct_ct_add_to_optin' ) ) {
			constant_contact_maybe_log_it( 'Nonces', 'process_optin_login_form() nonce failed to verify.' );
			return $user;
		}

		if ( empty( $username ) ) {
			return $user;
		}

		return $this->_process_user_data_for_optin( $user, $username );
	}

	/**
	 * Sends user data to CTCT.
	 *
	 * @since 1.0.0
	 *
	 * @param object $user     WP user object.
	 * @param string $username Username.
	 * @return object Passed in $user object.
	 */
	public function _process_user_data_for_optin( $user, $username ) {

		$user_data = get_user_by( 'login', $username );
		$email     = '';
		$name      = '';

		if ( $user_data && isset( $user_data->data ) && isset( $user_data->data->user_email ) ) {
			$email = sanitize_email( $user_data->data->user_email );
		}

		if ( $user_data && isset( $user_data->data ) && isset( $user_data->data->display_name ) ) {
			$name = sanitize_text_field( $user_data->data->display_name );
		}

		if ( ! isset( $_POST['ctct_optin_list'] ) ) {
			return $user;
		}

		$list = sanitize_text_field( wp_unslash( $_POST['ctct_optin_list'] ) );

		if ( $email ) {
			$args = [
				'email'      => $email,
				'list'       => $list,
				'first_name' => $name,
				'last_name'  => '',
			];

			constantcontact_api()->add_contact( $args );
		}

		return $user;
	}

	/**
	 * Register settings notices for display.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $object_id Option key.
	 * @param array $updated   Array of updated fields.
	 * @return void
	 */
	public function settings_notices( $object_id, $updated ) {

		if ( $object_id !== $this->key || empty( $updated ) ) {
			return;
		}

		add_settings_error( $this->key . '-notices', '', esc_html__( 'Settings updated.', 'constant-contact-forms' ), 'updated' );
		settings_errors( $this->key . '-notices' );
	}

	/**
	 * Replaces get_option with get_site_option.
	 *
	 * @since 1.0.0
	 *
	 * @param string $test Something.
	 * @param bool   $default Default to return.
	 * @return mixed Site option
	 */
	public function get_override( $test, $default = false ) {
		return get_site_option( $this->key, $default );
	}

	/**
	 * Replaces update_option with update_site_option.
	 *
	 * @since 1.0.0
	 *
	 * @param string $test         Key.
	 * @param mixed  $option_value Value to update to.
	 * @return mixed Site option
	 */
	public function update_override( $test, $option_value ) {
		return update_site_option( $this->key, $option_value );
	}

	/**
	 * Public getter method for retrieving protected/private variables.
	 *
	 * @since 1.0.0
	 *
	 * @throws Exception Invalid property.
	 *
	 * @param string $field Field to retrieve.
	 * @return mixed Field value or exception is thrown
	 */
	public function __get( $field ) {
		if ( in_array( $field, [ 'key', 'metabox_id' ], true ) ) {
			if ( isset( $this->{$field} ) ) {
				return $this->{$field};
			}

			return null;
		}

		throw new Exception( 'Invalid property: ' . $field );
	}

	/**
	 * Returns the status of our privacy policy acceptance.
	 *
	 * @since 1.2.0
	 *
	 * @return bool
	 */
	public function privacy_policy_status() {
		$status = get_option( 'ctct_privacy_policy_status', '' );
		return ! ( '' === $status || 'false' === $status );
	}

	/**
	 * Outputs the markup for the privacy policy modal popup.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function privacy_notice_markup() {
		if ( $this->privacy_policy_status() || ! constant_contact()->is_constant_contact() ) {
			return;
		}
		?>
		<div id="ctct-privacy-modal" class="ctct-modal">
			<div class="ctct-modal-dialog" role="document">
				<div class="ctct-modal-content">
					<div class="ctct-modal-header">
						<a href="#" class="ctct-modal-close" aria-hidden="true">&times;</a>
						<h2 class="ctct-logo"><img src="<?php echo constant_contact()->url . '/assets/images/constant-contact-logo.png' ?>" alt="<?php echo esc_attr_x( 'Constant Contact logo', 'img alt text', 'constant-contact-forms' ); ?>" /></h2>
					</div>
					<div class="ctct-modal-body ctct-privacy-modal-body">
						<?php
						echo constant_contact_privacy_policy_content();
						?>
					</div><!-- modal body -->
					<div id="ctct-modal-footer-privacy" class="ctct-modal-footer ctct-modal-footer-privacy">
						<a class="button button-blue ctct-connect" data-agree="true"><?php esc_html_e( 'Agree', 'constant-contact-forms' ); ?></a>
						<a class="button no-bg" data-agree="false"><?php esc_html_e( 'Disagree', 'constant-contact-forms' ); ?></a>
					</div>
				</div><!-- .modal-content -->
			</div><!-- .modal-dialog -->
		</div>
		<?php
	}

	/**
	 * Check if we have reCAPTCHA settings available to use with Google reCAPTCHA.
	 *
	 * @since 1.2.4
	 * @return bool
	 */
	public function has_recaptcha() {
		$site_key   = ctct_get_settings_option( '_ctct_recaptcha_site_key', '' );
		$secret_key = ctct_get_settings_option( '_ctct_recaptcha_secret_key', '' );

		if ( $site_key && $secret_key ) {
			return true;
		}
		return false;
	}

	/**
	 * Attempts to add the index file for protecting the log directory.
	 *
	 * @since 1.5.0
	 * @return void
	 */
	public function maybe_init_logs( $updated, $action ) {
		if ( 'updated' !== $action ) {
			return;
		}

		$this->plugin->logging->create_log_folder();
		$this->plugin->logging->create_log_index_file();
		$this->plugin->logging->create_log_file();
  }

  /*
	 * Adds a fieldset for controlling the spam error.
	 *
	 * @since 1.5.0
	 * @param object $cmb An instance of the CMB2 object.
	 */
	private function add_spam_error_fields( $cmb ) {
		$description  = '<div class="description">';
		$description .= esc_html__( 'This message displays when the plugin detects spam data.', 'constant-contact-forms' );
		$description .= esc_html__( 'Note that this message may be overriden on a per-post basis.', 'constant-contact-forms' );
		$description .= '</div>';

		$before_message = sprintf(
			'<hr/><h2>%s</h2>%s',
			__( 'Suspected Bot Error Message', 'constant-contact-forms' ),
			$description
		);

		$cmb->add_field(
			[
				'name'       => esc_html__( 'Error Message', 'constant-contact-forms' ),
				'id'         => '_ctct_spam_error',
				'type'       => 'text',
				'before_row' => $before_message,
				'default'    => $this->get_default_spam_error(),
			]
		);
	}

	/**
	 * Get the error message displayed to suspected spam input.
	 *
	 * @since 1.5.0
	 * @param string $message The error message to filter.
	 * @param mixed  $post_id The post ID of the current post, if any.
	 * @return string
	 */
	public function get_spam_error_message( $message, $post_id ) {
		$post_error = get_post_meta( $post_id, '_ctct_spam_error', true );

		if ( ! empty( $post_error ) ) {
			return $post_error;
		}

		$option_error = cmb2_get_option( '_ctct_spam_error' );

		if ( ! empty( $option_error ) ) {
			return $option_error;
		}

		return $this->get_default_spam_error();
	}

	/**
	 * Sanitize API key strings for Google reCaptcha. Length is enforced
	 *
	 * @since 1.6.0
	 *
	 * @param  mixed      $value      The unsanitized value from the form.
	 * @param  array      $field_args Array of field arguments.
	 * @param  CMB2_Field $field      The field object
	 * @return string
	 */
	public function sanitize_recaptcha_api_key_string( $value, $field_args, $field ) {
		$value = trim( $value );

		// Keys need to be under 50 chars long and have no spaces inside them.
		if ( false !== strpos( $value, ' ' ) || 50 <= strlen( $value ) ) {
			return '';
		}

		return sanitize_text_field( $value );
	}

	/**
	 * Get the default spam error message.
	 *
	 * @since 1.5.0
	 * @return string
	 */
	private function get_default_spam_error() {
		return __( 'We do not think you are human', 'constant-contact-forms' );
	}
}

/**
 * Wrapper function around cmb2_get_option.
 *
 * @since 1.0.0
 *
 * @param string $key     Options array key.
 * @param string $default Default value if no option exists.
 * @return mixed Option value.
 */
function ctct_get_settings_option( $key = '', $default = null ) {
	if ( function_exists( 'cmb2_get_option' ) ) {
		return cmb2_get_option( constant_contact()->settings->key, $key, $default );
	}

	$opts = get_option( constant_contact()->settings->key, $key, $default );
	$val  = $default;

	if ( 'all' === $key ) {
		$val = $opts;
	} elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
		$val = $opts[ $key ];
	}

	return $val;
}
