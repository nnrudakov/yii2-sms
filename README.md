Yii2 SMS
=============
Yii2 extension to send SMS via different services.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://poser.pugx.org/nnrudakov/yii2-sms/v/stable)](https://packagist.org/packages/nnrudakov/yii2-sms)
[![Total Downloads](https://poser.pugx.org/nnrudakov/yii2-sms/downloads)](https://packagist.org/packages/nnrudakov/yii2-sms)
[![License](https://poser.pugx.org/nnrudakov/yii2-sms/license)](https://packagist.org/packages/nnrudakov/yii2-sms)

Requirements
------------

* PHP >= 7.3 (use v1.2.5 if you need PHP 7.2)
* Yii2 >= 2.0.14

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist nnrudakov/yii2-sms
```

or add

```
"nnrudakov/yii2-sms": "^1.0"
```

to the require section of your `composer.json` file.

Configuration
-------------

Add the following in your config:

```php
...
    'components' => [
        'sms' => [
            'class' => nnrudakov\sms\Sms::class,
                'services' => [
                    'beeline' => [
                        'class'    => nnrudakov\sms\services\beeline\Beeline::class,
                        'user'     => '',
                        'password' => ''
                    ]
                ]
        ],
        ...
    ],
...
```

You can override message [translations](http://www.yiiframework.com/doc-2.0/guide-tutorial-i18n.html#message-translation):

```php
...
    'components' => [
        'i18n' => [
            'translations' => [
                'sms' => [ // must be the same name
                    'class'            => yii\i18n\PhpMessageSource::class,
                    'basePath'         => '@app/messages',
                    'sourceLanguage'   => 'ru',
                    'forceTranslation' => true
                ],
            ],
        ],
        ...
    ],
...
```

As an example full list messages you can find in [russian](src/messages/ru/sms.php) message file.

Usage
-----

Once the extension is installed, simply use it in your code by:

```php
 $service = Yii::$app->sms->getService('beeline');
 $service->send(['+7905XXXXXXX'], 'message');
 ```
 
Extension may throw exceptions in critical situations or fill up internal erorrs list. 
You can checkout errors by:

```php
 $service->hasErrors();
 ```
 
To get full errors list just call:

```php
 $service->getErrors();
 ```
 
To get error for certain phone number add number as parameter:

```php
 $service->getErrors('+7905XXXXXXX');
 ```
 
Services
--------

Extension implements these services:
* _Beeline_. Signed contract and credentials to [account](https://beeline.amega-inform.ru/) are required. You should
paste credentials in service config:

```php
...
    'beeline' => [
        'class'    => nnrudakov\sms\services\beeline\Beeline::class,
        'user'     => 'beeline_user',
        'password' => 'beeline_password'
    ]
...
```

Tests
-----

For tests uses [Codeception](http://codeception.com/docs/).

```
vendor/bin/codecept run unit
```
