<?php

declare(strict_types=1);

use BeastBytes\Router\Register\RegisterCommand;

return [
    'yiisoft/yii-console' => [
        'commands' => [
            'router:register' => RegisterCommand::class,
        ],
    ],
];