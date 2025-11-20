<?php

declare(strict_types=1);

use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    Group::create()
        ->namePrefix('app.')
        ->routes(
            Route::methods(['GET'], '/login')
                ->name('login')
                ->middleware('BeastBytes\Yii\Router\Register\Tests\resources\Middleware\IsGuest')
                ->action(['BeastBytes\Yii\Router\Register\Tests\resources\Frontend\AppController', 'login'])
            ,
            Route::methods(['GET'], '/logout')
                ->name('logout')
                ->middleware('BeastBytes\Yii\Router\Register\Tests\resources\Middleware\IsLoggedIn')
                ->action(['BeastBytes\Yii\Router\Register\Tests\resources\Frontend\AppController', 'logout'])
            ,
        )
    ,
    Route::methods(['GET'], '/posts')
        ->name('post.index')
        ->action(['BeastBytes\Yii\Router\Register\Tests\resources\Frontend\PostController', 'index'])
    ,
    Group::create('/post-comment')
        ->namePrefix('post-comment.')
        ->middleware('BeastBytes\Yii\Router\Register\Tests\resources\Middleware\IsLoggedIn')
        ->routes(
            Route::methods(['POST'], '/create/{postId:[1-9]\d*}')
                ->name('create')
                ->action(['BeastBytes\Yii\Router\Register\Tests\resources\Frontend\CommentController', 'create'])
            ,
        )
    ,
    Group::create('/post')
        ->namePrefix('post.')
        ->middleware('BeastBytes\Yii\Router\Register\Tests\resources\Middleware\IsLoggedIn')
        ->routes(
            Route::methods(['GET', 'POST'], '/create')
                ->name('create')
                ->middleware(fn(BeastBytes\Yii\Router\Register\Tests\resources\Middleware\AccessChecker $checker)=>$checker->withPermission('frontend.post.create')->withRedirectRoute('frontend.app.index'))
                ->action(['BeastBytes\Yii\Router\Register\Tests\resources\Frontend\PostController', 'create'])
            ,
            Route::methods(['GET', 'POST'], '/update/{id:[1-9]\d*}')
                ->name('update')
                ->middleware(fn(BeastBytes\Yii\Router\Register\Tests\resources\Middleware\AccessChecker $checker)=>$checker->withPermission('frontend.post.update')->withRedirectRoute('frontend.app.index'))
                ->action(['BeastBytes\Yii\Router\Register\Tests\resources\Frontend\PostController', 'update'])
            ,
            Route::methods(['GET'], '/view/{id:[1-9]\d*}')
                ->name('view')
                ->disableMiddleware('BeastBytes\Yii\Router\Register\Tests\resources\Middleware\IsLoggedIn')
                ->action(['BeastBytes\Yii\Router\Register\Tests\resources\Frontend\PostController', 'view'])
            ,
        )
    ,
    Route::methods(['GET'], '/')
        ->name('app.index')
        ->action(['BeastBytes\Yii\Router\Register\Tests\resources\Frontend\AppController', 'index'])
    ,
];