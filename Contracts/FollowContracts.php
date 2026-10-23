<?php

declare(strict_types=1);

namespace PrestoWorld\Modules\UserFollows\Contracts;

interface FollowRepositoryInterface
{
    public function follow(string $businessId, string $followerId, string $followingId): array;
    public function unfollow(string $businessId, string $followerId, string $followingId): bool;
    public function isFollowing(string $businessId, string $followerId, string $followingId): bool;
    public function followers(string $businessId, string $userId, int $offset, int $size): array;
    public function following(string $businessId, string $userId, int $offset, int $size): array;
    public function followerCount(string $businessId, string $userId): int;
    public function followingCount(string $businessId, string $userId): int;
}
