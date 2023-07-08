FROM laravelsail/php82-composer:latest

RUN docker-php-ext-install pdo pdo_mysql \
    && docker-php-ext-install mysqli \
    && docker-php-source delete
RUN useradd -ms /bin/bash indieweb
WORKDIR /opt/indieweb/src/public
COPY micropubkit/ /opt/indieweb/micropubkit/

RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini \
    && sed -i "s/memory_limit = 128M/memory_limit = 512M/g" /usr/local/etc/php/php.ini

## TESTING CONFIG
# PCOV for Code Coverage
RUN pecl install pcov \
    && docker-php-ext-enable pcov \
    && echo "pcov.directory = /opt/indieweb/src" > /usr/local/etc/php/conf.d/10-pcov.ini
# PHPUnit
RUN echo "alias phpunit=/opt/indieweb/src/vendor/bin/phpunit" >> /home/indieweb/.bashrc
##

# Non-root user for live
RUN chsh -s /bin/bash
USER indieweb
ENV SHELL=/bin/bash
## TESTING CONFIG
# PHPLint
RUN composer global require overtrue/phplint \
    && echo "alias phplint=/home/indieweb/.composer/vendor/bin/phplint" >> /home/indieweb/.bashrc
# bash
# phplint --exclude=vendor /opt/indieweb/src
##
ENTRYPOINT ["/bin/bash","/opt/indieweb/src/startup.sh"]