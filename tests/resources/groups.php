<?php

declare(strict_types=1);

use BeastBytes\Router\Register\Attribute\Fallback;
use BeastBytes\Router\Register\Attribute\GroupCors;
use BeastBytes\Router\Register\Attribute\GroupHost;
use BeastBytes\Router\Register\Attribute\GroupMiddleware;
use BeastBytes\Router\Register\Attribute\Method\Get;
use BeastBytes\Router\Register\Attribute\Method\GetPost;
use BeastBytes\Router\Register\Attribute\Method\Post;
use BeastBytes\Router\Register\Attribute\Middleware;
use BeastBytes\Router\Register\Attribute\Parameter\Id;
use BeastBytes\Router\Register\DTO\Group;
use BeastBytes\Router\Register\DTO\Route as Route;
use BeastBytes\Yii\Router\Register\Tests\resources\Middleware\CorsMiddleware;
use BeastBytes\Yii\Router\Register\Tests\resources\Middleware\GroupLevelMiddleware;
use BeastBytes\Yii\Router\Register\Tests\resources\Backend\AppController as BackendAppController;
use BeastBytes\Yii\Router\Register\Tests\resources\Backend\AppRoute as BackendAppRoute;
use BeastBytes\Yii\Router\Register\Tests\resources\Backend\CommentController as BackendCommentController;
use BeastBytes\Yii\Router\Register\Tests\resources\Backend\CommentRoute as BackendCommentRoute;
use BeastBytes\Yii\Router\Register\Tests\resources\Backend\PostController as BackendPostController;
use BeastBytes\Yii\Router\Register\Tests\resources\Backend\PostRoute as BackendPostRoute;
use BeastBytes\Yii\Router\Register\Tests\resources\Frontend\AppController as FrontendAppController;
use BeastBytes\Yii\Router\Register\Tests\resources\Frontend\AppRoute as FrontendAppRoute;
use BeastBytes\Yii\Router\Register\Tests\resources\Frontend\CommentController as FrontendCommentController;
use BeastBytes\Yii\Router\Register\Tests\resources\Frontend\CommentRoute as FrontendCommentRoute;
use BeastBytes\Yii\Router\Register\Tests\resources\Frontend\PostController as FrontendPostController;
use BeastBytes\Yii\Router\Register\Tests\resources\Frontend\PostRoute as FrontendPostRoute;
use BeastBytes\Yii\Router\Register\Tests\resources\Middleware\AccessChecker;
use BeastBytes\Yii\Router\Register\Tests\resources\Middleware\IsAdmin;
use BeastBytes\Yii\Router\Register\Tests\resources\Middleware\IsGuest;
use BeastBytes\Yii\Router\Register\Tests\resources\Middleware\IsLoggedIn;

$frontendAppGroup = Group::create('app');
$frontendPostGroup = Group::create(FrontendPostRoute::PREFIX, '/' . FrontendPostRoute::PREFIX);

