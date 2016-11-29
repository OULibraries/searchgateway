# Search Gateway

Almost exists.

This is an API Gateway that provides a unified, simplistic, cacheable
interface to the multifarious content search backends used at OU
Libraries. It's primarily intended to provide "Top 5 Hits" style
responses for use in our bento box search display.

## Quick Start

After adding your API info to the file at `config/secrets.php`, the
gateway can be run using the standalone php web server:

```
cd "$GATEWAY_FOLDER"
composer install
php -S localhost:8888 -t web web/index.php
```

## Ansible Role

We're deploying this search gateway to dev/test/prod with our
[OULibraries.searchgateway](https://github.com/OULibraries/ansible-role-searchgateway)
ansible role.