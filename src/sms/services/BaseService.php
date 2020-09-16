<?php

/**
 * Copyright (c) 2017-2020. Nikolaj Rudakov
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
 * @copyright  2017-2020
 */
abstract class BaseService extends BaseObject implements ServiceInterface
{
    /**
     * @var string Common errors key.
     */
    protected static $otherError = 'otherError';

    /**
     * @var string Service ID.
     */
    private $serviceId;
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
    private $errors = [];

    public function setId($id): void
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

    public function getErrors($key = null)
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
    protected function addError($key, $error): void
    {
        if ($key === static::$otherError) {
            $this->errors[$key][] = $error;
        } else {
            if (mb_strpos($error, 'с тем же текстом тому же адресату') !== false) {
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
