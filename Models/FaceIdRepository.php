<?php

declare(strict_types=1);

namespace Models;

interface FaceIdRepository
{
    /** @param array<int,float> $descriptor */
    public function upsert(int $userId, array $descriptor): void;

    /** @return array<int,float>|null */
    public function getByUserId(int $userId): ?array;
}
