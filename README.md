# Config Service Provider

[![Build Status](https://api.travis-ci.org/tomphp/config-service-provider.svg)](https://api.travis-ci.org/tomphp/config-service-provider)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/tomphp/config-service-provider/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/tomphp/config-service-provider/?branch=master)

This package enables you to configure your application and the Dependency
Injection Container (DIC) via config arrays or files. Currently, supported
containers are:

* [League Of Extraordinary Packages' Container](https://github.com/thephpleague/container)
* [Pimple](http://pimple.sensiolabs.org/)

## Installation

Installation can be done easily using composer:

```
$ composer require tomphp/config-service-provider
```

## Example Usage

```php
<?php

use League\Container\Container; // or Pimple\Container
use TomPHP\ConfigServiceProvider\ConfigureContainer;

$config = [
    'db' => [
        'name'     => 'example_db',
        'username' => 'dbuser',
        'password' => 'dbpass',
    ],

    'di' => [
        'services' => [
            'database_connection' => [
                'class' => DatabaseConnection::class,
                'arguments' => [
                    'config.db.name',
                    'config.db.username',
                    'config.db.password',
                ],
            ],
        ],
    ],
];

$container = new Container();
ConfigureContainer::fromArray($container, $config);

$db = $container->get('database_connection');
```

### Reading Files From Disk

Instead of providing the config as an array, you can also provide a list of
file pattern matches to the `fromFiles` function.

```php
ConfigureContainer::fromFiles(
  $container,
  [
      'config_dir/*.global.php',
      'json_dir/*.json',
      'config_dir/*.local.php',
  ]
);
```

#### Merging

The reader matches files in the order the appear in the array.  As files are
read their config is be merged in, overwriting any matching keys.

#### Supported Formats

Currently `.php` and `.json` files are supported. PHP config files **must**
return a PHP array.

### Application Configuration

All values in the config array are made accessible via the DIC with the keys
separated by a separator (default: `.`) and prefixed with set string (default:
`config`).

##### Example

```php
$config = [
    'db' => [
        'name'     => 'example_db',
        'username' => 'dbuser',
        'password' => 'dbpass',
    ],
];

$container = new Container();
ConfigureContainer::fromArray($container, $config);

var_dump($container->get('config.db.name'));
/*
 * OUTPUT:
 * string(10) "example_db"
 */
```

#### Accessing A Whole Sub-Array

Whole sub-arrays are also made available for cases where you want them instead
of individual values. Altering the previous example, this is also possible
instead:

##### Example

```php
$config = [
    'db' => [
        'name'     => 'example_db',
        'username' => 'dbuser',
        'password' => 'dbpass',
    ],
];

$container = new Container();
ConfigureContainer::fromArray($container, $config);

var_dump($container->get('config.db'));
/*
 * OUTPUT:
 * array(3) {
 *   ["name"]=>
 *   string(10) "example_db"
 *   ["username"]=>
 *   string(6) "dbuser"
 *   ["password"]=>
 *   string(6) "dbpass"
 * }
 */
```

### Configuring Services

Another feature is the ability to add services to your container via the
config. By default, this is done by adding a `services` key under a `di` key in
the config in the following format:

```php
$config = [
    'di' => [
        'logger' => [
            'class'     => Logger::class,
            'singleton' => true,
            'arguments' => [
                StdoutLogger::class
            ],
            'methods'   => [
                'setLogLevel' => [ 'info' ]
            ],
        ],

        StdoutLogger::class => [
            'class' => StdoutLogger::class
        ]
    ]
];

$container = new Container();
ConfigureContainer::fromArray($container, $config);

$logger = $container->get('logger'));
```

### Configuring Inflectors

**Currently only supported by the League Container.**

It is also possible to set up
[Inflectors](http://container.thephpleague.com/inflectors/) by adding an
`inflectors` key to the config.

```php
$appConfig = [
    'inflectors' => [
        LoggerAwareInterface::class => [
            'setLogger' => ['Some\Logger']
        ]
    ]
];
```

### Extra Settings

Both `ConfigureContainer::fromArray($container, array $config, array $settings = [])`
and `ConfigureContainer::fromFiles($container, array $files, array $settings = [])`
can take an optional array of settings are the third parameter.

```php
ConfigureContainer::fromFiles(
    $container,
    ['*.cfg.php'],
    ['config_prefix' => 'settings', 'config_separator' => '/']
);
```

Available settings are:

| Name               | Description                                     | Default         |
|--------------------|-------------------------------------------------|-----------------|
| config_prefix      | Sets prefix name for config value keys.         | `config`        |
| config_separator   | Sets the separator for config key.              | `.`             |
| services_key       | Where the config for the services is.           | `di.services`   |
| inflectors_key     | Where the config for the inflectors is.         | `di.inflectors` |
| singleton_services | Sets whether services are singleton by default. | `false`         |
