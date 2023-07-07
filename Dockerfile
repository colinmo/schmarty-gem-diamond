FROM laravelsail/php82-composer:latest

RUN docker-php-ext-install pdo pdo_mysql \
    && docker-php-ext-install mysqli \
    && docker-php-source delete
RUN useradd -ms /bin/bash indieweb
WORKDIR /opt/indieweb/src/public
COPY micropubkit/ /opt/indieweb/micropubkit/
USER indieweb
ENTRYPOINT ["/bin/bash","/opt/indieweb/src/startup.sh"]