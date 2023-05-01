#!/bin/sh

cd /var/www/
composer install

apache2-foreground