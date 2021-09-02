<?php
/**
 * Simple SMTP
 *
 * @package   wp-simple-smtp
 * @author    Vitor Mattos <vitor@php.rio>
 * @license   GPL-2.0+
 * @link      http://github.com/vitormattos
 * @copyright 2021 Vitor Mattos
 *
 * @wordpress-plugin
 * Plugin Name:       Simple SMTP
 * Plugin URI:        https://github.com/librecodecoop/wp-simple-smtp
 * Description:       Simple SMTP
 * Version:           1.0.0
 * Author:            Vitor Mattos
 * Author URI:        http://github.com/vitormattos
 * Text Domain:       wp-simple-smtp
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/librecodecoop/wp-simple-smtp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( !function_exists('librecode_simple_smtp_mail_sender') ) {
	add_action( 'phpmailer_init', 'librecode_simple_smtp_mail_sender' );
	function librecode_simple_smtp_mail_sender( $phpmailer ) {
		$phpmailer->isSMTP();
		$phpmailer->XMailer    = SMTP_XMAILER;
		$phpmailer->Hostname   = SMTP_HOSTNAME;
		$phpmailer->Host       = SMTP_HOST;
		$phpmailer->SMTPAuth   = SMTP_AUTH;
		$phpmailer->Port       = SMTP_PORT;
		$phpmailer->Username   = SMTP_USER;
		$phpmailer->Password   = SMTP_PASS;
		$phpmailer->SMTPSecure = SMTP_SECURE;
		$phpmailer->From       = SMTP_FROM;
		$phpmailer->FromName   = SMTP_NAME;
		if (defined('SMTP_VERIFY_PEER')) {
			$phpmailer->SMTPOptions['ssl']['verify_peer'] = SMTP_VERIFY_PEER;
		}
		if (defined('SMTP_VERIFY_PEER_NAME')) {
			$phpmailer->SMTPOptions['ssl']['verify_peer_name'] = SMTP_VERIFY_PEER_NAME;
		}
		if (defined('SMTP_ALLOW_SELF_SIGNED')) {
			$phpmailer->SMTPOptions['ssl']['allow_self_signed'] = SMTP_ALLOW_SELF_SIGNED;
		}

	}
}