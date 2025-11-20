<?php

declare(strict_types=1);

use Yiisoft\Router\Group;

return [
    Group::create()
        ->namePrefix('frontend.')
        ->routes(...(require __DIR__ . '/routes/frontend.php'))
    ,
    Group::create('/admin')
        ->namePrefix('backend.')
        ->host('https://example.com')
        ->withCors('BeastBytes\Yii\Router\Register\Tests\resources\Middleware\CorsMiddleware')
        ->middleware('BeastBytes\Yii\Router\Register\Tests\resources\Middleware\IsAdmin')
        ->routes(...(require __DIR__ . '/routes/backend.php'))
    ,
];