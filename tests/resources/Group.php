<?php

declare(strict_types=1);

namespace BeastBytes\Yii\Router\Register\Tests\resources;

use BeastBytes\Router\Register\Route\GroupInterface;
use BeastBytes\Router\Register\Route\GroupTrait;

enum Group: string implements GroupInterface
{
    use GroupTrait;

    case frontend = '';
    case backend = 'admin';
}
