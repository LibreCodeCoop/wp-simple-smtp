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
        $smtp_auth = get_option('smtp_auth');
        if (!is_bool($smtp_auth)) {
            if (is_numeric($smtp_auth)) {
                $smtp_auth = (bool) $smtp_auth;
            } elseif (is_string($smtp_auth)) {
                $smtp_auth = strtoupper($smtp_auth);
                $smtp_auth = in_array($smtp_auth, ['TRUE', 'ON', '1', 'OK']);
            } else {
                $smtp_auth = false;
            }
        }
        $phpmailer->isSMTP();
        $phpmailer->XMailer    = get_option('smtp_xmailer');
        $phpmailer->Hostname   = get_option('smtp_hostname');
        $phpmailer->Host       = get_option('smtp_host');
        $phpmailer->SMTPAuth   = $smtp_auth;
        $phpmailer->Port       = get_option('smtp_port');
        $phpmailer->Username   = get_option('smtp_user');
        $phpmailer->Password   = get_option('smtp_pass');
        $phpmailer->SMTPSecure = get_option('smtp_secure');
        $phpmailer->From       = get_option('smtp_from');
        $phpmailer->FromName   = get_option('smtp_name');
        if (get_option('smtp_verify_peer')) {
            $phpmailer->SMTPOptions['ssl']['verify_peer'] = get_option('smtp_verify_peer');
        }
        if (get_option('smtp_verify_peer_name')) {
            $phpmailer->SMTPOptions['ssl']['verify_peer_name'] = get_option('smtp_verify_peer_name');
        }
        if (get_option('smtp_allow_self_signed')) {
            $phpmailer->SMTPOptions['ssl']['allow_self_signed'] = get_option('smtp_allow_self_signed');
        }

    }

    add_filter('wp_mail_from', 'wpss_change_mail_from');
    function wpss_change_mail_from($original_email_address) {
        $from_email = get_option('smtp_from');
        return $from_email ? $from_email : $original_email_address;
    }

    add_filter('wp_mail_from_name', 'wpss_change_mail_from_name');
    function wpss_change_mail_from_name($original_from_name) {
        $from_name = get_option('smtp_name');
        return $from_name ? $from_name : $original_from_name;
    }
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wpss_add_settings_link');

function wpss_add_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=wpss-settings">Configurações</a>';
    array_unshift($links, $settings_link);
    return $links;
}

add_action('admin_menu', 'wpss_add_settings_page');

function wpss_add_settings_page() {
    add_options_page(
        'Settings Simple SMTP',
        'Simple SMTP',
        'manage_options',
        'wpss-settings',
        'wpss_render_settings_page'
    );
}

function wpss_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['wpss_save_settings'])) {
        check_admin_referer('wpss_settings_nonce', 'wpss_nonce_field');

        $options = [
            'smtp_xmailer', 'smtp_hostname', 'smtp_host', 'smtp_auth',
            'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_secure',
            'smtp_from', 'smtp_name', 'smtp_verify_peer',
            'smtp_verify_peer_name', 'smtp_allow_self_signed'
        ];

        foreach ($options as $option) {
            $value = sanitize_text_field($_POST[$option] ?? '');
            update_option($option, $value);
        }

        echo '<div class="updated"><p>Configurações salvas com sucesso.</p></div>';
    }

    $settings = [];
    foreach ([
        'smtp_xmailer', 'smtp_hostname', 'smtp_host', 'smtp_auth',
        'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_secure',
        'smtp_from', 'smtp_name', 'smtp_verify_peer',
        'smtp_verify_peer_name', 'smtp_allow_self_signed'
    ] as $option) {
        $settings[$option] = get_option($option, '');
    }

    $field_descriptions = [
        'smtp_xmailer' => 'The XMailer header value for SMTP messages.',
        'smtp_hostname' => 'The hostname of the application sending the email.',
        'smtp_host' => 'The hostname of the mail server (e.g., mail.example.com).',
        'smtp_auth' => 'Whether to use SMTP authentication (true|false).',
        'smtp_port' => 'SMTP port number - typically 25, 465, or 587.',
        'smtp_user' => 'Username to use for SMTP authentication.',
        'smtp_pass' => 'Password to use for SMTP authentication.',
        'smtp_secure' => 'Encryption system to use - "ssl" or "tls".',
        'smtp_from' => 'The email address to use as the "From" address.',
        'smtp_name' => 'The name to use as the "From" name.',
        'smtp_verify_peer' => 'Verify the SSL certificate of the server (true|false).',
        'smtp_verify_peer_name' => 'Verify the hostname against the server certificate (true|false).',
        'smtp_allow_self_signed' => 'Allow self-signed SSL certificates (true|false).'
    ];

    ?>

    <div class="wrap">
        <h1>Settings Simple SMTP</h1>
        <form method="post" action="">
            <?php wp_nonce_field('wpss_settings_nonce', 'wpss_nonce_field'); ?>
            <table class="form-table">
                <?php foreach ($settings as $key => $value): ?>
                    <tr>
                        <th scope="row"><label for="<?php echo $key; ?>"><?php echo $key; ?></label></th>
                        <td>
                            <?php if ($key === 'smtp_pass'): ?>
                                <input type="password" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="<?php echo esc_attr($value); ?>" class="regular-text">
                            <?php else: ?>
                                <input type="text" name="<?php echo $key; ?>" id="<?php echo $key; ?>" value="<?php echo esc_attr($value); ?>" class="regular-text">
                            <?php endif; ?>
                            <p class="description"><?php echo $field_descriptions[$key]; ?></p>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php submit_button('Save settings', 'primary', 'wpss_save_settings'); ?>
        </form>
    </div>
    <div class="wrap">
        <h1>Test Email</h1>
        <p>After saving your settings, you can test email delivery:</p>
        <form method="post" action="">
            <?php wp_nonce_field('wpss_settings_nonce', 'wpss_nonce_field'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="wpss_test_email_to">Recipient Email Address</label>
                    </th>
                    <td>
                        <input type="email" name="wpss_test_email_to" id="wpss_test_email_to" class="regular-text" required>
                        <p class="description">Enter the email address to send the test email.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Send Test Email', 'primary', 'wpss_test_email'); ?>
        </form>
    </div>

    <?php

    if (isset($_POST['wpss_test_email'])) {
        $to = sanitize_email($_POST['wpss_test_email_to']);
        $subject = 'Test Email';
        $message = 'This is a test email sent from Simple SMTP.';
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        if (wp_mail($to, $subject, $message, $headers)) {
            echo '<div class="updated"><p>Email sent successfully to ' . esc_html($to) . '.</p></div>';
        } else {
            echo '<div class="error"><p>Failed to send email. Please check your SMTP settings.</p></div>';
        }
    }
}

add_action('admin_menu', 'wpss_add_test_email_page');

function wpss_add_test_email_page() {
    add_submenu_page(
        null,
        'Test Email',
        'Test Email',
        'manage_options',
        'wpss-test-email',
        'wpss_render_test_email_page'
    );
}
