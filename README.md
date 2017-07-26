Yii2 SMS
=============
Yii2 extension to send sms via different services.

Extension implements these services:
* Beeline

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

### Configuration

Add the following in your config:

```php
...
    'components' => [
        'sms' => [
            'class'    => nnrudakov\sms\Sms::class,
                'services'     => [
                    'beeline' => [
                        'class' => nnrudakov\sms\services\beeline\Beeline::class,
                        'user' => '',
                        'password' => ''
                    ]
                ]
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

### License

**yii2-sms** is released under the MIT License. See the bundled `LICENSE.md` for details.