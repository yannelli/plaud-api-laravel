FROM ubuntu:24.04

ENV DEBIAN_FRONTEND=noninteractive

# System tooling plus PHP 8.3 and the extensions exercised by the test suite
# (dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite, pdo_sqlite). pcntl is
# compiled into the php8.3-cli SAPI on Ubuntu, so it is not a separate package.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        git \
        unzip \
        php8.3-cli \
        php8.3-dom \
        php8.3-curl \
        php8.3-xml \
        php8.3-mbstring \
        php8.3-zip \
        php8.3-sqlite3 \
        php8.3-bcmath \
        php8.3-intl \
    && rm -rf /var/lib/apt/lists/*

# Install Composer with checksum verification.
RUN EXPECTED_CHECKSUM="$(curl -fsSL https://composer.github.io/installer.sig)" \
    && curl -fsSL https://getcomposer.org/installer -o /tmp/composer-setup.php \
    && ACTUAL_CHECKSUM="$(php -r "echo hash_file('sha384', '/tmp/composer-setup.php');")" \
    && [ "$EXPECTED_CHECKSUM" = "$ACTUAL_CHECKSUM" ] \
    && php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm /tmp/composer-setup.php \
    && composer --version

USER ubuntu
