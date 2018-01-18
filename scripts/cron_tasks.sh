#!/usr/bin/env bash

# Crontab task
# * * * * * (sleep 5; /bin/bash /var/www/faaast/scripts/cron_tasks.sh)
chown -R www-data:www-data /var/www/faaast/html/builds /var/www/faaast/error
