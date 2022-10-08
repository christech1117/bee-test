FROM php:8.1-cli-alpine3.15

# Configure non-root user.
ARG PUID=1000
ENV PUID ${PUID}
ARG PGID=1000
ENV PGID ${PGID}

RUN apk --no-cache add shadow && \
    groupmod -o -g ${PGID} www-data && \
    usermod -o -u ${PUID} -g www-data www-data -s /bin/bash

RUN apk update && apk add wget git bash libressl-dev libzip-dev zip rabbitmq-c-dev \
    && apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS \
    && rm -rf /tmp/pear \
    && pecl install zip \
    && pecl install -o -f amqp \
    && docker-php-ext-enable amqp \
    && docker-php-ext-install bcmath ctype pdo_mysql pcntl sockets \
    && rm -f swoole.tar.gz $HOME/.composer/*-old.phar \
    && docker-php-source delete \
    && apk del .phpize-deps

RUN docker-php-ext-install mysqli

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

#net-tools
RUN apk add iputils npm

COPY aliases.sh /etc/profile.d/

USER root

RUN echo '\
        . /etc/profile ; \
    ' >> /root/.bashrc

USER www-data

RUN echo '\
        . /etc/profile ; \
    ' >> ~/.bashrc

WORKDIR /app

USER root
