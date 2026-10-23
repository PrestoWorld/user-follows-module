<?php

declare(strict_types=1);

namespace PrestoWorld\Modules\UserFollows\Headless\Handler;

use PrestoWorld\Modules\HeadlessCMS\Http\Query;
use PrestoWorld\Modules\UserFollows\Contracts\FollowRepositoryInterface;
use PrestoWorld\Modules\HeadlessCMS\Contracts\ResourceHandlerInterface;

final class FollowResourceHandler implements ResourceHandlerInterface
{
    public function __construct(
        private FollowRepositoryInterface $repository,
        private string $businessId,
    ) {}

    public function list(Query $query): array
    {
        $userId = $query->filters['user_id'] ?? '';
        $direction = $query->filters['direction'] ?? 'following';
        if ($userId === '') {
            return ['items' => [], 'total' => 0];
        }
        $result = $direction === 'followers'
            ? $this->repository->followers($this->businessId, $userId, (int) $query->offset, (int) $query->limit)
            : $this->repository->following($this->businessId, $userId, (int) $query->offset, (int) $query->limit);

        return [
            'items' => $result,
            'total' => count($result),
        ];
    }

    public function read(string $id): ?array
    {
        return null;
    }

    public function create(array $data): array
    {
        return $this->repository->follow(
            $this->businessId,
            $data['follower_id'] ?? '',
            $data['following_id'] ?? '',
        );
    }

    public function update(string $id, array $data): array
    {
        return $this->create($data);
    }

    public function delete(string $id): bool
    {
        $parts = explode('_', $id);
        return $this->repository->unfollow($this->businessId, $parts[0], $parts[1]);
    }
}
