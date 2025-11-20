<?php

declare(strict_types=1);

namespace BeastBytes\Yii\Router\Register\Tests\resources\Backend;

use BeastBytes\Router\Register\Route\RouteInterface;

enum PostRoute: string implements RouteInterface
{
    public const PREFIX = 'post';

    case index = '';
    case hide = '/hide/{id}';
    case view = '/view/{id}';
}