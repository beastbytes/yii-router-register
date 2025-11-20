<?php

declare(strict_types=1);

return <<<'BACKEND'
<?php

declare(strict_types=1);

use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

return [
    Group::create()
        ->namePrefix('app.')
        ->routes(
            Route::methods(['GET'], '')
                ->name('index')
                ->action(['BeastBytes\Yii\Router\Register\Tests\resources\Backend\AppController', 'index'])
            ,
            Route::methods(['GET'], '/login')
                ->name('login')
                ->middleware('BeastBytes\Yii\Router\Register\Tests\resources\Middleware\IsGuest')
                ->action(['BeastBytes\Yii\Router\Register\Tests\resources\Backend\AppController', 'login'])
            ,
            Route::methods(['GET'], '/logout')
                ->name('logout')
                ->middleware('BeastBytes\Yii\Router\Register\Tests\resources\Middleware\IsLoggedIn')
                ->action(['BeastBytes\Yii\Router\Register\Tests\resources\Backend\AppController', 'logout'])
            ,
        )
    ,
    Group::create('/comment')
        ->namePrefix('comment.')
        ->middleware('BeastBytes\Yii\Router\Register\Tests\resources\Middleware\IsLoggedIn')
        ->routes(
            Route::methods(['POST'], '/hide/{id:[1-9]\d*}')
                ->name('hide')
                ->middleware(fn(BeastBytes\Yii\Router\Register\Tests\resources\Middleware\AccessChecker $checker)=>$checker->withPermission('backend.comment.hide')->withRedirectRoute('backend.app.index'))
                ->action(['BeastBytes\Yii\Router\Register\Tests\resources\Backend\CommentController', 'hide'])
            ,
        )
    ,
    Group::create('/post')
        ->namePrefix('post.')
        ->middleware('BeastBytes\Yii\Router\Register\Tests\resources\Middleware\IsLoggedIn')
        ->routes(
            Route::methods(['GET'], '')
                ->name('index')
                ->action(['BeastBytes\Yii\Router\Register\Tests\resources\Backend\PostController', 'index'])
            ,
            Route::methods(['GET', 'POST'], '/hide/{id:[1-9]\d*}')
                ->name('hide')
                ->middleware(fn(BeastBytes\Yii\Router\Register\Tests\resources\Middleware\AccessChecker $checker)=>$checker->withPermission('backend.post.hide')->withRedirectRoute('backend.app.index'))
                ->action(['BeastBytes\Yii\Router\Register\Tests\resources\Backend\PostController', 'hide'])
            ,
            Route::methods(['GET'], '/view/{id:[1-9]\d*}')
                ->name('view')
                ->disableMiddleware('BeastBytes\Yii\Router\Register\Tests\resources\Middleware\GroupLevelMiddleware')
                ->action(['BeastBytes\Yii\Router\Register\Tests\resources\Backend\PostController', 'view'])
            ,
        )
    ,
];
BACKEND;