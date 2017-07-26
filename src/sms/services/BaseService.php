<?php

/**
 * Copyright (c) 2017. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace nnrudakov\sms\services;

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
     * Service ID.
     *
     * @var string
     */
    private $serviceId;

    public function setId($id)
    {
        $this->serviceId = $id;
    }

    public function getId(): string
    {
        return $this->serviceId;
    }
}
