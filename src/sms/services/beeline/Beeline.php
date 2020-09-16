<?php

/**
 * Copyright (c) 2017-2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace nnrudakov\sms\services\beeline;

use Exception;
use GuzzleHttp\Client;
use nnrudakov\sms\services\BaseService;
use nnrudakov\sms\services\exceptions\{InvalidConfigException as SmsInvalidConfigException,
    ServiceException,
    UnauthorizedException
};
use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;
use Yii;

use function count;

/**
 * Beeline SMS gateway service.
 *
 * Service let to send SMS messages for mobile users.
 *
 * @package    nnrudakov\sms\services\beeline
 * @author     Nikolay Rudakov <nnrudakov@gmail.com>
 * @copyright  2017-2020
 *
 * @see https://beeline.amega-inform.ru/support/protocol_http.php Demo login
 */
class Beeline extends BaseService
{
    /**
     * @var string URL gateway.
     */
    protected static $gatewayUrl = 'https://beeline.amega-inform.ru/sendsms2/';
    /**
     * @var string Beeline user name.
     */
    public $user;
    /**
     * @var string Beeline user password.
     */
    public $password;
    /**
     * @var Client HTTP request client.
     */
    protected $client;
    /**
     * @var array Phone numbers to send.
     */
    protected $phones = [];

    /**
     * @inheritdoc
     *
     * @throws SmsInvalidConfigException
     */
    public function init(): void
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

    public function send(array $phones, $message): bool
    {
        $this->clearErrors();
        $this->phones = $phones;
        $payload = [
            'action' => 'post_sms',
            'target' => implode(',', $phones),
            'message' => mb_substr($message, 0, 480)
        ];

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $payload['HTTP_ACCEPT_LANGUAGE'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        }

        $this->request($payload);

        return true;
    }

    /**
     * Request to gateway.
     *
     * @param array $payload Configurations.
     *
     * @throws UnauthorizedException when service credentials are invalid
     * @throws ServiceException when service return HTTP errors
     */
    protected function request(array $payload): void
    {
        $payload['user'] = $this->user;
        $payload['pass'] = $this->password;

        $response = $this->getResponse(['form_params' => $payload]);
        $data = $this->getResponseData($response);
        $this->checkErrors($data);
    }

    /**
     * Returns query response.
     *
     * @param array $params Request parameters.
     *
     * @return ResponseInterface
     *
     * @throws ServiceException
     */
    protected function getResponse(array $params): ResponseInterface
    {
        try {
            return $this->client->post(self::$gatewayUrl, $params);
        } catch (Exception $e) {
            throw new ServiceException(500, $e->getMessage(), 0, $e);
        }
    }

    /**
     * Retunrs response data.
     *
     * @param ResponseInterface $response Response object.
     *
     * @return SimpleXMLElement
     *
     * @throws ServiceException
     */
    protected function getResponseData(ResponseInterface $response): SimpleXMLElement
    {
        try {
            return new SimpleXMLElement($response->getBody()->__toString());
        } catch (Exception $e) {
            throw new ServiceException(500, $e->getMessage(), 0, $e);
        }
    }

    /**
     * Check errors in response and store it if found according to phone.
     *
     * @param SimpleXMLElement $response Service response.
     *
     * @throws UnauthorizedException when service credentials are invalid
     */
    protected function checkErrors(SimpleXMLElement $response): void
    {
        if (!count($response->errors->children())) {
            return;
        }

        if ($response->errors && $response->errors->error->__toString() === 'User authentication failed') {
            throw new UnauthorizedException(Yii::t('sms', $response->errors->error));
        }

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
