FROM ubuntu:20.04

ENV TZ=Europe/Athens

RUN apt-get update

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Install build tools and libraries
RUN apt-get install -yqq \
    apache2 \
    build-essential \
    software-properties-common \
    zip \
    curl \
    sudo \
    wget \
    git \
    python3-pip \
    software-properties-common \
    ruby-all-dev \
    sqlite3 \
    zlib1g-dev

# Install PHP
RUN apt-get update -y && \
    apt-get install -yqq php \
    php-cli \
    php-curl \
    php-common \
    php-mbstring \
    php-gd \
    php-intl \
    php-xml \
    php-json \
    php-mysql \
    php-zip

# Prepare to install nodejs
RUN curl -sL https://deb.nodesource.com/setup_14.x | bash -

RUN apt-get update && \
    apt-get install -yqq nodejs build-essential

# Install Bundler
RUN gem install bundler &&  echo "gem: --no-document" >> ~/.gemrc

# Install yarn, ied
RUN npm install -g --no-progress --quiet yarn ied

#Install pnpm
RUN curl -L https://unpkg.com/@pnpm/self-installer | node && \
    pnpm install -g pnpm

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Create versions for composer
RUN composer self-update --1 && cp /usr/local/bin/composer /usr/local/bin/composer1
RUN composer self-update -- && cp /usr/local/bin/composer /usr/local/bin/composer2

# Install Drush
RUN wget -q https://github.com/drush-ops/drush/releases/download/8.4.5/drush.phar && \
    chmod +x drush.phar && \
    mv drush.phar /usr/local/bin/drush

# Clean packages
RUN apt-get clean && \
    apt-get autoremove && \
    rm -rf /var/lib/apt/lists && \
    rm -f /var/www/html/index.html && \
    npm cache --force clean

# Create folders
RUN mkdir /downloads /error

# Copy useful files
COPY command.log /error/
COPY faast.readme.txt /home

RUN chmod 777 /home/faast.readme.txt

WORKDIR /home

VOLUME ["/.gems", "/.npm", "/.composer", "/usr/local/share/.cache/yarn/v1", "/.drush/cache/download"]
