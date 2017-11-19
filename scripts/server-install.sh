#!/bin/sh
# Custom script to install software on the server. Run with 'sudo'.
# Change variables according to your neeeds

INSTALL_LETSENCYPT=0
INSTALL_PORTAINER=0

# Generic software
apt-get -qqy update
apt-get install -y \
        git \
        wget \
        vim \
        zip \
        apache2 \
        php7.0 \
        php7.0-mbstring \
        php7.0-zip \
        python-certbot-apache

# Install Let's Encrypt
if [ "${INSTALL_LETSENCYPT}" -eq "1" ]; then
  certbot --apache -d ${DOMAIN} -m me@theodorosploumis.com
  certbot renew --dry-run
fi

# Clone git files
rm -rf /var/www
git clone https://github.com/theodorosploumis/faaast.git /var/www/

# Create several helpful folders
mkdir /var/www/html/builds /var/www/error
chown -R www-data:www-data /var/www/html/builds /var/www/error

# Setup cron task
chmod +x /var/www/scripts/cron_tasks.sh
(crontab -l ; echo "* * * * * (sleep 5 /bin/bash /var/www/scripts/cron_tasks.sh)") | crontab

cp /var/www/html/settings.default.php /var/www/html/settings.php

# Docker. Notice that we do not install latest Docker to support Rancher
# curl https://get.docker.com | sh
curl https://releases.rancher.com/install-docker/17.06.sh | sh

# Allow user www-data to run docker
#echo "www-data ALL=NOPASSWD: /usr/bin/docker" >> /etc/sudoers
usermod -aG docker www-data

# Start Portainer dashboard on port 9988
if [ "${INSTALL_PORTAINER}" -eq "1" ]; then
  docker volume create portainer_data
  docker run -d \
         --restart=always \
         -p 9988:9000 \
         -v /var/run/docker.sock:/var/run/docker.sock \
         -v portainer_data:/data \
         --name=portainer \
         portainer/portainer
fi

# Download docker image
docker pull tplcom/faaast:latest

# Link extra aliases
touch ~/.bashrc
echo "if [ -f /var/www/scripts/.aliases ]; then" >> ~/.bashrc
echo ". /var/www/scripts/.aliases" >> ~/.bashrc
echo "fi" >> ~/.bashrc
source ~/.bashrc

# Create folders
mkdir -p /caches/pip /caches/composer /caches/gem /caches/npm /caches/yarn /caches/drush

# Remove unused packages
apt-get autoremove

service apache2 reload

# Add swap file
# See more here: https://www.digitalocean.com/community/tutorials/how-to-add-swap-space-on-ubuntu-16-04
fallocate -l 1G /swapfile && \
chmod 600 /swapfile && \
mkswap /swapfile && \
swapon /swapfile && \
cp /etc/fstab /etc/fstab.bak && \
echo "/swapfile none swap sw 0 0" | tee -a /etc/fstab && \
echo "vm.swappiness=10 " >> /etc/sysctl.conf && \
echo "vm.vfs_cache_pressure=50 " >> /etc/sysctl.conf && \
sysctl vm.swappiness=10 && \
sysctl vm.vfs_cache_pressure=50

# Manually actions
# Set timezone
echo -n "Run: dpkg-reconfigure tzdata"
