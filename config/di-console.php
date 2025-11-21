<?php

declare(strict_types=1);

use BeastBytes\Router\Register\Parser;
use BeastBytes\Router\Register\WriterInterface;
use BeastBytes\Yii\Router\Register\Writer;

return [
    WriterInterface::class => Writer::class,
    Parser::class => [
        '__construct()' => [
            [],
        ],
    ],
];