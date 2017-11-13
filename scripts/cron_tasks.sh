#!/usr/bin/env bash

# Crontab task
# * * * * * (sleep 5; /bin/bash /var/www/scripts/cron_tasks.sh)
chown -R www-data:www-data /var/www/html/builds /var/www/error
