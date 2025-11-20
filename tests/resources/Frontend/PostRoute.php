<?php

declare(strict_types=1);

namespace BeastBytes\Yii\Router\Register\Tests\resources\Frontend;

use BeastBytes\Router\Register\Route\RouteInterface;

enum PostRoute: string implements RouteInterface
{
    public const PREFIX = 'post';

    case index = '//posts';
    case create = '/create';
    case update = '/update/{id}';
    case view = '/view/{id}';
}