<?php

declare(strict_types=1);

namespace BeastBytes\Yii\Router\Register\Tests\resources\Frontend;

use BeastBytes\Router\Register\Route\RouteInterface;

enum CommentRoute: string implements RouteInterface
{
    public const PREFIX = 'post-comment';

    case create = '/create/{postId}';
}