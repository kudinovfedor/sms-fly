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
    'from' => 'InfoCentr' // Alpha name
]);

// or
$smsFly = new SmsFly();
$smsFly->setLogin('login');
$smsFly->setPassword('password');
$smsFly->setFrom('InfoCentr'); // Alpha name
```

### Sending SMS
```php
$smsFly->setTo('380989361131');
$smsFly->setMessage('Your message');

$response = $smsFly->sendSMS();

// or
$response = $smsFly->sendSMS([
    'to' => '380989361131',
    'message' => 'Your message',
]);
```

#### Campaign info
```php
$smsFly->setCampaignId('3917349');
$info = $smsFly->getCampaignInfo();

// or
$info = $smsFly->getCampaignInfo('3917349');
```

#### Campaign detail
```php
$smsFly->setCampaignId('3917349');
$detail = $smsFly->getCampaignDetail();

// or
$detail = $smsFly->getCampaignDetail('3917349');
```

#### Get message status
```php
$smsFly->setTo('380989361131');
$smsFly->setCampaignId('3917349');
$messageStatus = $smsFly->getMessageStatus('380989361131', '3917349');

// or
$messageStatus = $smsFly->getMessageStatus('380989361131', '3917349');
```

#### Get balance
```php
$balance = $smsFly->getBalance(); // 0.01
```

#### Add alfa name
```php
$alfaname = $smsFly->addAlfaname('SMS');
```

#### Check alfa name
```php
$alfaname = $smsFly->checkAlfaname('SMS');
```

#### Alfa names list
```php
$alfanamesList = $smsFly->getAlfanamesList();
```

##### License
The SMS Fly API is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT)
