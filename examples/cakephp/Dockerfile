FROM kaz29/php-apache:7.3.10

ENV APACHE_CONFDIR /etc/apache2
ENV APACHE_ENVVARS $APACHE_CONFDIR/envvars
ENV COMPOSER_HOME /usr/local
ENV APACHE_LOG_DIR /var/log/apache2
ENV LISTEN_PORT 80

COPY config/ports.conf /etc/apache2/
COPY config/security.conf /etc/apache2/conf-available/
COPY config/000-default.conf /etc/apache2/sites-available/
COPY config/opcache.ini /usr/local/etc/php/conf.d/
COPY config/apc.ini /usr/local/etc/php/conf.d/
COPY config/php.ini /usr/local/etc/php/

RUN set -eux \
    && echo "export LISTEN_PORT=$LISTEN_PORT" >> "$APACHE_ENVVARS" \
    # link application logfile to stdio
    && ln -sfT /dev/stderr "$APACHE_LOG_DIR/app-error.log" \
    && ln -sfT /dev/stdout "$APACHE_LOG_DIR/app-access.log" \
    && a2ensite 000-default

# COPY app/composer.json app/composer.lock /srv/app/

# RUN composer install --working-dir=/srv/app --dev
# COPY app /srv/app/

# RUN set -ex \
#     && mkdir -p "/srv/app/logs" \
#     && chown www-data "/srv/app/logs" \
#     && mkdir -p "/srv/app/tmp" \
#     && chown www-data "/srv/app/tmp"

EXPOSE $LISTEN_PORT
WORKDIR /srv/app
