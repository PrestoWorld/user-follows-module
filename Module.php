<?php

declare(strict_types=1);

namespace PrestoWorld\Modules\UserFollows;

use Cycle\Database\DatabaseInterface;
use PrestoWorld\Foundation\Admin\DashboardSubsystem;
use PrestoWorld\Modules\UserFollows\Contracts\FollowRepositoryInterface;
use PrestoWorld\Modules\UserFollows\Headless\UserFollowsProvider;
use PrestoWorld\Modules\UserFollows\Storage\UserFollows\FollowRepository;
use Witals\Framework\Application;
use Witals\Framework\Module\Module as WitalsModule;

class Module extends WitalsModule
{
    public function __construct(
        protected Application $app,
        protected string $path = '',
        protected array $metadata = [],
    ) {
        if ($path === '') {
            $path = __DIR__;
        }
        if ($metadata === []) {
            $metadata = ['name' => 'user-follows'];
        }
        parent::__construct($app, $path, $metadata);
    }

    public function register(): void
    {
        $this->bindRepositories();

        $this->app->singleton(UserFollowsProvider::class, function (Application $app): UserFollowsProvider {
            return new UserFollowsProvider(
                $this->resolve($app, FollowRepositoryInterface::class),
                $this->headlessBusinessId($app),
            );
        });
    }

    public function boot(): void
    {
        DashboardSubsystem::register(
            $this->app,
            'user-follows',
            'User Follows',
            'Quan hệ follow giữa người dùng',
            'UserGroup',
            15,
            'content',
        );
    }

    public function getName(): string { return 'UserFollows'; }

    private function bindRepositories(): void
    {
        $prefix = fn (Application $app): string => (string) ($app->config('user-follows.table_prefix', 'pw_') ?: 'pw_');
        $db = fn (Application $app): DatabaseInterface => $this->resolveDatabase($app);

        $this->app->singleton(FollowRepositoryInterface::class, fn (Application $app) => new FollowRepository($db($app), $prefix($app)));
    }

    private function headlessBusinessId(Application $app): string
    {
        return (string) ($app->config('user-follows.headless.business_id', ''));
    }

    private function resolve(Application $app, string $abstract): object
    {
        $instance = $app->make($abstract);
        if (!$instance instanceof $abstract) {
            throw new \RuntimeException(sprintf('%s is not bound in the container.', $abstract));
        }
        return $instance;
    }

    private function resolveDatabase(Application $app): DatabaseInterface
    {
        $db = $app->make(DatabaseInterface::class);
        if (!$db instanceof DatabaseInterface) {
            throw new \RuntimeException('DatabaseInterface is not bound in the container.');
        }
        return $db;
    }
}
