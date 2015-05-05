# redports node Makefile

VERSION?=0.1.0
DOWNLOADURL="https://api.redports.org/downloads"
DOWNLOADDIR="downloads/"

all:	build

clean:
	rm -f rpnode.phar* manifest.json

box.phar:
	fetch -q -o - http://box-project.org/installer.php | php

private.key: box.phar
	php box.phar key:create

public.key: private.key
	php box.phar key:extract private.key

build:	box.phar public.key
	php -d phar.readonly=0 box.phar build

release: build
	@printf '   {\n' > manifest.json
	@printf '      "name": "%s",\n' rpnode.phar >> manifest.json
	@printf '      "sha1": "%s",\n' `sha1 -q rpnode.phar` >> manifest.json
	@printf '      "url": "%s/rpnode-%s.phar",\n' $(DOWNLOADURL) $(VERSION) >> manifest.json
	@printf '      "publicKey": "%s/rpnode-%s.phar.pubkey",\n' $(DOWNLOADURL) $(VERSION) >> manifest.json
	@printf '      "version": "%s"\n' $(VERSION) >> manifest.json
	@printf '   }\n' >> manifest.json

	cp -p rpnode.phar $(DOWNLOADDIR)/rpnode-$(VERSION).phar
	cp -p rpnode.phar.pubkey $(DOWNLOADDIR)/rpnode-$(VERSION).phar.pubkey
	
	tail -r $(DOWNLOADDIR)/manifest.json | tail +3 | tail -r > $(DOWNLOADDIR)/manifest.json.new
	@printf '   },\n' >> $(DOWNLOADDIR)/manifest.json.new
	cat manifest.json >> $(DOWNLOADDIR)/manifest.json.new
	@printf ']' >> $(DOWNLOADDIR)/manifest.json.new
	mv $(DOWNLOADDIR)/manifest.json.new $(DOWNLOADDIR)/manifest.json
	
