<?php

declare(strict_types=1);

namespace PrestoWorld\Modules\UserFollows\Models;

class Follow
{
    public int $id;
    public string $externalId;
    public string $businessId;
    public string $followerId;
    public string $followingId;
    public int $createdAt;

    public function __construct(array $data)
    {
        $this->id = (int) ($data['id'] ?? 0);
        $this->externalId = (string) ($data['external_id'] ?? '');
        $this->businessId = (string) ($data['business_id'] ?? '');
        $this->followerId = (string) ($data['follower_id'] ?? '');
        $this->followingId = (string) ($data['following_id'] ?? '');
        $this->createdAt = (int) ($data['created_at'] ?? 0);
    }
}
