<?php

/**
 * Copyright (c) 2017. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace nnrudakov\sms\services\beeline;

use GuzzleHttp\Client;
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
 *
 * @see https://beeline.amega-inform.ru/support/protocol_http.php Demo login
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
     * HTTP request client.
     *
     * @var Client
     */
    private $client;
    /**
     * Phone numbers to send.
     *
     * @var array
     */
    private $phones = [];

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

        $this->client = new Client();
    }

    public function send(array $phones, $message)
    {
        $this->clearErrors();
        $this->phones = $phones;
        $payload = [
            'action'  => 'post_sms',
            'target'  => implode(',', $phones),
            'message' => mb_substr($message, 0, 480)
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
            $response = $this->client->post(self::$gatewayUrl, ['form_params' => $payload]);
        } catch (\Exception $e) {
            throw new ServiceException(500, $e->getMessage(), 0, $e);
        }

        try {
            $data = new \SimpleXMLElement($response->getBody()->__toString());
        } catch (\Exception $e) {
            throw new ServiceException(500, $e->getMessage(), 0, $e);
        }

        $this->checkErrors($data);
    }

    /**
     * Check errors in response and store it if found according to phone.
     *
     * @param \SimpleXMLElement $response Service response.
     *
     * @throws UnauthorizedException when service credentials are invalid
     */
    protected function checkErrors(\SimpleXMLElement $response)
    {
        if (!count($response->errors->children())) {
            return;
        }

        if ($response->errors && $response->errors->error->__toString() === 'User authentication failed') {
            throw new UnauthorizedException(Yii::t('sms', $response->errors->error));
        }

        /** @var \SimpleXMLElement $error */
        /** @noinspection ForeachSourceInspection */
        foreach ($response->errors->children() as $error) {
            $error_str = $error->__toString();
            $found_phone = false;
            foreach ($this->phones as $phone) {
                if (strpos($error_str, $phone) !== false) {
                    $found_phone = true;
                    $this->addError($phone, $error_str);
                    break;
                }
            }
            if (!$found_phone) {
                $this->addError(static::$otherError, $error_str);
            }
        }
    }
}
