FROM ubuntu:16.04

RUN apt-get update

# Install build tools and libraries
RUN apt-get install -yqq \
    apache2 \
    build-essential \
    software-properties-common \
    zip \
    curl \
    wget \
    git \
    python \
    python-dev \
    python-pip \
    python-software-properties \
    ruby-all-dev \
    sqlite3 \
    zlib1g-dev

# Install PHP
RUN apt-get install -yqq php7.0 \
    php7.0-cli \
    php7.0-curl \
    php7.0-common \
    php7.0-mbstring \
    php7.0-gd \
    php7.0-intl \
    php7.0-xml \
    php7.0-json \
    php7.0-mysql \
    php7.0-mcrypt \
    php7.0-zip

# Prepare to install nodejs
RUN curl -sL https://deb.nodesource.com/setup_8.x | bash -

RUN apt-get update && \
    apt-get install nodejs

# Upgrade pip
RUN pip install --upgrade pip

# Install Bundler
RUN gem install bundler --no-ri --no-rdoc && \
    echo "gem: --no-document" >> ~/.gemrc

# Install yarn, pnpm, ied
RUN /bin/bash -c "npm --quiet --no-progress install -g yarn pnpm ied"

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer global require hirak/prestissimo

# Install Drush
RUN wget -q https://github.com/drush-ops/drush/releases/download/8.1.15/drush.phar && \
    chmod +x drush.phar && \
    mv drush.phar /usr/local/bin/drush

# Clean packages
RUN apt-get clean && \
    apt-get autoremove && \
    rm -rf /var/lib/apt/lists && \
    rm -f /var/www/html/index.html
    
# Create downloads folder
RUN mkdir /downloads

WORKDIR /home

VOLUME ["/.gems", "/.npm", "/.composer", "/usr/local/share/.cache/yarn/v1", "/.drush/cache/download"]
