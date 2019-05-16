FROM alpine:3.9

ENV PHPIZE_DEPS \
		autoconf dpkg-dev dpkg file g++ gcc libc-dev make pkgconf re2c

RUN apk add --no-cache --virtual .persistent-deps ca-certificates curl tar xz openssl \
    && mkdir /data /data/build /data/www /data/pid /data/log

ENV PHP_VERSION 7.3.3
ENV SWOOLE_VERSION 4.3.0
ENV PHP_INI_DIR /usr/local/etc
ENV PHP_CFLAGS="-fstack-protector-strong -fpic -fpie -O2"
ENV PHP_CPPFLAGS="$PHP_CFLAGS"
ENV PHP_LDFLAGS="-Wl,-O1 -Wl,--hash-style=both -pie"


ENV PHP_URL="https://secure.php.net/get/php-$PHP_VERSION.tar.xz/from/this/mirror"
RUN set -xe; \
	apk add --no-cache --virtual .fetch-deps gnupg wget; \
	cd /data/build; \
	wget -O php.tar.xz "$PHP_URL"; \
	apk del .fetch-deps

RUN set -xe \
    && sed -i 's/dl-cdn.alpinelinux.org/mirrors.ustc.edu.cn/g' /etc/apk/repositories \
	&& apk add \
	    --no-cache --virtual .build-deps \
		$PHPIZE_DEPS \
		argon2-dev \
		coreutils \
		curl-dev \
		libedit-dev \
		openssl-dev \
		libsodium-dev \
		libxml2-dev \
		sqlite-dev \
		libzip-dev \
        hiredis-dev \
        yaml-dev \

        && export CFLAGS="$PHP_CFLAGS" \
    		CPPFLAGS="$PHP_CPPFLAGS" \
    		LDFLAGS="$PHP_LDFLAGS" \
    	&& cd /data/build \
    	&& tar xvf php.tar.xz \
    	&& cd php-$PHP_VERSION \
    	&& gnuArch="$(dpkg-architecture --query DEB_BUILD_GNU_TYPE)" \
    	&& ./configure \
    		--build="$gnuArch" \
    		--with-config-file-path="$PHP_INI_DIR" \
    		--enable-option-checking=fatal \
    		--enable-fpm --disable-cgi --enable-ftp --enable-inline-optimization --enable-mbregex --enable-mbstring --enable-pcntl --enable-sockets \
            --enable-json --with-pdo-mysql --enable-opcache --with-mysqli --enable-mysqlnd --with-openssl --with-zlib --enable-zip --with-curl --with-mhash \
#            --disable-dom --disable-simplexml --disable-xmlreader --disable-xmlwriter --without-sqlite3 --without-pdo-sqlite \
            --with-password-argon2 --with-sodium=shared \
    		$(test "$gnuArch" = 's390x-linux-gnu' && echo '--without-pcre-jit') \
    		\
    		$PHP_EXTRA_CONFIGURE_ARGS \
    	&& make -j "$(nproc)" \
    	&& find -type f -name '*.a' -delete \
    	&& make install \
    	&& { find /usr/local/bin /usr/local/sbin -type f -perm +0111 -exec strip --strip-all '{}' + || true; } \
    	&& make clean \
    	&& cd / \
    	\
    	&& runDeps="$( \
    		scanelf --needed --nobanner --format '%n#p' --recursive /usr/local \
    			| tr ',' '\n' \
    			| sort -u \
    			| awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
    	)" \
    	&& apk add --no-cache --virtual .php-rundeps $runDeps \
    	#Swoole扩展
    	libstdc++ \
        && cd /data/build \
        && wget http://pecl.php.net/get/swoole-$SWOOLE_VERSION.tgz && tar zxvf swoole-$SWOOLE_VERSION.tgz && cd swoole-$SWOOLE_VERSION \
        && phpize && ./configure --enable-openssl --enable-http2 --enable-mysqlnd --enable-sockets  \
        && make -j 4 && make install \
        #yaml扩展
        && cd /data/build \
        && wget http://pecl.php.net/get/yaml-2.0.4.tgz  && tar zxvf yaml-2.0.4.tgz && cd /data/build/yaml-2.0.4 \
        && phpize && ./configure && make -j 4 && make install \
        && apk del .build-deps \
        && rm -rf /data/build \
        && rm -rf /tmp/pear ~/.pearrc

COPY ./php.ini /usr/local/etc/php.ini

WORKDIR /data/www
