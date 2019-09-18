Json parser
===========

A simple JSON parser, that doesn't use native `json_decode` function.

Installation
------------

```bash
composer require greeny/json-parser
```

Usage
-----

```php
<?php
$json = '[1,2,3]';
var_dump(greeny\Json\Parser::parse($json)); // array(1, 2, 3)
```

Running tests
-------------

```bash
vendor/bin/tester -c tests/php.ini tests
```
