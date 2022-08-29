FROM yiisoftware/yii2-php:7.4-apache

ARG APP_DIR=/app
RUN echo "APP_DIR: ${APP_DIR}"

RUN apt-get update

#region Not required but often used for debugging
RUN apt-get install -y iputils-ping
#endregion

COPY docker/Yii2Oauth2Server/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# Create alias for PHP with Xdebug
RUN echo 'alias phpx="php -d xdebug.start_with_request=1"' >> ~/.bashrc

# Copy the app directory. NOTE! For development docker-compose will overwrite this directory with a mount
COPY . ${APP_DIR}

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
