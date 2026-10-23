<?php

declare(strict_types=1);

namespace PrestoWorld\Modules\UserFollows\Headless;

use PrestoWorld\Modules\UserFollows\Contracts\FollowRepositoryInterface;
use PrestoWorld\Modules\UserFollows\Headless\Handler\FollowResourceHandler;
use PrestoWorld\Modules\HeadlessCMS\Contracts\ProviderInterface;
use PrestoWorld\Modules\HeadlessCMS\Resource\ResourceDefinition;

final class UserFollowsProvider implements ProviderInterface
{
    public function __construct(
        private FollowRepositoryInterface $follows,
        private string $businessId,
    ) {}

    public function namespace(): string
    {
        return 'user-follows';
    }

    public function resources(): array
    {
        return [
            'user_follows' => new ResourceDefinition(
                name: 'user_follows',
                handler: new FollowResourceHandler($this->follows, $this->businessId),
                key: 'external_id',
                operations: ['list', 'read', 'create', 'delete'],
                publicRead: true,
            ),
        ];
    }
}
