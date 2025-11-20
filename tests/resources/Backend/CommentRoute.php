<?php

declare(strict_types=1);

namespace BeastBytes\Yii\Router\Register\Tests\resources\Backend;

use BeastBytes\Router\Register\Route\RouteInterface;

enum CommentRoute: string implements RouteInterface
{
    public const PREFIX = 'post-comment';

    case hide = '/hide/{id}';
}