<?php

declare(strict_types=1);

namespace BeastBytes\Yii\Router\Register;

use BeastBytes\Router\Register\DTO\Group as DtoGroup;
use BeastBytes\Router\Register\DTO\Route as DtoRoute;
use BeastBytes\Router\Register\WriterInterface;
use RuntimeException;
use Yiisoft\Router\Group;
use Yiisoft\Router\Route;

final class Writer implements WriterInterface
{
    private const FILE = <<<FILE
<?php

declare(strict_types=1);

%s

return [
%s
];
FILE;

    private const ACTION = 'action(%s)';
    private const CORS = 'withCors(%s)';
    private const DEFAULTS = 'defaults(%s)';
    private const DISABLE_MIDDLEWARE = 'disableMiddleware(%s)';
    private const GROUP = "Group::create(%s)";
    private const GROUP_ROUTES = "routes(...(require __DIR__ . '/routes/%s.php'))";
    private const HOST = "host('%s')";
    private const HOSTS = "hosts(...['%s'])";
    private const MIDDLEWARE = 'middleware(%s)';
    private const NAME = "name('%s')";
    private const NAME_PREFIX = "namePrefix('%s.')";
    private const OVERRIDE = 'override()';
    private const ROUTE = "Route::methods(['%s'], '%s')";
    private const ROUTES = "routes(%s)";
    private const WRITE_FAILED = 'Failed to write `%s`';

    private const DUPLICATE_FALLBACK = 'Duplicate Fallback route in `%s` group';
    private const GROUP_FILENAME = '%s' . DIRECTORY_SEPARATOR . 'routes'  . DIRECTORY_SEPARATOR . '%s.php';
    private const GROUPS_FILENAME = '%s' . DIRECTORY_SEPARATOR . 'groups.php';
    private const USE = 'use %s;';
    private const INDENT = '    ';

    private const TOP_LEVEL = true;

    private ?string $fallback;
    private string $path;

    public function write(array $groups, string $path): void
    {
        $this->ensurePath($path);

        $level = 1;
        $grps = [];

        foreach ($groups as $group) {
            $grps[] = $this->createGroup($group, $level, self::TOP_LEVEL);
            $this->writeGroup($group, $level);
        }

        $filename = sprintf(self::GROUPS_FILENAME, $this->path);
        $use[] = sprintf(self::USE, Group::class);
        $indent = str_repeat(self::INDENT, $level);
        $result = file_put_contents(
            $filename,
            sprintf(
                self::FILE,
                implode("\n", $use),
                $indent . implode("\n$indent,\n$indent", $grps) . "\n$indent,"
            ),
            LOCK_EX
        );

        if ($result === false) {
            throw new RuntimeException(sprintf(self::WRITE_FAILED, $filename));
        }
    }

    private function ensurePath(string $path): void
    {
        $this->path = $path;

        if (!is_dir($path)) {
            mkdir($path);
        }

        if (!is_dir($path . DIRECTORY_SEPARATOR . 'routes')) {
            mkdir($path . DIRECTORY_SEPARATOR . 'routes');
        }
    }

    private function writeGroup(DtoGroup $group, int $level): void
    {
        $this->fallback = null;
        $routes = [];

        foreach ($group->getRoutes() as $route) {
            if ($route instanceof DtoRoute) {
                if ($route->isFallback()) {
                    if ($this->fallback === null) {
                        $this->fallback = $this->createRoute($route, $level);
                    } else {
                        throw new RuntimeException(
                            sprintf(self::DUPLICATE_FALLBACK, $group->getName())
                        );
                    }
                } else {
                    $routes[] = $this->createRoute($route, $level);
                }
            } elseif ($route instanceof DtoGroup) {
                $routes[] = $this->createGroup($route, $level);
            } else {
                exit;
            }
        }

        if ($this->fallback !== null) {
            $routes[] = $this->fallback;
        }


        $filename = sprintf(self::GROUP_FILENAME, $this->path, $group->getName());
        $use[] = sprintf(self::USE, Group::class);
        $use[] = sprintf(self::USE, Route::class);
        $indent = str_repeat(self::INDENT, $level);
        $result = file_put_contents(
            $filename,
            sprintf(
                self::FILE,
                implode("\n", $use),
                $indent . implode("\n$indent", $routes)
            ),
            LOCK_EX
        );

        if ($result === false) {
            throw new RuntimeException(sprintf(self::WRITE_FAILED, $filename));
        }
    }

