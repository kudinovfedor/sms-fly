# SMS Fly (XML)

## Installation
Require this package with [composer](https://getcomposer.org) using the following command:
```bash
composer require kudinovfedor/sms-fly
```

## Usage
```php
use KudinovFedor\SmsFly\SmsFly;

$smsFly = new SmsFly([
    'login' => 'login',
    'password'  => 'password',
    'source' => 'InfoCentr' // Альфаимя (from)
]);

// or
$smsFly = new SmsFly();
$smsFly->setLogin('login');
$smsFly->setPassword('password');
$smsFly->setSource('InfoCentr'); // Альфаимя (from)
```

### Sending SMS
```php
$smsFly->setRecipient('380989361131');
$smsFly->setMessage('Your message');

$response = $smsFly->sendSms();
```

### Get balance
```php
$balance = $smsFly->getBalance(); // 0.01
```

#### License
The SMS Fly API is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT)
