<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace tests\unit;

use _generated\UnitTesterActions;
use Codeception\Test\Unit;
use nnrudakov\sms\services\beeline\Beeline;
use nnrudakov\sms\services\exceptions\{InvalidConfigException as SmsInvalidConfigException, UnauthorizedException};
use nnrudakov\sms\services\ServiceInterface;
use ReflectionMethod;
use SimpleXMLElement;
use Yii;
use yii\base\InvalidConfigException;
use yii\i18n\PhpMessageSource;

/**
 * Beeline service tests.
 *
 * @property UnitTesterActions $tester Unit $tester.
 *
 * @package    tests\unit
 * @author     Nikolay Rudakov <nnrudakov@gmail.com>
 * @copyright  2017-2021
 *
 * @group services
 * @group beeline
 */
class BeelineServiceTest extends Unit
{
    /**
     * @var string
     */
    private static string $otherError = '<?xml version="1.0" encoding="UTF-8"?><output>
<RECEIVER AGT_ID="" DATE_REPORT="" />
<errors><error>Some error</error></errors></output>';
    /**
     * @var array
     */
    private array $config;
    /**
     * @var string
     */
    private string $phone;

    public function testCreateService(): void
    {
        /** @var ServiceInterface $service */
        $service = Yii::createObject($this->config);
        self::assertInstanceOf(
            Beeline::class,
            $service,
            'Service should be instance of ' . Beeline::class
        );
        self::assertInstanceOf(
            ServiceInterface::class,
            $service,
            'Service should be instance of ' . ServiceInterface::class
        );

        $this->tester->expectThrowable(
            SmsInvalidConfigException::class,
            function () {
                $config = $this->config;
                unset($config['user']);
                Yii::createObject($config);
            }
        );

        $this->tester->expectThrowable(
            SmsInvalidConfigException::class,
            function () {
                $config = $this->config;
                unset($config['password']);
                Yii::createObject($config);
            }
        );

        $this->tester->expectThrowable(
            InvalidConfigException::class,
            function () {
                $config = $this->config;
                unset($config['class']);
                Yii::createObject($config);
            }
        );
    }

    public function testMessages(): void
    {
        $this->tester->expectThrowable(
            new SmsInvalidConfigException('Required `user` for `beeline` service.'),
            function () {
                $config = $this->config;
                unset($config['user']);
                Yii::createObject($config);
            }
        );

        Yii::$app->language = 'ru';
        $this->tester->expectThrowable(
            new SmsInvalidConfigException('Параметр `user` обязателен для сервиса `beeline`.'),
            function () {
                $config = $this->config;
                unset($config['user']);
                Yii::createObject($config);
            }
        );
    }

    public function testSend(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ru_RU';
        /** @var ServiceInterface $service */
        $service = Yii::createObject($this->config);
        self::assertTrue($service->send([$this->phone], 'test message ' . mt_rand()));
        self::assertFalse($service->hasErrors());
    }

    public function testServiceErrors(): void
    {
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ru_RU';
        $message = 'test message ' . mt_rand();

        // invalid user
        $user = $this->config['user'];
        $this->config['user'] = 'wrong_user';
        /** @var ServiceInterface $service */
        $service = Yii::createObject($this->config);
        $this->tester->expectThrowable(
            UnauthorizedException::class,
            function () use ($service, $message) {
                $service->send([$this->phone], $message);
            }
        );
        $this->config['user'] = $user;

        // good send
        $service = Yii::createObject($this->config);
        $service->send([$this->phone], $message);
        self::assertFalse($service->hasErrors());

        // duplicate send
        $service->send([$this->phone], $message);
        self::assertTrue($service->hasErrors());
        self::assertNotEmpty($service->getErrors());

        // invalid number
        $service->send(['invalid_number'], $message);
        self::assertTrue($service->hasErrors());
        self::assertEquals('Неправильный номер телефона : invalid_number', $service->getErrors('invalid_number'));
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en_US';
        $service->send(['invalid_number'], $message);
        self::assertTrue($service->hasErrors());
        self::assertEquals('Invalid phone number : invalid_number', $service->getErrors('invalid_number'));

        // multiple errors
        $service->send(['invalid_number', $this->phone], $message);
        self::assertTrue($service->hasErrors());
        self::assertEquals('Invalid phone number : invalid_number', $service->getErrors('invalid_number'));
        self::assertEquals(
            'You cannot send the same message to `' . $this->phone . '` during 20 minutes.',
            $service->getErrors($this->phone)
        );

        // other errors
        $checkErrors = new ReflectionMethod($service, 'checkErrors');
        $checkErrors->setAccessible(true);
        $checkErrors->invoke($service, new SimpleXMLElement(static::$otherError));
        self::assertTrue($service->hasErrors());
        self::assertNotEmpty($service->getErrors('otherError'));
    }

    protected function _before(): void
    {
        parent::_before();
        Yii::$app->getI18n()->translations['sms'] = [
            'class' => PhpMessageSource::class,
            'basePath' => codecept_root_dir() . '/src/messages',
            'sourceLanguage' => 'en-US',
        ];
        /** @noinspection PhpIncludeInspection */
        $config = require codecept_data_dir() . 'config/config.php';
        $this->config = $config['components']['sms']['services']['beeline'];
        $this->config['id'] = 'beeline';
        $this->phone = $this->config['phone'];
        unset($this->config['phone']);
    }
}
