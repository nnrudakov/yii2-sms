<?php

declare(strict_types=1);

/**
 * Сообщения компонента.
 *
 * Могут быть переопределены стандартными средствами Yii2.
 *
 * @package    messages
 * @author     Nikolay Rudakov <nnrudakov@gmail.com>
 * @copyright  2017
 */
return [
    'Services list cannot be empty.'          => 'Список сервисов не может быть пустым.',
    'Required `user` for `{id}` service.'     => 'Параметр `user` обязателен для сервиса `{id}`.',
    'Required `password` for `{id}` service.' => 'Параметр `password` обязателен для сервиса `{id}`.',
    'User authentication failed'              => 'Ошибка авторизации. Проверьте доступы.',
    'You cannot send the same message to `{phone}` during 20 minutes.' => 'Запрещено посылать сообщение с тем же текстом тому же адресату `{phone}` в течение 20 минут.',
];
