#!/usr/bin/env bash
#
# Install dependencies for one CI matrix row (PHP + Laravel).
# Removes composer.lock so PHP 7.3 jobs do not inherit PHP 8+ locked packages.
#
# Usage: ci-composer-install.sh <php> <laravel>   e.g. 7.3 8.*
#
set -euo pipefail

PHP_VER="${1:-}"
LARAVEL_VER="${2:-}"
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if [[ -z "$PHP_VER" || -z "$LARAVEL_VER" ]]; then
  echo "Usage: $0 <php-version> <laravel-constraint>" >&2
  exit 1
fi

cd "$ROOT"

PLATFORM_PHP="$(bash "$ROOT/scripts/ci-platform-php.sh" "$PHP_VER" "$LARAVEL_VER")"
echo "→ Composer platform.php: ${PLATFORM_PHP} (PHP ${PHP_VER}, Laravel ${LARAVEL_VER})"

laravel_major="${LARAVEL_VER%%.*}"
case "$laravel_major" in
  8)  TESTBENCH="6.*" ;;
  9)  TESTBENCH="7.*" ;;
  10) TESTBENCH="8.*" ;;
  11) TESTBENCH="9.*" ;;
  12) TESTBENCH="10.*" ;;
  13) TESTBENCH="11.*" ;;
  *)  TESTBENCH="8.*" ;;
esac

case "$PHP_VER" in
  7.3|7.4) PHPUNIT_CONSTRAINT="^9.6" ;;
  8.0)     PHPUNIT_CONSTRAINT="^9.6|^10.5" ;;
  *)       PHPUNIT_CONSTRAINT="^10.5|^11.5|^12.0" ;;
esac

rm -f composer.lock
rm -rf vendor

composer config platform.php "$PLATFORM_PHP"

composer require --dev --no-update \
  "illuminate/support:${LARAVEL_VER}" \
  "orchestra/testbench:${TESTBENCH}" \
  "phpunit/phpunit:${PHPUNIT_CONSTRAINT}"

composer update --prefer-dist --optimize-autoloader --no-progress --ansi --no-interaction

composer check-platform-reqs
