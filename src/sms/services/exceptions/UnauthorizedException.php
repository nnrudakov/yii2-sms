<?php

/**
 * Copyright (c) 2017. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace nnrudakov\sms\services\exceptions;

use yii\web\UnauthorizedHttpException;

/**
 * SMS service authorization exception.
 *
 * @package    nnrudakov\sms\services\exceptions
 * @author     Nikolay Rudakov <nnrudakov@gmail.com>
 * @copyright  2017
 */
class UnauthorizedException extends UnauthorizedHttpException
{
    //
}
