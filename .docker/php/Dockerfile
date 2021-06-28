FROM php:8.0-fpm-alpine as base

ENV USER=user
ENV GROUP=docker
ARG UID=1000
ARG GID=1000
ENV BASE_DIRECTORY=/var/local/json-attribute
ENV EXECUTABLE_DIRECTORY=/var/local/bin
WORKDIR $BASE_DIRECTORY

RUN apk update \
    && apk upgrade \
    && apk --no-cache add --virtual .build-dependency \
                g++ \
                autoconf \
                make \
    && apk add --no-cache \
            bash-completion \
            git \
            vim \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-dependency \
    && addgroup --gid $GID $GROUP \
    && adduser \
           --disabled-password \
           --gecos "" \
           --ingroup "$GROUP" \
           --uid "$UID" \
           "$USER" \
    && mkdir -p $BASE_DIRECTORY \
    && mkdir -p $EXECUTABLE_DIRECTORY \
    && chown -R $USER:$GROUP $EXECUTABLE_DIRECTORY

USER $USER

COPY .docker/php/config/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/
COPY .docker/php/config/php.ini /usr/local/etc/php/

RUN curl -s https://getcomposer.org/installer | php -- \
            --install-dir=$EXECUTABLE_DIRECTORY \
            --filename=composer
