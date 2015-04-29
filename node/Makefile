# redports node Makefile

all:	build

clean:
	rm -f *.phar *.pubkey

box.phar:
	fetch -q -o - http://box-project.org/installer.php | php

private.key: box.phar
	php box.phar key:create

public.key: private.key
	php box.phar key:extract private.key

build:	box.phar public.key
	php -d phar.readonly=0 box.phar build

