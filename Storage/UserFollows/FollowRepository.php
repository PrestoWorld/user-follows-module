<?php

declare(strict_types=1);

namespace PrestoWorld\Modules\UserFollows\Storage\UserFollows;

use PrestoWorld\Modules\UserFollows\Contracts\FollowRepositoryInterface;
use PrestoWorld\Modules\UserFollows\Storage\AbstractRepository;

final class FollowRepository extends AbstractRepository implements FollowRepositoryInterface
{
    public function follow(string $businessId, string $followerId, string $followingId): array
    {
        if ($followerId === $followingId) {
            return ['success' => false, 'error' => 'Không thể tự follow mình'];
        }

        $externalId = 'follow_' . bin2hex(random_bytes(8));
        $now = time();

        $existing = $this->db->select('id')->from($this->table('user_follows'))
            ->where('business_id', $businessId)
            ->where('follower_id', $followerId)
            ->where('following_id', $followingId)
            ->run()->fetch();

        if ($existing === false) {
            $this->db->insert($this->table('user_follows'))->values([
                'external_id' => $externalId,
                'business_id' => $businessId,
                'follower_id' => $followerId,
                'following_id' => $followingId,
                'created_at' => $now,
            ])->run();
        }

        return ['success' => true, 'action' => 'followed'];
    }

    public function unfollow(string $businessId, string $followerId, string $followingId): bool
    {
        $result = $this->db->delete($this->table('user_follows'))
            ->where('business_id', $businessId)
            ->where('follower_id', $followerId)
            ->where('following_id', $followingId)
            ->run();
        return $result > 0;
    }

    public function isFollowing(string $businessId, string $followerId, string $followingId): bool
    {
        $row = $this->db->select('id')->from($this->table('user_follows'))
            ->where('business_id', $businessId)
            ->where('follower_id', $followerId)
            ->where('following_id', $followingId)
            ->run()->fetch();
        return $row !== false;
    }

    public function followers(string $businessId, string $userId, int $offset, int $size): array
    {
        $rows = $this->db->select('*')->from($this->table('user_follows'))
            ->where('business_id', $businessId)
            ->where('following_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->offset($offset)
            ->limit($size)
            ->run()->fetchAll();
        return $this->rows($rows);
    }

    public function following(string $businessId, string $userId, int $offset, int $size): array
    {
        $rows = $this->db->select('*')->from($this->table('user_follows'))
            ->where('business_id', $businessId)
            ->where('follower_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->offset($offset)
            ->limit($size)
            ->run()->fetchAll();
        return $this->rows($rows);
    }

    public function followerCount(string $businessId, string $userId): int
    {
        return (int) $this->db->select('count(*) as c')->from($this->table('user_follows'))
            ->where('business_id', $businessId)
            ->where('following_id', $userId)
            ->run()->fetch()['c'];
    }

    public function followingCount(string $businessId, string $userId): int
    {
        return (int) $this->db->select('count(*) as c')->from($this->table('user_follows'))
            ->where('business_id', $businessId)
            ->where('follower_id', $userId)
            ->run()->fetch()['c'];
    }
}
