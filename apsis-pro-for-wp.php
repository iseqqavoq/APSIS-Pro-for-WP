<?php
/*
Plugin Name: APSIS Pro for WP
Plugin URI: https://wordpress.org/plugins/apsis-pro-for-wp
Description: APSIS Pro for WP integrates APSIS Pro with WordPress, so you can add subscription forms to APSIS Pro on your site.
Version: 1.0.9
Author: iqq
Author URI: http://www.iqq.se/
Tags: apsis, newsletter, subscription, mailing list
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * APSIS Pro for WP
 */
class APSIS_Pro_For_WP {

	/**
	 * Initialize hooks
	 */
	public static function init() {

		add_action( 'admin_menu', array( __CLASS__, 'apsispro_add_admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'apsispro_settings_init' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'apsispro_enqueue_backend_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'apsispro_enqueue_frontend_scripts' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'load_textdomain' ) );
		add_action( 'wp_ajax_nopriv_apsispro_action', array( __CLASS__, 'apsispro_action_callback' ) );
		add_action( 'wp_ajax_apsispro_action', array( __CLASS__, 'apsispro_action_callback' ) );
		add_action( 'init', array( __CLASS__, 'register_shortcodes' ) );
		add_action( 'widgets_init', array( __CLASS__, 'register_widgets' ) );

	}

	/**
	 * Enqueue backend scripts
	 */
	public static function apsispro_enqueue_backend_scripts() {

		wp_enqueue_script( 'backend', plugins_url( '/js/backend.min.js', __FILE__ ), array( 'jquery' ) );

		$options = get_option( 'apsispro_settings' );
		if ( $options['apsispro_hidden_verified'] ) :
			$verified = 1;
		else:
			$verified = 0;
		endif;

		wp_localize_script( 'backend', 'ajax_object',
			array(
				'verified' => $verified
			) );

	}

	/**
	 * Enqueue frontend scripts
	 */
	public static function apsispro_enqueue_frontend_scripts() {

		wp_enqueue_script( 'frontend', plugins_url( '/js/frontend.min.js', __FILE__ ), array( 'jquery' ) );

		wp_localize_script( 'frontend', 'ajax_object',
			array(
				'ajax_url'           	=> admin_url( 'admin-ajax.php' ),
				'error_msg_standard' 	=> __( 'An error occurred, please try again later.', 'apsispro' ),
				'error_msg_email'    	=> __( 'The e-mail address is not correct.', 'apsispro' ),
				'error_msg_mailinglist' => __( 'A mailing list needs to be selected.', 'apsispro' )
			) );

	}

