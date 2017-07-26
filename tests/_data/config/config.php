<?php

declare(strict_types=1);

/**
 * This is the configuration file for the unit tests.
 * You can override configuration values by creating a `config.local.php` file
 * and manipulate the `$config` variable.
 * For example to change Beeline username and password your `config.local.php` should
 * contain the following:
 *
 *
 * ```$config['components']['sms']['services']['beeline']['user'] = 'user';
 * ```$config['components']['sms']['services']['beeline']['password'] = 'pass';
 * ```$config['components']['sms']['services']['beeline']['phone'] = '+7905XXXXXXX';
 *
 * `phone` parameter required for `Beeline` tests and must not be in application.
 */

$config = [
    'id'         => 'yii2-sms-tests',
    'name'       => 'Yii2-SMS',
    'basePath'   => __DIR__ . '/..',
    'timeZone'   => 'Europe/Moscow',
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
        'sms' => [
            'class'    => nnrudakov\sms\Sms::class,
            'services' => [
                'beeline' => [
                    'class' => nnrudakov\sms\services\beeline\Beeline::class
                ]
            ]
        ]
    ]
];

if (is_file(__DIR__ . '/config.local.php')) {
    include __DIR__ . '/config.local.php';
}

return $config;
