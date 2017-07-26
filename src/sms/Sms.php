<?php

/**
 * Copyright (c) 2017. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace nnrudakov\sms;

use Yii;
use yii\base\{Component, InvalidConfigException};
use nnrudakov\sms\services\ServiceInterface;
use nnrudakov\sms\services\exceptions\{
    InvalidConfigException as SmsInvalidConfigException, InvalidParamException as SmsInvalidParamException
};

/**
 * Main SMS component.
 *
 * Let you to send SMS messages to users.
 *
 * @property array $services SMS services list. Required.
 *
 * @see        http://www.yiiframework.com/doc-2.0/guide-concept-components.html
 *
 * @package    nnrudakov\sms
 * @author     Nikolay Rudakov <nnrudakov@gmail.com>
 * @copyright  2017
 */
class Sms extends Component
{
    /**
     * SMS services list.
     *
     * @var array|ServiceInterface[]
     */
    private $servicesList = [];

    public function init()
    {
        parent::init();

        if (!$this->servicesList) {
            throw new SmsInvalidConfigException(Yii::t('sms', 'Services list cannot be empty.'));
        }
    }

    /**
     * Set services list.
     *
     * @param array $services A list.
     */
    public function setServices(array $services)
    {
        $this->servicesList = $services;
    }

    /**
     * Returns services list.
     *
     * @return ServiceInterface[] Instances list of {@link ServiceInterface}
     *
     * @throws InvalidConfigException when some service cannot be created
     * @throws SmsInvalidParamException when unknown service
     */
    public function getServices(): array
    {
        $services = [];
        foreach ($this->servicesList as $id => $service) {
            $services[$id] = $this->getService($id);
        }

        return $services;
    }

    /**
     * Returns {@link ServiceInterface} instance.
     *
     * @param string $id Service ID.
     *
     * @return ServiceInterface
     *
     * @throws InvalidConfigException when some service cannot be created
     * @throws SmsInvalidParamException when unknown service
     */
    public function getService($id): ServiceInterface
    {
        if (!$this->hasService($id)) {
            throw new SmsInvalidParamException(
                Yii::t('sms', 'Unknown service `{id}`.', ['id' => $id])
            );
        }

        if (!is_object($this->servicesList[$id])) {
            $this->servicesList[$id] = $this->createService($id, $this->servicesList[$id]);
        }

        return $this->servicesList[$id];
    }

    /**
     * Returns TRUE on service exists, or FALSE if not.
     *
     * @param string $id Service ID.
     *
     * @return bool
     */
    public function hasService($id): bool
    {
        return array_key_exists($id, $this->servicesList);
    }

    /**
     * Create a new {@link ServiceInterface} instance.
     *
     * @param string $id     Service ID.
     * @param array  $config Configurations.
     *
     * @return ServiceInterface|\Object Object instance.
     *
     * @throws InvalidConfigException when some service cannot be created
     */
    protected function createService($id, $config): ServiceInterface
    {
        $config['id'] = $id;

        return Yii::createObject($config);
    }
}
