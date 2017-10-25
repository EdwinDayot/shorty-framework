# Shorty Framework

[![Build Status](https://travis-ci.org/EdwinDayot/shorty-framework.svg?branch=master)](https://travis-ci.org/EdwinDayot/shorty-framework)

Use package at [https://github.com/EdwinDayot/shorty](https://github.com/EdwinDayot/shorty)

Start an application:

```php
<?php

$app = new \Shorty\Framework\Application();
$app->run();
```

Get a response object from `Application`:

```php
<?php

$app = new \Shorty\Framework\Application();
$response = $app->getResponse();
```

Add new routes:

```php
<?php

$app = new \Shorty\Framework\Application();

$router = $app->getRouter();
$router->get('posts', function () {
    return 'posts';
});

$app->run();
```