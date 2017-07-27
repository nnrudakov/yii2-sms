<?php

/**
 * Copyright (c) 2017. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace nnrudakov\sms\services;

use nnrudakov\sms\services\exceptions\{ServiceException, UnauthorizedException};

/**
 * Object interface to send SMS messages.
 *
 * You should implements this interface for every service.
 *
 * @package    nnrudakov\sms\services
 * @author     Nikolay Rudakov <nnrudakov@gmail.com>
 * @copyright  2017
 */
interface ServiceInterface
{
    /**
     * Set service ID.
     *
     * @param string $id ID.
     */
    public function setId($id);

    /**
     * Returns service ID.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Send SMS message.
     *
     * @param array  $phones  Phone numbers list. Format has an open numbering plan with 10-digit phone number with
     *                        country code: +79051234567.
     * @param string $message Message text. 480 chars max.
     *
     * @throws UnauthorizedException when service credentials are invalid
     * @throws ServiceException when service return HTTP errors
     */
    public function send(array $phones, $message);

    /**
     * Returns has service errors.
     *
     * @return bool
     */
    public function hasErrors(): bool;

    /**
     * Return error for number or whole list.
     *
     * @param string $key Phone number or `otherError` for common errors.
     *
     * @return array|string
     */
    public function getErrors($key = null);
}
