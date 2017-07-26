<?php

/**
 * Copyright (c) 2017. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace nnrudakov\sms\services\beeline;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Yii;
use nnrudakov\sms\services\BaseService;
use nnrudakov\sms\services\exceptions\{
    InvalidConfigException as SmsInvalidConfigException, ServiceException, UnauthorizedException
};

/**
 * Beeline SMS gateway service.
 *
 * Service let to send SMS messages for mobile users.
 *
 * @package    nnrudakov\sms\services\beeline
 * @author     Nikolay Rudakov <nnrudakov@gmail.com>
 * @copyright  2017
 */
class Beeline extends BaseService
{
    /**
     * Beeline user name.
     *
     * @var string
     */
    public $user;
    /**
     * Beeline user password.
     */
    public $password;
    /**
     * Beeline sender name.
     * Will set application {@link \yii\base\Application::name} if not specified in service configuration.
     *
     * @var string
     */
    public $senderName;

    /**
     * HTTP request client.
     *
     * @var Client
     */
    private $client;

    /**
     * URL gateway.
     *
     * @var string
     */
    private static $gatewayUrl = 'https://beeline.amega-inform.ru/sendsms/';

    public function init()
    {
        parent::init();

        if (!$this->user) {
            throw new SmsInvalidConfigException(
                Yii::t('sms', 'Required `user` for `{id}` service.', ['id' => $this->getId()])
            );
        }

        if (!$this->password) {
            throw new SmsInvalidConfigException(
                Yii::t('sms', 'Required `password` for `{id}` service.', ['id' => $this->getId()])
            );
        }

        if (!$this->senderName) {
            $this->senderName = Yii::$app->name;
        }

        $this->client = new Client();
    }

    public function send(array $phones, $message)
    {
        $payload = [
            'action'  => 'post_sms',
            'target'  => implode(',', $phones),
            'message' => $message,
            'sender'  => $this->senderName
        ];

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $payload['HTTP_ACCEPT_LANGUAGE'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        }

        $this->request($payload);
    }

    /**
     * Request to gateway.
     *
     * @param array $payload Configurations.
     *
     * @throws UnauthorizedException when service credentials are invalid
     * @throws ServiceException when service return HTTP errors
     */
    protected function request(array $payload)
    {
        $payload['user'] = $this->user;
        $payload['pass'] = $this->password;
        try {
            $response = $this->client->post(self::$gatewayUrl, $payload);
        } catch (ConnectException $e) {
            throw new ServiceException(500, $e->getMessage(), 0, $e);
        }

        $data = new \SimpleXMLElement($response->getBody()->__toString());

        if ($data->errors && $data->errors->error->__toString() === 'User authentication failed') {
            throw new UnauthorizedException(Yii::t('sms', $data->errors->error));
        }
    }
}
