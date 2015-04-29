#!/bin/sh

if [ ! -f "box.phar" ]; then
   curl -LSs http://box-project.org/installer.php | php
fi

if [ ! -d "vendor" ]; then
   composer install
fi

php -d phar.readonly=0 box.phar build

