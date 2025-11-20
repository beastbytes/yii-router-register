<?php

declare(strict_types=1);

namespace BeastBytes\Yii\Router\Register\Tests\resources\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class AccessCheck
{

}