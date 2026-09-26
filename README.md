# Simple SMTP plugin

Simple plugin to use SMTP on any WordPress instance.

## Configure

Install and go to settings pages to put your SMTP settings.

## Development

Every check is a Composer script:

```bash
composer lint  # php -l on every file
composer cs    # PHPCS
composer stan  # PHPStan
composer test  # PHPUnit
composer ci    # all of the above, in this order
```

### Tests

`composer install` brings in WordPress and the WordPress test suite, so the tests
only need a MySQL/MariaDB database they are allowed to wipe on every run.

| Variable | Default |
|---|---|
| `WP_TESTS_DB_NAME` | `wordpress_test` |
| `WP_TESTS_DB_USER` | `root` |
| `WP_TESTS_DB_PASSWORD` | `root` |
| `WP_TESTS_DB_HOST` | `mariadb` |
| `WP_TESTS_TABLE_PREFIX` | `wptests_` |
| `WP_CORE_DIR` | `vendor/wordpress` |

On the local SaaS stack:

```bash
docker exec wordpress-docker-mariadb-1 \
  mariadb -uroot -proot -e 'CREATE DATABASE IF NOT EXISTS wordpress_test;'

docker exec -w /var/www/html/wp-content/plugins/wp-simple-smtp \
  wordpress-docker-wordpress-1 composer test
```
