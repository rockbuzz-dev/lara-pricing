FROM php:7.3-cli-alpine

RUN docker-php-ext-install pdo_mysql exif

ADD entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT [ "/entrypoint.sh" ]