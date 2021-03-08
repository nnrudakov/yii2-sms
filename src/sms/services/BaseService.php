<?php

/**
 * Copyright (c) 2017-2021. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace nnrudakov\sms\services;

use Yii;
use yii\base\BaseObject;

/**
 * Base class for other services.
 *
 * @package    nnrudakov\sms\services
 * @author     Nikolay Rudakov <nnrudakov@gmail.com>
 * @copyright  2017-2021
 */
abstract class BaseService extends BaseObject implements ServiceInterface
{
    /**
     * @var string Common errors key.
     */
    protected static string $otherError = 'otherError';

    /**
     * @var string Service ID.
     */
    private string $serviceId;
    /**
     * Errors after sending messages.
     * It looks like:
     * <code>
     * [
     *  '+79050000000' => 'Some error',
     *  'otherError'   => [
     *      // list of common errors
     *  ]
     * ]
     * </code>
     *
     * @var array
     */
    private array $errors = [];

    public function setId(string $id): void
    {
        $this->serviceId = $id;
    }

    public function getId(): string
    {
        return $this->serviceId;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getErrors(string $key = null): array|string
    {
        if ($key === null) {
            return $this->errors;
        }

        return $this->errors[$key] ?? '';
    }

    /**
     * Add error to number.
     *
     * @param string $key Phone number or other key.
     * @param string $error Error value.
     */
    protected function addError(string $key, string $error): void
    {
        if ($key === static::$otherError) {
            $this->errors[$key][] = $error;
        } else {
            if (str_contains($error, 'с тем же текстом тому же адресату')) {
                $error = Yii::t(
                    'sms',
                    'You cannot send the same message to `{phone}` during 20 minutes.',
                    ['phone' => $key]
                );
            }
            $this->errors[$key] = $error;
        }
    }

    /**
     * Clear errors list.
     */
    protected function clearErrors(): void
    {
        $this->errors = [];
    }
}
