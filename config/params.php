<?php

declare(strict_types=1);

use BeastBytes\Yii\Router\Register\RegisterCommand;

return [
    'yiisoft/yii-console' => [
        'commands' => [
            'router:register' => RegisterCommand::class,
        ],
    ],
];