<?php

/**
 * Copyright (c) 2017. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace nnrudakov\sms\services;

use Yii;
use yii\base\Object;

/**
 * Base class for other services.
 *
 * @package    nnrudakov\sms\services
 * @author     Nikolay Rudakov <nnrudakov@gmail.com>
 * @copyright  2017
 */
abstract class BaseService extends Object implements ServiceInterface
{
    /**
     * Common errors key;
     *
     * @var string
     */
    protected static $otherError = 'otherError';

    /**
     * Service ID.
     *
     * @var string
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

    public function setId($id)
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
     * @param string $key   Phone number or other key.
     * @param string $error Error value.
     */
    protected function addError($key, $error)
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
    protected function clearErrors()
    {
        $this->errors = [];
    }
}
