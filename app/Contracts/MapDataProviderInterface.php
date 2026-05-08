<?php

declare(strict_types=1);

namespace App\Contracts;

interface MapDataProviderInterface
{
    public function mapData(?string $categorie, ?string $date, ?string $zone): array;
}