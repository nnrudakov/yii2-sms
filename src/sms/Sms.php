<?php

/**
 * Copyright (c) 2017-2021. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace nnrudakov\sms;

use JetBrains\PhpStorm\Pure;
use nnrudakov\sms\services\exceptions\{InvalidArgumentException as SmsInvalidArgumentException,
    InvalidConfigException as SmsInvalidConfigException};
use nnrudakov\sms\services\ServiceInterface;
use Yii;
use yii\base\{Component, InvalidConfigException};
use yii\i18n\PhpMessageSource;

use function is_object;

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
 * @copyright  2017-2021
 */
class Sms extends Component
{
    /**
     * @var array|ServiceInterface[] SMS services list.
     */
    private array $servicesList = [];

    /**
     * @inheritdoc
     *
     * @throws SmsInvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        if (!isset(Yii::$app->getI18n()->translations['sms'])) {
            Yii::$app->getI18n()->translations['sms'] = [
                'class' => PhpMessageSource::class,
                'basePath' => __DIR__ . '/../messages',
                'sourceLanguage' => 'en-US'
            ];
        }

        if (!$this->servicesList) {
            throw new SmsInvalidConfigException(Yii::t('sms', 'Services list cannot be empty.'));
        }
    }

    /**
     * Set services list.
     *
     * @param array $services A list.
     */
    public function setServices(array $services): void
    {
        $this->servicesList = $services;
    }

    /**
     * Returns services list.
     *
     * @return ServiceInterface[] Instances list of {@link ServiceInterface}
     *
     * @throws InvalidConfigException when some service cannot be created
     * @throws SmsInvalidArgumentException when unknown service
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
     * @throws SmsInvalidArgumentException when unknown service
     */
    public function getService(string $id): ServiceInterface
    {
        if (!$this->hasService($id)) {
            throw new SmsInvalidArgumentException(
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
    #[Pure] public function hasService(string $id): bool
    {
        return array_key_exists($id, $this->servicesList);
    }

    /**
     * Create a new {@link ServiceInterface} instance.
     *
     * @param string $id Service ID.
     * @param array $config Configurations.
     *
     * @return array|object Object instance.
     *
     * @throws InvalidConfigException when some service cannot be created
     */
    protected function createService(string $id, array $config): array|object
    {
        $config['id'] = $id;

        return Yii::createObject($config);
    }
}
