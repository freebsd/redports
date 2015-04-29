#!/bin/sh

# bootstrap box
if [ ! -f "box.phar" ]; then
   curl -LSs http://box-project.org/installer.php | php
fi

# create OpenSSL keys for signing
if [ ! -f "private.key" ]; then
   php box.phar key:create
fi

if [ ! -f "public.key" ]; then
   php box.phar key:extract private.key
fi

# bootstrap composer
if [ ! -d "vendor" ]; then
   composer install
fi

# build phar
php -d phar.readonly=0 box.phar build