	public static function load_textdomain() {
		load_plugin_textdomain( 'apsispro', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 * Create new subscriber to mailing list at APSIS.
	 */
	public static function apsispro_action_callback() {

		$form_data = array(
			'Email' => ( isset( $_POST['email'] ) ? $_POST['email'] : '' ),
			'Name'  => ( isset( $_POST['name'] ) ? $_POST['name'] : '' )
		);

		$args    = array(
			'headers' => array(
				'Accept'       => 'application/json',
				'Content-Type' => 'application/json'
			),
			'body'    => json_encode( $form_data
			)
		);
		$options = get_option( 'apsispro_settings' );
		if ( isset( $options['apsispro_hidden_https'] ) ) :
			$https = $options['apsispro_hidden_https'];
		else :
			$https = false;
		endif;
		$response = wp_remote_post( self::get_api_url( $https, $options['apsispro_input_api_key'], $options['apsispro_select_api_url'] ) . '/v1/subscribers/mailinglist/' . $_POST['listid'] . '/create?updateIfExists=true', $args );

		if ( is_wp_error( $response ) ):
			print( - 1 );
		else:
			print( $response['body'] );
			ob_start();
			do_action( 'apsispro_after_form_registration', $form_data );
			ob_end_clean();
		endif;

		wp_die();

	}

	/**
	 * Add settings page in admin menu
	 */
	public static function apsispro_add_admin_menu() {

		add_submenu_page( 'options-general.php', __( 'APSIS Pro Settings', 'apsispro' ), __( 'APSIS Pro', 'apsispro' ), 'manage_options', 'apsispro-settings', array(
			__CLASS__,
			'apsispro_settings_page'
		), 'dashicons-admin-generic' );

	}

	/**
	 * Register settings for settings page
	 */
	public static function apsispro_settings_init() {

		register_setting( 'apsispro_group', 'apsispro_settings', array( __CLASS__, 'apsispro_api_validation' ) );
		register_setting( 'apsispro_hidden_group', 'apsispro_settings' );
		register_setting( 'apsispro_shortcode_group', 'apsispro_settings' );

		add_settings_section(
			'apsispro_group_section',
			__( 'APSIS Pro settings', 'apsispro' ),
			array( __CLASS__, 'apsispro_settings_section_callback' ),
			'apsispro_group'
		);

		add_settings_section(
			'apsispro_hidden_group_section',
			'',
			array( __CLASS__, 'apsispro_hidden_settings_section_callback' ),
			'apsispro_hidden_group'
		);

		add_settings_section(
			'apsispro_shortcode_group_section',
			__( 'Shortcode Generator', 'apsispro' ),
			array( __CLASS__, 'apsispro_shortcode_settings_section_callback' ),
			'apsispro_shortcode_group'
		);

		add_settings_field(
			'apsispro_input_api_key',
			__( 'APSIS Pro API Key', 'apsispro' ),
			array( __CLASS__, 'apsispro_input_api_key_render' ),
			'apsispro_group',
			'apsispro_group_section'
		);

		add_settings_field(
			'apsispro_select_api_url',
			__( 'We access APSIS Pro via', 'apsispro' ),
			array( __CLASS__, 'apsispro_select_api_url_render' ),
			'apsispro_group',
			'apsispro_group_section'
		);

		add_settings_field(
			'apsispro_hidden_verified',
			null,
			array( __CLASS__, 'apsispro_hidden_verified_render' ),
			'apsispro_hidden_group',
			'apsispro_hidden_group_section'
		);

		add_settings_field(
			'apsispro_hidden_https',
			null,
			array( __CLASS__, 'apsispro_hidden_https_render' ),
			'apsispro_hidden_group',
			'apsispro_hidden_group_section'
		);

		add_settings_field(
			'apsispro_select_mailing_list',
			__( 'Select Mailing List(s)', 'apsispro' ),
			array( __CLASS__, 'apsispro_select_mailing_list_render' ),
			'apsispro_shortcode_group',
			'apsispro_shortcode_group_section'
		);

		add_settings_field(
			'apsispro_checkbox_name',
			__( 'Show name field', 'apsispro' ),
			array( __CLASS__, 'apsispro_checkbox_name_render' ),
			'apsispro_shortcode_group',
			'apsispro_shortcode_group_section'
		);

		add_settings_field(
			'apsispro_input_thank_you_msg',
			__( 'Thank you message', 'apsispro' ),
			array( __CLASS__, 'apsispro_input_thank_you_msg_render' ),
			'apsispro_shortcode_group',
			'apsispro_shortcode_group_section'
		);

		add_settings_field(
			'apsispro_input_submit_name',
			__( 'Submit name', 'apsispro' ),
			array( __CLASS__, 'apsispro_input_submit_name_render' ),
			'apsispro_shortcode_group',
			'apsispro_shortcode_group_section'
		);

	}

	/**
	 * Hidden field for verification status
	 */
	public static function apsispro_hidden_verified_render() {

		$options = get_option( 'apsispro_settings' );
		?>
		<input type='hidden'
		       name='apsispro_hidden_settings[apsispro_hidden_verified]'
		       value='<?php echo $options['apsispro_hidden_verified']; ?>'>
		<?php

	}

	/**
	 * Hidden field for verification status
	 */
	public static function apsispro_hidden_https_render() {

		$options = get_option( 'apsispro_settings' );
		?>
		<input type='hidden'
		       name='apsispro_hidden_settings[apsispro_hidden_https]'
		       value='<?php if ( isset( $options['apsispro_hidden_https'] ) ) : echo $options['apsispro_hidden_https']; else: echo false; endif; ?>'>
		<?php

	}

	/**
	 * Input field for API Key
	 */
	public static function apsispro_input_api_key_render() {

		$options = get_option( 'apsispro_settings' );
		?>
		<input type='password'
		       name='apsispro_settings[apsispro_input_api_key]'
		       value='<?php echo $options['apsispro_input_api_key']; ?>'>
		<span id="apsispro-verified-msg"
		      style="display:none; padding-left: 20px; color: green;"><b><?php _e( 'Verified!', 'apsispro' ); ?></b></span>
		<?php

	}

	/**
	 * Drop-down list for API URLs
	 */
	public static function apsispro_select_api_url_render() {

		$api_urls = array(
			array(
				'name' => 'www.anpdm.com',
				'url'  => 'se.api.anpdm.com:8443'
			),
			array(
				'name' => 'www.anpasia.com',
				'url'  => 'se.api.anpasia.com'
			)
		);
		$options  = get_option( 'apsispro_settings' );
		if ( isset( $options['apsispro_select_api_url'] ) ) :
			$selected_api_url = $options['apsispro_select_api_url'];
		else :
			$selected_api_url = 'se.api.anpdm.com';
		endif;
		?>
		<select class="apsispro_select_api_url" name='apsispro_settings[apsispro_select_api_url]'>
			<?php foreach ( $api_urls as $api_url ) {
				?>
				<option
					value="<?php echo $api_url['url']; ?>"<?php echo ( $selected_api_url == $api_url['url'] ) ? ' selected="selected"' : ''; ?>><?php echo $api_url['name']; ?></option>
				<?php
			} ?>
		</select>
		<?php

	}

	/**
	 * Checkboxes for mailing lists
	 */
	public static function apsispro_select_mailing_list_render() {

		$options = get_option( 'apsispro_settings' );
		if ( isset( $options['apsispro_select_mailing_list'] ) ) :
			$selected_mailinglist = $options['apsispro_select_mailing_list'];
		else :
			$selected_mailinglist = - 1;
		endif;
		?>
		<div class="apsispro_mailinglist_checkboxes">
			<?php
			$mailinglist_items = '';
			$mailinglists = self::get_mailinglists( intval( $selected_mailinglist ) );
			if ( $mailinglists !== false ) :
				foreach ( $mailinglists as $index => $list_item ) {
					$mailinglist_items .= '<input type="checkbox" id="apsispro_mailinglist_checkbox-' . $list_item['Id'] . '" name="' . $list_item['Name'] . '" value="' . $list_item['Id'] . '"><label for="apsispro_mailinglist_checkbox-' . $list_item['Id'] . '">' . $list_item['Name'] . '</label><br>';
				}
			endif;
			echo $mailinglist_items;
			?>
		</div>
		<?php

	}

	/**
	 * Checkbox for name
	 */
	public static function apsispro_checkbox_name_render() {

		$options = get_option( 'apsispro_settings' );
		?>
		<input type='checkbox'
		       class='apsispro_checkbox_name'
		       name='apsispro_settings[apsispro_checkbox_name]'<?php if ( isset( $options['apsispro_checkbox_name'] ) ) {
			checked( $options['apsispro_checkbox_name'], 1 );
		} ?>
		       value='1'>
		<?php

	}

	/**
	 * Input field for 'Thank you' message
	 */
	public static function apsispro_input_thank_you_msg_render() {

		$options = get_option( 'apsispro_settings' );
		?>
		<input type='text'
		       class='apsispro_input_thank_you_msg'
		       name='apsispro_settings[apsispro_input_thank_you_msg]'
		       value='<?php if ( isset( $options['apsispro_input_thank_you_msg'] ) ) : echo $options['apsispro_input_thank_you_msg']; endif; ?>'>
		<?php

	}

	/**
	 * Input field for 'Submit' - name
	 */
	public static function apsispro_input_submit_name_render() {

		$options = get_option( 'apsispro_settings' );
		?>
		<input type='text'
		       class='apsispro_input_submit_name'
		       name='apsispro_settings[apsispro_input_submit_name]'
		       value='<?php if ( isset( $options['apsispro_input_submit_name'] ) ) : echo $options['apsispro_input_submit_name']; endif; ?>'>
		<?php

	}

	/**
	 * Instructions for the settings fields
	 */
	public static function apsispro_settings_section_callback() {

		echo sprintf( __( 'Enter your APSIS Pro API Key and select the website you access APSIS Pro through, then click on <i>Save API Settings</i>. For more information on integration with APSIS Pro, %sclick here%s.', 'apsispro' ), '<a href="http://customers.anpdm.com/apsisproforwordpress/help.html" target="_blank">', '</a>' );

	}

	/**
	 * Instructions for the hidden settings fields
	 */
	public static function apsispro_hidden_settings_section_callback() {

		echo '';

	}

	/**
	 * Instructions for the shortcode generator fields
	 */
	public static function apsispro_shortcode_settings_section_callback() {

		echo __( 'Modify the settings and click on <i>Generate Shortcode</i> to generate a shortcode that can be inserted on the site.', 'apsispro' );

	}

	/**
	 * Validation of API Key
	 */
	public static function apsispro_api_validation( $data ) {
		$args = array(
			'headers' => array(
				'accept' => 'application/json'
			)
		);

		$data['apsispro_input_api_key'] = preg_replace( '/\s+/', '', $data['apsispro_input_api_key'] );

		$fallback = false;
		do {
			if ( $fallback ) :
				$response = wp_remote_post( self::get_api_url( false, $data['apsispro_input_api_key'], $data['apsispro_select_api_url'] ) . '/mailinglists/v2/all', $args );
			else :
				$response = wp_remote_post( self::get_api_url( true, $data['apsispro_input_api_key'], $data['apsispro_select_api_url'] ) . '/mailinglists/v2/all', $args );
			endif;

			if ( is_wp_error( $response ) && ! $fallback ) :
				if ( 'connect() timed out!' === $response->get_error_message() ) :
					$fallback = true;
				else:
					$fallback = false;
				endif;
			else:
				$fallback = false;
			endif;
		} while ( $fallback );

		if ( is_wp_error( $response ) || 200 !== $response['response']['code'] ) :
			$data['apsispro_hidden_verified'] = 0;

			add_settings_error(
				'apiCallError',
				'api',
				__( 'An error occured. Please make sure you have entered the correct APSIS API Key and selected the correct APSIS website.', 'apsispro' ),
				'error'
			);
		else :
			$data['apsispro_hidden_verified'] = 1;
		endif;

		return $data;
	}

	/**
	 * The settings page in admin
	 */
	public static function apsispro_settings_page() {

		?>
		<form action='options.php' method='post'>

			<h2>APSIS Pro for WordPress</h2>

			<?php
			settings_fields( 'apsispro_group' );
			do_settings_sections( 'apsispro_group' );
			submit_button( __( 'Save API Settings', 'apsispro' ), 'primary', 'save-api-settings-button' );
			settings_fields( 'apsispro_hidden_group' );
			do_settings_sections( 'apsispro_hidden_group' );
			?>
			<div id="apsispro-shortcode-generator" style="display: none;">
				<?php
				settings_fields( 'apsispro_shortcode_group' );
				do_settings_sections( 'apsispro_shortcode_group' );
				submit_button( __( 'Generate Shortcode', 'apsispro' ), 'secondary', 'generate-shortcode-button' );
				?>

				<table class="form-table">
					<th scope="row">
						<?php echo __( 'Generated shortcode:', 'apsispro' ); ?>
					</th>
					<td>
						<input type='text' id='apsispro-generated-code' name='apsispro-generated-code' value=''
						       readonly>
					</td>
				</table>
			</div>
			<hr style="margin-top:40px;">
			<p><?php echo __( 'If you have any general questions regarding this plugin, please visit WordPress forums.', 'apsispro' ); ?></p>
			<p><?php echo sprintf( __( 'For more information on integration with APSIS Pro, %sclick here%s.', 'apsispro' ), '<a href="http://customers.anpdm.com/apsisproforwordpress/help.html" target="_blank">', '</a>' ); ?></p>
			<p><?php echo sprintf( __( 'Visit APSIS web page at %swww.apsis.com%s.', 'apsispro' ), '<a href="http://www.apsis.com" target="_blank">', '</a>' ); ?></p>

		</form>
		<?php

	}

	/**
	 * Returns selected APSIS API URL.
	 */
	public static function get_api_url( $https, $api_key, $api_url ) {

		if ( ! isset( $api_url ) ) :
			$api_url = 'se.api.anpdm.com';
			$https = false;
		endif;

		if ( $https ) :
			$http_string   = 'https://';
		else:
			$api_url_parts = explode( ':', $api_url );
			$api_url       = $api_url_parts[0];
			$http_string = 'http://';
		endif;

		return $http_string . $api_key . ':@' . $api_url;

	}

	/**
	 * Returns mailing lists from APSIS.
	 *
	 * @param int $mailinglist_id The id of the mailing list that will be selected in drop-down list
	 */
	public static function get_mailinglists( $mailinglist_id = - 1 ) {

		$args = array(
			'headers' => array(
				'accept' => 'application/json'
			)
		);

		$options = get_option( 'apsispro_settings' );
		if ( isset( $options['apsispro_hidden_https'] ) ) :
			$https = $options['apsispro_hidden_https'];
		else :
			$https = false;
		endif;
		$response = wp_remote_post( self::get_api_url( $https, $options['apsispro_input_api_key'], $options['apsispro_select_api_url'] ) . '/mailinglists/v2/all', $args );

		if ( 200 === $response['response']['code'] ) :
			$json_a			= json_decode( $response['body'], true );
			$mailinglists	= $json_a['Result'];

			return $mailinglists;
		else:
			return '';
		endif;

	}

	/**
	 * Shows a subscription form for APSIS.
	 *
	 * @param int $mailinglist_id The id of the mailing list that user will be subscribed to
	 * @param bool $show_name Decides if name field will be visible
	 * @param string $thank_you The 'thank you' message
	 */
	public static function get_form( $mailinglist, $show_name, $thank_you, $submitname ) {

		if ( ! empty( $mailinglist ) ) : ?>
			<form action="?apsispro_action" method="post" class="apsispro-form">
				<?php if ( $show_name ) : ?>
					<p class="apsispro-form-item apsispro-signup-name-item">
						<label><?php _e( 'Name: ', 'apsispro' ); ?></label>
						<input type="text" name="apsispro-signup-name" class="apsispro-signup-name" value=""/>
					</p>
				<?php endif; ?>
				<p class="apsispro-form-item apsispro-signup-email-item">
					<label><?php _e( 'E-mail: ', 'apsispro' ); ?></label>
					<input type="email" name="apsispro-signup-email" class="apsispro-signup-email" value=""/>
				</p>
				<?php
				if ( count( $mailinglist ) === 1 ) : ?>
					<input type="hidden" name="apsispro-signup-mailinglist-id" class="apsispro-signup-mailinglist-id"
						   value="<?php echo key($mailinglist) ?>"/>
				<?php else : ?>
					<p class="apsispro-form-item apsispro-signup-mailinglists-item">
						<?php foreach ( $mailinglist as $mailinglist_item => $mailinglist_item_text ) {
							echo '<input type="checkbox" id="apsispro-signup-mailinglists-' . $mailinglist_item . '" name="' . $mailinglist_item_text . '" class="apsispro-signup-mailinglists-id" value="' . $mailinglist_item . '"><label for="apsispro-signup-mailinglists-' . $mailinglist_item . '">' . $mailinglist_item_text . '</label><br>';
						} ?>
					</p>
				<?php endif; ?>
				<input type="hidden" name="apsispro-signup-thank-you" class="apsispro-signup-thank-you"
				       value="<?php echo $thank_you ?>"/>
				<input type="submit" 
							 value="<?php echo $submitname ?>"

							 name="apsispro-signup-button"
				       class="apsispro-signup-button">
			</form>
			<p class="apsispro-signup-response"></p>
		<?php endif;

	}

	/**
	 * Register shortcodes
	 */
	public static function register_shortcodes() {

		add_shortcode( 'apsispro', array( __CLASS__, 'apsispro_shortcode' ) );

	}

	/**
	 * Shortcode for showing subscription form
	 */
	public static function apsispro_shortcode( $atts, $content = '' ) {

		$atts = shortcode_atts(
			array(
				'id'       => '',
				'text'     => false,
				'name'     => '',
				'thankyou' => __( 'Thank you for submitting!', 'apsispro' ),
				'submitname' => __('Subscribe', 'apsispro')
			), $atts
		);

		$id_array = explode( ',', $atts['id'] );
		$text_array = explode( ',', $atts['text'] );
		$mailinglist_array = array_combine( $id_array, $text_array );

		ob_start();
		self::get_form( $mailinglist_array, $atts['name'], $atts['thankyou'], $atts['submitname'] );
		$output = ob_get_clean();
		return $output;

	}

	/**
	 * Register widgets
	 */
	public static function register_widgets() {

		$options = get_option( 'apsispro_settings' );
		if ( $options['apsispro_hidden_verified'] ):
			register_widget( 'APSIS_Pro_Widget' );
		endif;

	}

}

APSIS_Pro_For_WP::init();

/**
 * APSIS Pro widget for subscription form.
 */
class APSIS_Pro_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {

		parent::__construct(
			'apsispro-widget',
			__( 'APSIS Pro Widget', 'apsispro' ),
			array( 'description' => __( 'APSIS Pro subscription form.', 'apsispro' ), )
		);

	}

	/**
	 * Frontend display of widget.
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) :
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		endif;
		if ( ! empty( $instance['text'] ) ) :
			echo '<p>' . $instance['text'] . '</p>';
		endif;

		if ( ! empty( $instance['mailinglist'] ) ) :

			$mailinglist_array = array();
			foreach ( $instance['mailinglist'] as $mailinglist => $list_item ) {
				$mailinglist_array_item = explode( '|', $list_item );
				$mailinglist_array[$mailinglist_array_item[0]] = $mailinglist_array_item[1];
			}
			$submit_name = $instance['submitname'];
			if( $submit_name == '' ) :
				$submit_name = __('Subscribe', 'apsispro');
			endif;

			APSIS_Pro_For_WP::get_form( $mailinglist_array, $instance['show-name'], $instance['thank-you-msg'], $submit_name );

		endif;

		echo $args['after_widget'];

	}

	/**
	 * Backend widget form.
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = __( 'New title', 'apsispro' );
		}
		if ( isset( $instance['text'] ) ) {
			$text = $instance['text'];
		} else {
			$text = __( 'New text', 'apsispro' );
		}
		if ( isset( $instance['mailinglist'] ) ) {
			$mailinglist = $instance['mailinglist'];
		} else {
			$mailinglist = - 1;
		}
		if ( isset( $instance['show-name'] ) ) {
			$show_name = $instance['show-name'];
		} else {
			//decide what should be default
			$show_name = false;
		}
		if ( isset( $instance['thank-you-msg'] ) ) {
			$thank_you_msg = $instance['thank-you-msg'];
		} else {
			$thank_you_msg = __( 'Thank you for submitting!', 'apsispro' );
		}
		if ( isset( $instance['submitname'] ) ) {
			$submitname = $instance['submitname'];
		} else {
			$submitname = __( 'Subscribe', 'apsispro' );
		}


		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'apsispro' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
			       name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
			       value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'text' ); ?>"><?php _e( 'Text:', 'apsispro' ); ?></label>
			<textarea class="widefat" rows="6" cols="20" id="<?php echo $this->get_field_id( 'text' ); ?>"
			          name="<?php echo $this->get_field_name( 'text' ); ?>"><?php echo $text; ?></textarea>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'mailinglist' ); ?>"><?php _e( 'Mailing List(s):', 'apsispro' ); ?></label>
			<div class="apsispro_mailinglist_checkboxes">
				<?php
				$mailinglist_items = '';
				$mailinglists = APSIS_Pro_For_WP::get_mailinglists( intval( $mailinglist ) );
				if ( $mailinglists !== false ) :
					foreach ( $mailinglists as $index => $list_item ) {
						$checked = '';
						if ( $mailinglist !== -1 ) :
							if ( in_array( $list_item['Id'], $mailinglist ) ) :
								$checked = 'checked';
							endif;
						endif;
						$mailinglist_items .= '<label style="margin-right: 8px;"><input type="checkbox" name="' . $this->get_field_name( 'mailinglist' ) . '[]" ' . $checked . ' value="' . $list_item['Id'] . '|' . $list_item['Name'] . '">' . $list_item['Name'] . '</label>';
					}
				endif;
				echo $mailinglist_items;
				?>
			</div>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show-name' ); ?>"><?php _e( 'Show name:', 'apsispro' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'show-name' ); ?>"
				<?php checked( $show_name, 'on' ); ?>
				   name="<?php echo $this->get_field_name( 'show-name' ); ?>" type="checkbox">
		</p>
		<p>
			<label
				for="<?php echo $this->get_field_id( 'thank-you-msg' ); ?>"><?php _e( 'Thank you message:', 'apsispro' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'thank-you-msg' ); ?>"
			       name="<?php echo $this->get_field_name( 'thank-you-msg' ); ?>" type="text"
			       value="<?php echo esc_attr( $thank_you_msg ); ?>">
		</p>
		<p>
			<label
				for="<?php echo $this->get_field_id( 'submitname' ); ?>"><?php _e( 'Submit name:', 'apsispro' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'submitname' ); ?>"
			       name="<?php echo $this->get_field_name( 'submitname' ); ?>" type="text"
			       value="<?php echo esc_attr( $submitname ); ?>">
		</p>

		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance                  		= $old_instance;
		$instance['title']         		= ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['text']          		= ( ! empty( $new_instance['text'] ) ) ? strip_tags( $new_instance['text'] ) : '';
		//$instance['mailinglist']   		= ( ! empty( $new_instance['mailinglist'] ) ) ? esc_sql( $new_instance['mailinglist'] ) : '';

		$instance['mailinglist'] = array();

		if ( isset ( $new_instance['mailinglist'] ) )
		{
			foreach ( $new_instance['mailinglist'] as $value )
			{
				if ( '' !== trim( $value ) )
					$instance['mailinglist'][] = $value;
			}
		}


		$instance['mailinglist_text']   = ( ! empty( $new_instance['mailinglist_text'] ) ) ? esc_sql( $new_instance['mailinglist_text'] ) : '';
		$instance['show-name']     		= ( ! empty( $new_instance['show-name'] ) ) ? esc_sql( $new_instance['show-name'] ) : false;
		$instance['thank-you-msg'] 		= ( ! empty( $new_instance['thank-you-msg'] ) ) ? strip_tags( $new_instance['thank-you-msg'] ) : '';
		$instance['submitname'] 		= ( ! empty( $new_instance['submitname'] ) ) ? strip_tags( $new_instance['submitname'] ) : '';

		return $instance;

	}
}
