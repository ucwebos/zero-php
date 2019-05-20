FROM php:7.3-alpine

RUN mkdir /data /data/www /data/log

RUN pecl install redis-4.3.0 \
    && pecl install yaml-2.0.4 \
    && docker-php-ext-enable redis yaml

RUN curl -fsSL 'http://pecl.php.net/get/swoole-4.3.4.tgz' -o swoole.tar.gz \
    && mkdir -p swoole \
    && tar -xf swoole.tar.gz -C swoole --strip-components=1 \
    && rm swoole.tar.gz \
    && ( \
        cd swoole \
        && phpize \
        && ./configure --enable-openssl --enable-http2 --enable-mysqlnd --enable-sockets \
        && make -j "$(nproc)" \
        && make install \
    ) \
    && rm -r xcache \
    && docker-php-ext-enable swoole

COPY ./php.ini /usr/local/etc/php.ini

WORKDIR /data/www
