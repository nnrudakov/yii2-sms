<?php

declare(strict_types=1);

namespace tests\unit;

use _generated\UnitTesterActions;
use Codeception\Test\Unit;
use Yii;
use yii\base\InvalidConfigException;
use nnrudakov\sms\services\ServiceInterface;
use nnrudakov\sms\services\beeline\Beeline;
use nnrudakov\sms\services\exceptions\{
    InvalidConfigException as SmsInvalidConfigException, UnauthorizedException
};

/**
 * Beeline service tests.
 *
 * @property UnitTesterActions $tester Unit $tester.
 *
 * @package    tests\unit
 * @author     Nikolay Rudakov <nnrudakov@gmail.com>
 * @copyright  2017
 *
 * @group services
 * @group beeline
 */
class BeelineServiceTest extends Unit
{
    /**
     * @var array
     */
    private $config;
    /**
     * @var string
     */
    private $phone;

    protected function _before()
    {
        parent::_before();
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        Yii::$app->getI18n()->translations['sms'] = [
            'class'          => \yii\i18n\PhpMessageSource::class,
            'basePath'       => codecept_root_dir() . '/src/messages',
            'sourceLanguage' => 'en-US',
        ];
        /** @noinspection PhpIncludeInspection */
        $config = require codecept_data_dir() . 'config/config.php';
        $this->config = $config['components']['sms']['services']['beeline'];
        $this->config['id'] = 'beeline';
        $this->phone = $this->config['phone'];
        unset($this->config['phone']);
    }

    public function testCreateService()
    {
        /** @var ServiceInterface $service */
        $service = Yii::createObject($this->config);
        $this->assertInstanceOf(
            Beeline::class,
            $service,
            'Service should be instance of ' . Beeline::class
        );
        $this->assertInstanceOf(
            ServiceInterface::class,
            $service,
            'Service should be instance of ' . ServiceInterface::class
        );

        $this->tester->expectException(SmsInvalidConfigException::class, function () {
            $config = $this->config;
            unset($config['user']);
            Yii::createObject($config);
        });

        $this->tester->expectException(SmsInvalidConfigException::class, function () {
            $config = $this->config;
            unset($config['password']);
            Yii::createObject($config);
        });

        $this->tester->expectException(InvalidConfigException::class, function () {
            $config = $this->config;
            unset($config['class']);
            Yii::createObject($config);
        });
    }

    public function testSend()
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ru_RU';
        /** @var ServiceInterface $service */
        $service = Yii::createObject($this->config);
        $this->tester->expectException(UnauthorizedException::class, function () use ($service) {
            $service->send([$this->phone], 'test');
        });
    }

    public function testMessages()
    {
        $this->tester->expectException(new SmsInvalidConfigException('Required `user` for `beeline` service.'), function () {
            $config = $this->config;
            unset($config['user']);
            Yii::createObject($config);
        });

        Yii::$app->language = 'ru';
        $this->tester->expectException(new SmsInvalidConfigException('Параметр `user` обязателен для сервиса `beeline`.'), function () {
            $config = $this->config;
            unset($config['user']);
            Yii::createObject($config);
        });
    }
}