return [
    Group::create('frontend')
        ->route(
            $frontendAppGroup
                ->route(
                    Route::create(new Get(FrontendAppRoute::index))
                        ->group($frontendAppGroup)
                        ->action(new Middleware([FrontendAppController::class, 'index']))
                        ->fallback(new Fallback())
                )
                ->route(
                    Route::create(new Get(FrontendAppRoute::login))
                        ->middlewares([new Middleware(IsGuest::class)])
                        ->action(new Middleware([FrontendAppController::class, 'login']))
                )
                ->route(
                    Route::create(new Get(FrontendAppRoute::logout))
                        ->middlewares([new Middleware(IsLoggedIn::class)])
                        ->action(new Middleware([FrontendAppController::class, 'logout']))
                )
        )
        ->route(
            Route::create(new Get(FrontendPostRoute::index))
                ->group($frontendPostGroup)
                ->action(new Middleware([FrontendPostController::class, 'index']))
        )
        ->route(
            Group::create(FrontendCommentRoute::PREFIX, '/' . FrontendCommentRoute::PREFIX)
                ->middlewares([new GroupMiddleware(IsLoggedIn::class)])
                ->route(
                    Route::create(new Post(FrontendCommentRoute::create))
                        ->parameters([new Id('postId')])
                        ->action(new Middleware([FrontendCommentController::class, 'create']))
                )
        )
        ->route(
            $frontendPostGroup
                ->middlewares([new GroupMiddleware(IsLoggedIn::class)])
                ->route(
                    Route::create(new GetPost(FrontendPostRoute::create))
                        ->parameters([new Id('id')])
                        ->middlewares([
                            new Middleware(
                                "fn(BeastBytes\Yii\Router\Register\Tests\\resources\Middleware\AccessChecker \$checker)=>\$checker->withPermission('frontend.post.create')->withRedirectRoute('frontend.app.index')"
                            )
                        ])
                        ->action(new Middleware([FrontendPostController::class, 'create']))
                )
                ->route(
                    Route::create(new GetPost(FrontendPostRoute::update))
                        ->parameters([new Id('id')])
                        ->middlewares([
                            new Middleware(
                                "fn(BeastBytes\Yii\Router\Register\Tests\\resources\Middleware\AccessChecker \$checker)=>\$checker->withPermission('frontend.post.update')->withRedirectRoute('frontend.app.index')"
                            )
                        ])
                        ->action(new Middleware([FrontendPostController::class, 'update']))
                )
                ->route(
                    Route::create(new Get(FrontendPostRoute::view))
                        ->parameters([new Id('id')])
                        ->middlewares([new Middleware(IsLoggedIn::class, Middleware::DISABLE)])
                        ->action(new Middleware([FrontendPostController::class, 'view']))
                )
        )
    ,
    Group::create('backend', '/admin')
        ->hosts([new GroupHost('https://example.com')])
        ->cors(new GroupCors(CorsMiddleware::class))
        ->middlewares([new GroupMiddleware(IsAdmin::class)])
        ->route(
            Group::create('app')
                ->route(
                    Route::create(new Get(BackendAppRoute::index))
                        ->action(new Middleware([BackendAppController::class, 'index']))
                )
                ->route(
                    Route::create(new Get(BackendAppRoute::login))
                        ->middlewares([new Middleware(IsGuest::class)])
                        ->action(new Middleware([BackendAppController::class, 'login']))
                )
                ->route(
                    Route::create(new Get(BackendAppRoute::logout))
                        ->middlewares([new Middleware(IsLoggedIn::class)])
                        ->action(new Middleware([BackendAppController::class, 'logout']))
                )
        )
        ->route(
            Group::create('comment', '/comment')
                ->middlewares([new Middleware(IsLoggedIn::class)])
                ->route(
                    Route::create(new Post(BackendCommentRoute::hide))
                        ->parameters([new Id('id')])
                        ->middlewares([
                            new Middleware('fn(' . AccessChecker::class . " \$checker)=>\$checker->withPermission('backend.comment.hide')->withRedirectRoute('backend.app.index')")
                        ])
                        ->action(new Middleware([BackendCommentController::class, 'hide']))
                )
        )
        ->route(
            Group::create('post', '/post')
                ->middlewares([new Middleware(IsLoggedIn::class)])
                ->route(
                    Route::create(new Get(BackendPostRoute::index))
                        ->action(new Middleware([BackendPostController::class, 'index']))
                )
                ->route(
                    Route::create(new GetPost(BackendPostRoute::hide))
                        ->parameters([new Id('id')])
                        ->middlewares([
                                new Middleware('fn(' . AccessChecker::class . " \$checker)=>\$checker->withPermission('backend.post.hide')->withRedirectRoute('backend.app.index')")
                        ])
                        ->action(new Middleware([BackendPostController::class, 'hide']))
                )
                ->route(
                    Route::create(new Get(BackendPostRoute::view))
                        ->parameters([new Id('id')])
                        ->middlewares([
                            new GroupMiddleware(
                                GroupLevelMiddleware::class,
                                Middleware::DISABLE
                            )
                        ])
                        ->action(new Middleware([BackendPostController::class, 'view']))
                )
        )
];