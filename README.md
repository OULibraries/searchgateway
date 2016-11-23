# Search Gateway

Doesn't quite exist yet.

This is an API Gateway that provides a unified, simplistic, cacheable
interface to the multifarious content search backends used at OU
Libraries. It's primarily intended to provide "Top 5 Hits" style
responses for use in our bento box search display.

This can be run with:

```
composer install
php -S localhost:8888 -t web web/index.php

```

It expects a configuration file at `config/secrets.php`. 