[![StyleCI](https://styleci.io/repos/32724433/shield)](https://styleci.io/repos/32724433)

# redports
Redports is a continuous integration platform for FreeBSD ports.

This is a new attempt to fully automate the redports platform and
to minimize setup and administration effort. This is why we are
heavily depending on GitHub for user accounts, repositories and
just integrate into that workflow.

Want to help? IRC #redports (freenode)


# TODO

This are the major items before we can do a first release.

node:
- poudriere integration
- business logic for building and talking to master
  (node/lib/Redports/Node/Process/Child.php)

master:
- GitHub Status API Integration for build status response
https://github.com/KnpLabs/php-github-api/blob/master/lib/Github/Api/Repository/Statuses.php

web:
- register new user at master
- register new repository at master