    private function createGroup(DtoGroup $group, int $level, bool $topLevel = false): string
    {
        $grp[] = sprintf(
            self::GROUP,
            $group->getPrefix() === null ? '' : "'" . $group->getPrefix() . "'"
        );
        $grp[] = sprintf(self::NAME_PREFIX, $group->getName());

        if ($group->hasHosts()) {
            $hosts = $group->getHosts();
            $grp[] = sizeof($hosts) === 1
                ? sprintf(self::HOST, $hosts[0])
                : sprintf(self::HOSTS, implode("', '", $hosts))
            ;
        }

        if ($group->hasCors()) {
            $grp[] = sprintf(self::CORS, $group->getCors()->getMiddleware());
        }

        foreach ($group->getMiddlewares() as $middleware) {
            $grp[] = sprintf(self::MIDDLEWARE, $middleware->getMiddleware());
        }

        foreach ($group->getDisableMiddlewares() as $middleware) {
            $grp[] = sprintf(self::DISABLE_MIDDLEWARE, $middleware->getMiddleware());
        }

        if ($topLevel) {
            $grp[] = sprintf(self::GROUP_ROUTES, $group->getName());
        } else {
            $routes = [];

            foreach ($group->getRoutes() as $route) {
                if ($route->isFallback()) {
                    if ($this->fallback === null) {
                        $this->fallback = $this->createRoute($route, $level);
                    } else {
                        throw new RuntimeException(
                            sprintf(self::DUPLICATE_FALLBACK, $group->getName())
                        );
                    }
                } else {
                    $routes[] = $this->createRoute($route, $level + 2);
                }
            }

            ;
            $grp[] = sprintf(
                self::ROUTES,
                "\n" . str_repeat(self::INDENT, $level + 2)
                    . implode("\n" . str_repeat(self::INDENT, $level + 2), $routes)
                    . "\n" . str_repeat(self::INDENT, $level + 1)
            ) . "\n" . str_repeat(self::INDENT, $level) . ',';
        }

        $indent = str_repeat(self::INDENT, $level + 1);
        return implode("\n$indent->", $grp);
    }

    private function createRoute(DtoRoute $route, int $level): string
    {
        $rt = [];

        $rt[] = sprintf(
            self::ROUTE,
            implode("', '", $route->getMethods()),
            $route->getPattern()
        );
        if ($route->hasDefaultValues()) {
            $rt[] = sprintf(self::DEFAULTS, implode("', '", $route->getDefaultValues()));
        }

        $rt[] = sprintf(self::NAME, $route->getName());

        if ($route->isOverride()) {
            $rt[] = self::OVERRIDE;
        }

        if ($route->hasHosts()) {
            $hosts = $route->getHosts();
            $rt[] = sizeof($hosts) === 1
                ? sprintf(self::HOST, $hosts[0])
                : sprintf(self::HOSTS, implode("', '", $hosts))
            ;
        }

        foreach ($route->getMiddlewares() as $middleware) {
            $rt[] = sprintf(self::MIDDLEWARE, $middleware->getMiddleware());
        }

        foreach ($route->getDisableMiddlewares() as $middleware) {
            $rt[] = sprintf(self::DISABLE_MIDDLEWARE, $middleware->getMiddleware());
        }

        $rt[] = sprintf(self::ACTION, $route->getAction()->getMiddleware());

        return implode("\n" . str_repeat(self::INDENT, $level + 1) . '->', $rt)
            . "\n" . str_repeat(self::INDENT, $level) . ','
        ;
    }
}