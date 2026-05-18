<?php declare(strict_types=1);

namespace App\Normalizer;

final class EmailNormalizer
{
    public function normalize(string $email): string
    {
        return $email
            |> trim(...)
            |> strtolower(...);
    }
}
