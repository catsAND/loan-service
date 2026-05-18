<?php declare(strict_types=1);

namespace App\Dto;

use App\Enum\CurrencyEnum;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ApplicationDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public string $clientId,
        #[Assert\NotBlank]
        #[Assert\Range(min: 10, max: 30)]
        public int $term,
        #[Assert\NotBlank]
        #[Assert\Range(min: 100.00, max: 5000.00)]
        public string $amount,
        #[Assert\NotBlank]
        public CurrencyEnum $currency,
    ) {
    }
}
