FROM php:8.0-fpm-buster

###########################################################################
# userid of www-data (linux support file permissions)
###########################################################################
ARG DOCKER_WWW_USER_ID=1000
ARG DOCKER_WWW_GROUP_ID=1000
ENV DOCKER_WWW_USER_ID $DOCKER_WWW_USER_ID
ENV DOCKER_WWW_GROUP_ID $DOCKER_WWW_GROUP_ID
RUN usermod --non-unique --uid ${DOCKER_WWW_USER_ID} www-data \
    && groupmod --gid ${DOCKER_WWW_GROUP_ID} www-data


###########################################################################
# apt sources:
###########################################################################
COPY ./apt.sources.list /etc/apt/sources.list

###########################################################################
# nginx:
###########################################################################
RUN apt-get update \
    && apt-get install -y nginx  \
    && rm -rf /var/lib/apt/lists/*
RUN rm /etc/nginx/sites-enabled/default

###########################################################################
# install Imagemagick
###########################################################################
RUN apt-get update && apt-get install -y \
        libmagickwand-dev --no-install-recommends && rm -rf /var/lib/apt/lists/*
RUN printf "\n" | pecl install imagick
RUN docker-php-ext-enable imagick
###########################################################################
# PHP:
###########################################################################
RUN apt-get update && apt-get install zlib1g-dev -y \
        libicu-dev \
        libmcrypt-dev \
        libpq-dev \
        libjpeg-dev \
        libpng-dev \
        supervisor \
        git \
        libzip-dev \
        zip \
        libonig-dev \
        libxslt-dev \
        libcurl3-dev \
        xvfb \
        libfontconfig \
        wkhtmltopdf \
    && docker-php-ext-configure intl --enable-intl \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-configure mysqli --with-mysqli \
    && docker-php-ext-configure curl --with-curl \
    && docker-php-ext-configure mbstring --enable-mbstring \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        curl \
        pdo \
        pdo_mysql \
        mysqli \
        gd \
        zip \
        intl \
        iconv \
        pcntl \
        tokenizer \
        xsl

## REDIS
RUN pecl install -o -f redis \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis

RUN mv /usr/local/etc/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf.backup
COPY ./www.conf /usr/local/etc/php-fpm.d/www.conf

###########################################################################
# composer:
###########################################################################
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_MEMORY_LIMIT -1
USER root
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN mkdir -p /var/www/.composer && chown -R www-data /var/www/.composer && chmod -R u+w /var/www/.composer
USER www-data
ENV PATH="${PATH}:/var/www/.composer/vendor/bin"
RUN composer global require laravel/installer

###########################################################################
# nodejs / npm and yarn via nvm:
###########################################################################
USER root

ARG DOCKER_NODE_VERSION=12.18.4
ENV NODE_VERSION=${DOCKER_NODE_VERSION}
ARG DOCKER_YARN_VERSION=1.22.4
ENV YARN_VERSION=${DOCKER_YARN_VERSION}
ENV NVM_DIR=/usr/local/nvm
RUN mkdir -p $NVM_DIR \
    && curl https://raw.githubusercontent.com/nvm-sh/nvm/v0.35.3/install.sh | bash \
    && . $NVM_DIR/nvm.sh \
    && nvm install $NODE_VERSION \
    && nvm alias default $NODE_VERSION \
    && nvm use default \
    && npm install yarn@$YARN_VERSION -g \
    && chown -R www-data: $NVM_DIR \
    && chmod a+x $NVM_DIR/nvm.sh \
    && mkdir -p /var/www/.cache \
    && mkdir -p /var/www/.npm \
    && mkdir -p /var/www/.yarn \
    && chown -R www-data: /var/www/.cache \
    && chown -R www-data: /var/www/.npm \
    && chown -R www-data: /var/www/.yarn

# Setup bashrc
RUN echo | tee -a /etc/skel/.bashrc >> /etc/bash.bashrc \
    && echo ". \$NVM_DIR/nvm.sh" | tee -a /etc/skel/.bashrc >> /etc/bash.bashrc \
    && echo | tee -a /etc/skel/.bashrc >> /etc/bash.bashrc

# Setup zsh & oh-my-zsh
RUN apt-get update && apt-get install -y zsh
RUN sh -c "$(curl -L https://github.com/deluan/zsh-in-docker/releases/download/v1.1.1/zsh-in-docker.sh)" -- \
    -p git \
    -p ssh-agent \
    -p laravel -p laravel5 \
    -p https://github.com/zsh-users/zsh-autosuggestions \
    -p https://github.com/zsh-users/zsh-completions \
    -p 'history-substring-search' \
    -a 'bindkey "^[OA" history-substring-search-up' \
    -a 'bindkey "^[OB" history-substring-search-down' \
    -a 'bindkey "^[OC" forward-char' \
    -a 'bindkey "^[OD" backward-char' \
    -a 'bindkey "^[OF" end-of-line' \
    -a 'bindkey "^[OH" beginning-of-line' \
    -a 'bindkey "^[[1~" beginning-of-line' \
    -a 'bindkey "^[[3~" delete-char' \
    -a 'bindkey "^[[4~" end-of-line' \
    -a 'bindkey "^[[5~" up-line-or-history' \
    -a 'bindkey "^[[6~" down-line-or-history' \
    -a 'bindkey "^?" backward-delete-char' \
    -a ". \$NVM_DIR/nvm.sh"
RUN cp -r /root/.oh-my-zsh /var/www/.oh-my-zsh \
    && chown -R www-data: /var/www/.oh-my-zsh \
    && cp /root/.zshrc /var/www/.zshrc \
    && sed -i 's+/root/.oh-my-zsh+/var/www/.oh-my-zsh+g' /var/www/.zshrc \
    && chown www-data: /var/www/.zshrc

RUN touch /var/www/.yarnrc \
    && chown www-data: /var/www/.yarnrc

###########################################################################
# ssh:
###########################################################################
USER root

RUN useradd --create-home tester && usermod -a -G www-data tester
RUN apt-get update && apt-get install -y openssh-client

ADD /ssh/git_id_rsa /root/.ssh/git_id_rsa
ADD /ssh/config-root /root/.ssh/config
ADD /ssh/git_id_rsa /home/tester/.ssh/git_id_rsa
ADD /ssh/config-tester /home/tester/.ssh/config
ADD /ssh/git_id_rsa /var/www/.ssh/git_id_rsa
ADD /ssh/config-www /var/www/.ssh/config

RUN chmod 600 /root/.ssh/git_id_rsa \
    && chmod 600 /home/tester/.ssh/git_id_rsa \
    && chmod 600 /var/www/.ssh/git_id_rsa \
    && chown -R root:root /root/.ssh \
    && chown -R tester:tester /home/tester/.ssh \
    && chown -R www-data:www-data /var/www/.ssh


USER root
RUN ssh-keyscan -H git.gal-digital.de > /root/.ssh/known_hosts
USER www-data
RUN ssh-keyscan -H git.gal-digital.de > /var/www/.ssh/known_hosts
USER tester
RUN ssh-keyscan -H git.gal-digital.de > /home/tester/.ssh/known_hosts

USER root
# add support to connect via ssh for unit tests
COPY ./ssh/insecure_id_rsa.pub /tmp/id_rsa.pub

RUN apt-get update && apt-get install -y openssh-server && rm -f /etc/service/sshd/down && \
    cat /tmp/id_rsa.pub >> /home/tester/.ssh/authorized_keys \
        && rm -f /tmp/id_rsa* \
        && chmod 644 /home/tester/.ssh/authorized_keys


###########################################################################
# Laravel:
###########################################################################

USER root

# copy ssl key
COPY ssl-keys/localhost.crt /etc/ssl/localhost/localhost.crt
COPY ssl-keys/localhost.key /etc/ssl/localhost/localhost.key

# vim
RUN apt-get update && apt-get install vim -y

# supervisor to execute queues
RUN apt-get update && apt-get install supervisor -y
COPY supervisord.conf /etc/supervisord.conf

# crontab
RUN apt-get update && apt-get install cron -y
COPY crontab /etc/crontab
RUN chmod 644 /etc/crontab
RUN crontab /etc/crontab

# support tinker
RUN mkdir -p ~/.config/psysh \
    && chmod -R 755 ~/.config \
    && mkdir -p /var/www/.config/psysh \
    && chown -R www-data: /var/www/.config/ \
    && chmod -R 755 /var/www/.config/

STOPSIGNAL SIGTERM

COPY entrypoint /var/run/entrypoint
RUN chmod +x /var/run/entrypoint

ENTRYPOINT ["/var/run/entrypoint"]
