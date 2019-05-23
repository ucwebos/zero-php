FROM php:7.3-alpine

ENV PHPIZE_DEPS \
		autoconf dpkg-dev dpkg file g++ gcc libc-dev make pkgconf re2c

ENV PHP_CFLAGS="-fstack-protector-strong -fpic -fpie -O2"
ENV PHP_CPPFLAGS="$PHP_CFLAGS"
ENV PHP_LDFLAGS="-Wl,-O1 -Wl,--hash-style=both -pie"
ENV PHP_INI_DIR /usr/local/etc/php

RUN docker-php-ext-install pdo_mysql

RUN set -xe \
    && sed -i 's/dl-cdn.alpinelinux.org/mirrors.ustc.edu.cn/g' /etc/apk/repositories \
    && mkdir /data /data/www /data/log /data/build \
	&& apk add \
	    --no-cache --virtual .build-deps \
		$PHPIZE_DEPS \
		openssl-dev \
		zlib-dev \
        && export CFLAGS="$PHP_CFLAGS" \
           CPPFLAGS="$PHP_CPPFLAGS" \
           LDFLAGS="$PHP_LDFLAGS" \
        #Redis扩展
        && cd /data/build \
        && wget http://pecl.php.net/get/redis-4.3.0.tgz && tar zxvf redis-4.3.0.tgz && cd redis-4.3.0 \
        && phpize && ./configure && make -j 4 && make install \
        && cd /data/build \
        #igbinary扩展
        && cd /data/build \
        && wget http://pecl.php.net/get/igbinary-3.0.1.tgz && tar -zxvf igbinary-3.0.1.tgz && cd /data/build/igbinary-3.0.1 \
        && phpize && ./configure && make -j 4 && make install \
        #protobuf扩展
        && cd /data/build \
        && wget http://pecl.php.net/get/protobuf-3.7.1.tgz && tar zxvf protobuf-3.7.1.tgz && cd /data/build/protobuf-3.7.1 \
        && phpize && ./configure && make -j 4 && make install \
        && cd /data/build \
        && runDeps="$( \
            		scanelf --needed --nobanner --format '%n#p' --recursive /usr/local \
            			| tr ',' '\n' \
            			| sort -u \
            			| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
            	)" \
        && apk add --no-cache --virtual .php-rundeps $runDeps \
        libstdc++ \
        yaml-dev \
         #yaml扩展
        && cd /data/build \
        && wget http://pecl.php.net/get/yaml-2.0.4.tgz  && tar zxvf yaml-2.0.4.tgz && cd /data/build/yaml-2.0.4 \
        && phpize && ./configure && make -j 4 && make install \
        #swoole扩展
        && cd /data/build \
        && wget http://pecl.php.net/get/swoole-4.3.4.tgz && tar zxvf swoole-4.3.4.tgz && cd /data/build/swoole-4.3.4 \
        && phpize && ./configure --enable-openssl --enable-http2 --enable-mysqlnd \
        && make -j 4 && make install \
        && apk del .build-deps \
        && rm -rf /data/build \
        && rm -rf /tmp/pear ~/.pearrc


COPY ./php.ini $PHP_INI_DIR/php.ini

WORKDIR /data/www
