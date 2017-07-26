Yii2 SMS
=============
Yii2 extension to send SMS via different services.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://poser.pugx.org/nnrudakov/yii2-sms/v/stable)](https://packagist.org/packages/nnrudakov/yii2-sms)
[![Total Downloads](https://poser.pugx.org/nnrudakov/yii2-sms/downloads)](https://packagist.org/packages/nnrudakov/yii2-sms)
[![License](https://poser.pugx.org/nnrudakov/yii2-sms/license)](https://packagist.org/packages/nnrudakov/yii2-sms)

Requirements
------------

* PHP >= 7.0
* Yii2 >= 2.0.11

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist nnrudakov/yii2-sms
```

or add

```
"nnrudakov/yii2-sms": "~1.0"
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

Set up message [translation](http://www.yiiframework.com/doc-2.0/guide-tutorial-i18n.html#message-translation):

```php
...
    'components' => [
        'i18n' => [
            'translations' => [
                'sms' => [
                    'class' => yii\i18n\PhpMessageSource::class,
                    'basePath' => '@app/messages',
                    'sourceLanguage' => 'en-US',
                ],
            ],
        ],
        ...
    ],
...
```

Usage
-----

Once the extension is installed, simply use it in your code by:

```php
 $service = Yii::$app->sms->getService('beeline');
 $service->send(['+7905XXXXXXX'], 'message');
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
