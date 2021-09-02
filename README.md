# Simple SMTP plugin

Simple plugin to use SMTP on any WordPress instance.

## Using environments

```bash
WORDPRESS_CONFIG_EXTRA="
define( 'SMTP_USER',     'user@example.coop' );  // Username to use for SMTP authentication
define( 'SMTP_PASS',     'password-here' );      // Password to use for SMTP authentication
define( 'SMTP_HOSTNAME', 'example.coop' );       // The hostname of application
define( 'SMTP_HOST',     'mail.example.coop' );  // The hostname of the mail server
define( 'SMTP_XMAILER',  'Simple SMTP Mail' );   // The hostname of the mail server
define( 'SMTP_FROM',     'user@example.coop' );  // SMTP From email address
define( 'SMTP_NAME',     'User' );               // SMTP From name
define( 'SMTP_PORT',     '587' );                // SMTP port number - likely to be 25, 465 or 587
define( 'SMTP_SECURE',   'tls' );                // Encryption system to use - ssl or tls
define( 'SMTP_AUTH',      true );                // Use SMTP authentication (true|false)
define( 'SMTP_VERIFY_PEER', false );
define( 'SMTP_VERIFY_PEER_NAME', false );
define( 'SMTP_ALLOW_SELF_SIGNED', true );
"
```