<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace tests\unit;

use _generated\UnitTesterActions;
use Codeception\Test\Unit;
use nnrudakov\sms\services\exceptions\{
    InvalidArgumentException as SmsInvalidParamException,
    InvalidConfigException as SmsInvalidConfigException
};
use nnrudakov\sms\services\ServiceInterface;
use nnrudakov\sms\Sms;
use ReflectionMethod;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Component tests.
 *
 * @property UnitTesterActions $tester Unit $tester.
 *
 * @package    tests\unit
 * @author     Nikolay Rudakov <nnrudakov@gmail.com>
 * @copyright  2017-2021
 *
 * @group component
 */
class SmsComponentTest extends Unit
{
    private array $config;

    public function testCreateComponent(): void
    {
        $sms = Yii::$app->sms;
        self::assertInstanceOf(Sms::class, $sms, 'Component should be instance of ' . Sms::class);

        $sms = Yii::createObject($this->config);
        self::assertInstanceOf(Sms::class, $sms, 'Component should be instance of ' . Sms::class);

        $this->tester->expectThrowable(
            SmsInvalidConfigException::class,
            function () {
                $config = $this->config;
                unset($config['services']);
                Yii::createObject($config);
            }
        );
    }

    public function testCreateService(): void
    {
        $sms = Yii::createObject($this->config);
        $createService = new ReflectionMethod($sms, 'createService');
        $createService->setAccessible(true);
        $service = $createService->invoke($sms, 'beeline', $this->config['services']['beeline']);
        self::assertInstanceOf(
            ServiceInterface::class,
            $service,
            'Service should be instance of ' . ServiceInterface::class
        );

        $this->tester->expectThrowable(
            InvalidConfigException::class,
            function () {
                $config = $this->config;
                unset($config['services']['beeline']['class']);
                Yii::createObject($config['services']['beeline']);
            }
        );
    }

    public function testHasService(): void
    {
        /** @var Sms $sms */
        $sms = Yii::createObject($this->config);
        self::assertTrue($sms->hasService('beeline'));
        self::assertFalse($sms->hasService('wrong_service'));
    }

    public function testGetOneService(): void
    {
        /** @var Sms $sms */
        $sms = Yii::createObject($this->config);
        $this->tester->expectThrowable(
            SmsInvalidParamException::class,
            function () use ($sms) {
                $sms->getService('wrong_service');
            }
        );

        $service = $sms->getService('beeline');
        self::assertInstanceOf(
            ServiceInterface::class,
            $service,
            'Service should be instance of ' . ServiceInterface::class
        );
    }

    public function testGetServices(): void
    {
        /** @var Sms $sms */
        $sms = Yii::createObject($this->config);
        $services = $sms->getServices();
        self::assertNotEmpty($services);
        self::assertContainsOnlyInstancesOf(
            ServiceInterface::class,
            $services,
            'Services list should contains only ' . ServiceInterface::class . ' objects'
        );
    }

    public function testMessages(): void
    {
        $this->tester->expectThrowable(
            new SmsInvalidConfigException('Services list cannot be empty.'),
            function () {
                $config = $this->config;
                unset($config['services']);
                Yii::createObject($config);
            }
        );

        Yii::$app->language = 'ru';
        $this->tester->expectThrowable(
            new SmsInvalidConfigException('Список сервисов не может быть пустым.'),
            function () {
                $config = $this->config;
                unset($config['services']);
                Yii::createObject($config);
            }
        );
    }

    protected function _before(): void
    {
        parent::_before();
        /** @noinspection PhpIncludeInspection */
        $config = require codecept_data_dir() . 'config/config.php';
        $this->config = $config['components']['sms'];
        unset($this->config['services']['beeline']['phone']);
    }
}
