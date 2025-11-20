<?php

declare(strict_types=1);

namespace BeastBytes\Yii\Router\Register\Tests\resources\Backend;

use BeastBytes\Router\Register\Route\RouteInterface;

enum AppRoute: string implements RouteInterface
{
    case index = '';
    case login = '/login';
    case logout = '/logout';
}