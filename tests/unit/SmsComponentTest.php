<?php

declare(strict_types=1);

namespace tests\unit;

use _generated\UnitTesterActions;
use Codeception\Test\Unit;
use Yii;
use yii\base\InvalidConfigException;
use nnrudakov\sms\Sms;
use nnrudakov\sms\services\ServiceInterface;
use nnrudakov\sms\services\exceptions\{
    InvalidConfigException as SmsInvalidConfigException, InvalidParamException as SmsInvalidParamException
};

/**
 * Component tests.
 *
 * @property UnitTesterActions $tester Unit $tester.
 *
 * @package    tests\unit
 * @author     Nikolay Rudakov <nnrudakov@gmail.com>
 * @copyright  2017
 *
 * @group component
 */
class SmsComponentTest extends Unit
{
    /**
     * @var array
     */
    private $config;

    protected function _before()
    {
        parent::_before();
        /** @noinspection PhpIncludeInspection */
        $config = require codecept_data_dir() . 'config/config.php';
        $this->config = $config['components']['sms'];
        unset($this->config['services']['beeline']['phone']);
    }

    public function testCreateComponent()
    {
        $sms = Yii::$app->sms;
        $this->assertInstanceOf(Sms::class, $sms, 'Component should be instance of ' . Sms::class);

        $sms = Yii::createObject($this->config);
        $this->assertInstanceOf(Sms::class, $sms, 'Component should be instance of ' . Sms::class);

        $this->tester->expectException(SmsInvalidConfigException::class, function () {
            $config = $this->config;
            unset($config['services']);
            Yii::createObject($config);
        });
    }

    public function testCreateService()
    {
        $sms = Yii::createObject($this->config);
        $createService = new \ReflectionMethod($sms, 'createService');
        $createService->setAccessible(true);
        $service = $createService->invoke($sms, 'beeline', $this->config['services']['beeline']);
        $this->assertInstanceOf(
            ServiceInterface::class,
            $service,
            'Service should be instance of ' . ServiceInterface::class
        );

        $this->tester->expectException(InvalidConfigException::class, function () {
            $config = $this->config;
            unset($config['services']['beeline']['class']);
            Yii::createObject($config['services']['beeline']);
        });
    }

    public function testHasService()
    {
        /** @var Sms $sms */
        $sms = Yii::createObject($this->config);
        $this->assertTrue($sms->hasService('beeline'));
        $this->assertFalse($sms->hasService('wrong_service'));
    }

    public function testGetOneService()
    {
        /** @var Sms $sms */
        $sms = Yii::createObject($this->config);
        $this->tester->expectException(SmsInvalidParamException::class, function () use ($sms) {
            $sms->getService('wrong_service');
        });

        $service = $sms->getService('beeline');
        $this->assertInstanceOf(
            ServiceInterface::class,
            $service,
            'Service should be instance of ' . ServiceInterface::class
        );
    }

    public function testGetServices()
    {
        /** @var Sms $sms */
        $sms = Yii::createObject($this->config);
        $services = $sms->getServices();
        $this->assertNotEmpty($services);
        $this->assertContainsOnlyInstancesOf(
            ServiceInterface::class,
            $services,
            'Services list should contains only ' . ServiceInterface::class . ' objects'
        );
    }
}
