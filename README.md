# Snaptier API Client

## Installation

This version requires [PHP](https://php.net) 7.1 or 7.2.

To get the latest version, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require snaptier/client
```


## Usage

The main point of entry is the `Snaptier\API\Client` class. Simply create a new instance of that, and you're good to go!

Practically, you will also want to set authentication details before calling any of the endpoint, however, this is not required to call endpoints for which authentication is not needed. We support logging in with an OAuth2 token, or with a username and password.

```php
<?php

use Snaptier\API\Client;

$c = new Client();

$c->authenticate(Client::AUTH_OAUTH_TOKEN, 'your-token-here');
// $c->authenticate(Client::AUTH_HTTP_PASSWORD, 'your-username', 'your-password');

var_dump($c->api('users')->me());
```


## Security

If you discover a security vulnerability within this package, please send an e-mail to Miguel Piedrafita at miguel@snaptier.co. All security vulnerabilities will be promptly addressed.


## License

This package is licensed under [The MIT License (MIT)](LICENSE).
