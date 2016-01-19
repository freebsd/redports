# redports
Redports is a continuous integration platform for FreeBSD ports.

This is a new attempt to fully automate the redports platform and
to minimize setup and administration effort.

Want to help? IRC #redports (freenode)


# TODO

This are the major TODO items before we can do a first
2.0.0 release.

node:
- poudriere integration
- business logic for building and talking to master
  (node/lib/Redports/Node/Process/Child.php)

master:
- GitHub Status API Integration
https://github.com/KnpLabs/php-github-api/blob/master/lib/Github/Api/Repository/Statuses.php

- GitHub oauth integration when a user registers
https://gist.github.com/technoweenie/419219 https://docs.travis-ci.com/user/github-oauth-scopes/

