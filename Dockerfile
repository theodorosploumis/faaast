FROM ubuntu:20.04

RUN apt-get update

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
    python \
    python-pip \
    python-software-properties \
    ruby-all-dev \
    sqlite3 \
    zlib1g-dev

# Install PHP
RUN apt-get update -y && \
    apt-get install -yqq php7.4 \
    php7.4-cli \
    php7.4-curl \
    php7.4-common \
    php7.4-mbstring \
    php7.4-gd \
    php7.4-intl \
    php7.4-xml \
    php7.4-json \
    php7.4-mysql \
    php7.4-mcrypt \
    php7.4-zip

# Prepare to install nodejs
RUN curl -sL https://deb.nodesource.com/setup_14.x | bash -

RUN apt-get update && \
    apt-get install -yqq nodejs build-essential

# Upgrade pip
RUN pip install --upgrade pip

# Install Bundler
RUN gem install bundler --no-ri --no-rdoc && \
    echo "gem: --no-document" >> ~/.gemrc

# Install yarn, ied
RUN npm install -g --no-progress --quiet yarn ied

#Install pnpm
RUN curl -L https://unpkg.com/@pnpm/self-installer | node && \
    pnpm install -g pnpm

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer global require hirak/prestissimo

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

WORKDIR /home

VOLUME ["/.gems", "/.npm", "/.composer", "/usr/local/share/.cache/yarn/v1", "/.drush/cache/download"]
