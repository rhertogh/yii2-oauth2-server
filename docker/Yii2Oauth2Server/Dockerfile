FROM yiisoftware/yii2-php:7.4-apache

# Note: Using SQLite for the stand-alone Docker image, the docker-compose configuration defaults to MySQL.
ENV YII_DB_DRIVER=SQLite

# Setting default YII2_OAUTH2_SERVER_* for stand-alone sample usage
# Note: These keys are provided for sample use only. NEVER use them for other purposes! (These environment variables should be kept in sync in the docker-compose files).
ENV YII2_OAUTH2_SERVER_PRIVATE_KEY_PASSPHRASE="secret"
ENV YII2_OAUTH2_SERVER_CODES_ENCRYPTION_KEY="def00000761b6fce5b2c1721c37602e82effb785154c3bb0db93bfb3f413012bd85d46461e28f156a3a5afab910a64d5b2665276d45f24b1085d90e12ab3d38ee47b4337"
ENV YII2_OAUTH2_SERVER_STORAGE_ENCRYPTION_KEYS="{\"2021-01-01\": \"def00000cb36fd6ed6641e0ad70805b28da86192765eb73daae7306acc537ca5e9678db80e92dbfcb489debbac0ed96139e6ff210fc0281078e99c1420d2d18e2c7388ac\",\"2022-01-01\": \"def00000c8fc3b1b8d017afc6a645f94e6d2f5fc9d71e8b3eb26e5b2de6ef23232dd19446bbeef26fbd51dd2fd4cd5641e68db28ec76f8460bb3f33aaab3cff7b9fcfe62\"}"

ENV SQLITE_DB_FILE="/var/sqlite/Yii2-Oauth2-Server.sqlite"

COPY docker/Yii2Oauth2Server/.bashrc /root/.bashrc

ARG APP_DIR=/app
RUN echo "APP_DIR: ${APP_DIR}"

RUN echo "deb http://ftp.de.debian.org/debian buster main" >> /etc/apt/sources.list

RUN apt-get update

#region Not required but often used for debugging
RUN apt-get install -y iputils-ping
#endregion

COPY docker/Yii2Oauth2Server/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# Create alias for PHP with Xdebug
RUN echo 'alias phpx="php -d xdebug.start_with_request=1"' >> ~/.bashrc

# Enable pdo_postgres
RUN apt-get install -y libpq-dev
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Copy the app directory. NOTE! For development docker-compose will overwrite this directory with a mount
COPY --chown=root:www-data --chmod=740 . ${APP_DIR}
# In the COPY above --chmod should be "=u=rwX,g=rX,o=" so no further `chmod` is needed but that's currently not supported: https://github.com/moby/buildkit/issues/1951
RUN find ${APP_DIR} -path "./vendor" -prune -o -type d -exec chmod 750 {} +
# Change ownership for the app root directory (not set by the COPY command). Note: no chown is currently needed since it's already set by the workaround above.
RUN chown root:www-data ${APP_DIR}

# Set Application specific permissions (replaces the `init` script)
RUN chmod +x ${APP_DIR}/yii && \
    chmod -R g=+wX ${APP_DIR}/sample/web/assets

RUN chmod +x /app/yii
ENV PATH="/app:${PATH}"

# Configure PHP Xdebug (Remove default config and use own)
RUN rm /usr/local/etc/php/conf.d/xdebug.ini
COPY docker/Yii2Oauth2Server/php/conf.d/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
# xdebug.log directory
RUN mkdir /var/log/xdebug/
RUN chown root:www-data /var/log/xdebug/
RUN chmod 770 /var/log/xdebug/
# xdebug.output_dir directory
RUN mkdir /tmp/xdebug
RUN chown root:www-data /tmp/xdebug
RUN chmod 770 /tmp/xdebug

COPY docker/Yii2Oauth2Server/docker-php-entrypoint /usr/local/bin/docker-php-entrypoint
RUN chmod +x /usr/local/bin/docker-php-entrypoint
