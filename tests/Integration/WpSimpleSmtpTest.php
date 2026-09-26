<?php

namespace LibreCodeCoop\SimpleSmtp\Tests\Integration;

use WP_UnitTestCase;
use WPDieException;

final class WpSimpleSmtpTest extends WP_UnitTestCase {

	private const SETTINGS = array(
		'smtp_xmailer'           => 'LibreSign',
		'smtp_hostname'          => 'app.example.org',
		'smtp_host'              => 'mail.example.org',
		'smtp_auth'              => '1',
		'smtp_port'              => '587',
		'smtp_user'              => 'mailer',
		'smtp_pass'              => 'secret',
		'smtp_secure'            => 'tls',
		'smtp_from'              => 'noreply@example.org',
		'smtp_name'              => 'LibreSign',
		'smtp_verify_peer'       => '',
		'smtp_verify_peer_name'  => '',
		'smtp_allow_self_signed' => '',
	);

	public function set_up() {
		parent::set_up();

		reset_phpmailer_instance();
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	public function tear_down() {
		$_POST    = array();
		$_REQUEST = array();

		parent::tear_down();
	}

	private function save_settings( array $settings ) {
		foreach ( $settings as $option => $value ) {
			update_option( $option, $value );
		}
	}

	private function send_mail() {
		wp_mail( 'recipient@example.org', 'Subject', 'Body' );

		return tests_retrieve_phpmailer_instance();
	}

	private function sent_recipients() {
		return array_map( static fn ( $mail ) => $mail['to'][0][0], tests_retrieve_phpmailer_instance()->mock_sent );
	}

	private function log_in_as( $role ) {
		wp_set_current_user( self::factory()->user->create( array( 'role' => $role ) ) );
	}

	private function submit_settings_page( array $fields ) {
		$submitted = wp_slash( $fields );
		$_POST     = $submitted;
		$_REQUEST  = $submitted;

		ob_start();
		try {
			librecode_simple_smtp_render_settings_page();
		} catch ( WPDieException $exception ) {
			ob_end_clean();
			return $exception;
		}

		return ob_get_clean();
	}

	private function settings_form( array $settings ) {
		return $settings + array(
			'wpss_save_settings' => 'Save settings',
			'wpss_nonce_field'   => wp_create_nonce( 'wpss_settings_nonce' ),
		);
	}

	public function test_sends_through_smtp_with_the_saved_settings() {
		$this->save_settings( self::SETTINGS );

		$mailer = $this->send_mail();

		$this->assertSame( 'smtp', $mailer->Mailer );
		$this->assertSame( 'mail.example.org', $mailer->Host );
		$this->assertSame( '587', $mailer->Port );
		$this->assertTrue( $mailer->SMTPAuth );
		$this->assertSame( 'mailer', $mailer->Username );
		$this->assertSame( 'secret', $mailer->Password );
		$this->assertSame( 'tls', $mailer->SMTPSecure );
		$this->assertSame( 'LibreSign', $mailer->XMailer );
		$this->assertSame( 'app.example.org', $mailer->Hostname );
		$this->assertSame( 'noreply@example.org', $mailer->From );
		$this->assertSame( 'LibreSign', $mailer->FromName );
	}

	public function test_keeps_the_wordpress_sender_when_none_is_saved() {
		$this->save_settings(
			array(
				'smtp_from' => '',
				'smtp_name' => '',
			) + self::SETTINGS
		);

		$mailer = $this->send_mail();

		$this->assertSame( 'wordpress@example.org', $mailer->From );
		$this->assertSame( 'WordPress', $mailer->FromName );
	}

	/**
	 * @dataProvider provide_smtp_auth_values
	 */
	public function test_reads_the_smtp_auth_option( $value, $expected ) {
		$this->save_settings( array( 'smtp_auth' => $value ) + self::SETTINGS );

		$this->assertSame( $expected, $this->send_mail()->SMTPAuth );
	}

	public static function provide_smtp_auth_values() {
		yield 'one'               => array( '1', true );
		yield 'zero'              => array( '0', false );
		yield 'true'              => array( 'true', true );
		yield 'true in uppercase' => array( 'TRUE', true );
		yield 'on'                => array( 'on', true );
		yield 'ok'                => array( 'ok', true );
		yield 'false'             => array( 'false', false );
		yield 'yes'               => array( 'yes', false );
		yield 'empty'             => array( '', false );
	}

	public function test_reads_a_missing_smtp_auth_option_as_false() {
		$this->save_settings( self::SETTINGS );
		delete_option( 'smtp_auth' );

		$this->assertFalse( $this->send_mail()->SMTPAuth );
	}

	public function test_keeps_the_default_ssl_options_when_they_are_empty() {
		$this->save_settings( self::SETTINGS );

		$this->assertSame( array(), $this->send_mail()->SMTPOptions );
	}

	/**
	 * @dataProvider provide_mail_from_filters
	 */
	public function test_replaces_the_sender_with_the_saved_one( $filter, $option ) {
		update_option( $option, 'Saved' );

		$this->assertSame( 'Saved', apply_filters( $filter, 'Default' ) );
	}

	/**
	 * @dataProvider provide_mail_from_filters
	 */
	public function test_keeps_the_default_sender_when_none_is_saved( $filter, $option ) {
		update_option( $option, '' );

		$this->assertSame( 'Default', apply_filters( $filter, 'Default' ) );
	}

	public static function provide_mail_from_filters() {
		yield 'address' => array( 'wp_mail_from', 'smtp_from' );
		yield 'name'    => array( 'wp_mail_from_name', 'smtp_name' );
	}

	public function test_links_the_settings_page_first_in_the_plugin_actions() {
		$links = apply_filters(
			'plugin_action_links_' . plugin_basename( dirname( __DIR__, 2 ) . '/wp-simple-smtp.php' ),
			array( 'deactivate' => '<a href="#">Deactivate</a>' )
		);

		$this->assertStringContainsString( 'options-general.php?page=wpss-settings', $links[0] );
		$this->assertSame( '<a href="#">Deactivate</a>', $links['deactivate'] );
	}

	public function test_adds_the_settings_page_for_administrators() {
		global $submenu;

		$this->log_in_as( 'administrator' );
		do_action( 'admin_menu' );

		$pages = wp_list_pluck( $submenu['options-general.php'], 1, 2 );
		$this->assertSame( 'manage_options', $pages['wpss-settings'] );
	}

	public function test_does_not_register_the_hidden_test_email_page() {
		$this->log_in_as( 'administrator' );
		do_action( 'admin_menu' );

		$this->assertFalse( has_action( get_plugin_page_hookname( 'wpss-test-email', '' ) ) );
	}

	public function test_shows_nothing_and_saves_nothing_to_users_without_manage_options() {
		$this->log_in_as( 'editor' );
		update_option( 'smtp_host', 'mail.example.org' );

		$output = $this->submit_settings_page( $this->settings_form( array( 'smtp_host' => 'evil.example.org' ) ) );

		$this->assertSame( '', $output );
		$this->assertSame( 'mail.example.org', get_option( 'smtp_host' ) );
	}

	public function test_shows_the_saved_settings_in_the_form() {
		$this->log_in_as( 'administrator' );
		$this->save_settings( array( 'smtp_name' => 'Libre "Sign"' ) + self::SETTINGS );

		$output = $this->submit_settings_page( array() );

		$this->assertStringContainsString( 'name="smtp_name" id="smtp_name" value="Libre &quot;Sign&quot;"', $output );
		$this->assertStringContainsString( '<input type="password" name="smtp_pass" id="smtp_pass" value="secret"', $output );
	}

	public function test_saves_every_submitted_setting() {
		$this->log_in_as( 'administrator' );

		$output = $this->submit_settings_page( $this->settings_form( self::SETTINGS ) );

		$this->assertStringContainsString( 'Configurações salvas com sucesso.', $output );
		foreach ( self::SETTINGS as $option => $value ) {
			$this->assertSame( $value, get_option( $option ), $option );
		}
	}

	public function test_saves_a_setting_missing_from_the_form_as_empty() {
		$this->log_in_as( 'administrator' );
		update_option( 'smtp_user', 'mailer' );

		$this->submit_settings_page( $this->settings_form( array( 'smtp_host' => 'mail.example.org' ) ) );

		$this->assertSame( '', get_option( 'smtp_user' ) );
	}

	public function test_saves_quotes_and_backslashes_as_typed() {
		$this->log_in_as( 'administrator' );
		$settings = array(
			'smtp_pass' => 'pa\'ss\\word',
			'smtp_name' => 'O\'Brien',
		) + self::SETTINGS;

		$this->submit_settings_page( $this->settings_form( $settings ) );
		$this->submit_settings_page( $this->settings_form( $settings ) );

		$this->assertSame( 'pa\'ss\\word', get_option( 'smtp_pass' ) );
		$this->assertSame( 'O\'Brien', get_option( 'smtp_name' ) );
	}

	public function test_strips_tags_from_the_submitted_settings() {
		$this->log_in_as( 'administrator' );

		$this->submit_settings_page( $this->settings_form( array( 'smtp_name' => '<b>LibreSign</b>' ) + self::SETTINGS ) );

		$this->assertSame( 'LibreSign', get_option( 'smtp_name' ) );
	}

	public function test_refuses_to_save_without_a_valid_nonce() {
		$this->log_in_as( 'administrator' );
		update_option( 'smtp_host', 'mail.example.org' );

		$result = $this->submit_settings_page(
			array(
				'smtp_host'          => 'evil.example.org',
				'wpss_save_settings' => 'Save settings',
				'wpss_nonce_field'   => 'forged',
			)
		);

		$this->assertInstanceOf( WPDieException::class, $result );
		$this->assertSame( 'mail.example.org', get_option( 'smtp_host' ) );
	}

	public function test_sends_the_test_email_to_the_given_address() {
		$this->log_in_as( 'administrator' );
		$this->save_settings( self::SETTINGS );

		$output = $this->submit_settings_page(
			array(
				'wpss_test_email'    => 'Send Test Email',
				'wpss_test_email_to' => 'admin@example.org',
				'wpss_nonce_field'   => wp_create_nonce( 'wpss_settings_nonce' ),
			)
		);

		$this->assertStringContainsString( 'Email sent successfully to admin@example.org.', $output );
		$this->assertSame( array( 'admin@example.org' ), $this->sent_recipients() );
	}

	public function test_reports_a_test_email_that_could_not_be_sent() {
		$this->log_in_as( 'administrator' );
		add_filter( 'pre_wp_mail', '__return_false' );

		$output = $this->submit_settings_page(
			array(
				'wpss_test_email'    => 'Send Test Email',
				'wpss_test_email_to' => 'admin@example.org',
				'wpss_nonce_field'   => wp_create_nonce( 'wpss_settings_nonce' ),
			)
		);

		$this->assertStringContainsString( 'Failed to send email. Please check your SMTP settings.', $output );
	}

	public function test_refuses_to_send_the_test_email_without_a_valid_nonce() {
		$this->log_in_as( 'administrator' );

		$result = $this->submit_settings_page(
			array(
				'wpss_test_email'    => 'Send Test Email',
				'wpss_test_email_to' => 'attacker@example.org',
			)
		);

		$this->assertInstanceOf( WPDieException::class, $result );
		$this->assertSame( array(), $this->sent_recipients() );
	}
}
