<?php declare(strict_types=1);

namespace App\Dto;

use App\Validator\PhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class ClientDto
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 32)]
        public string $firstName,
        #[Assert\NotBlank]
        #[Assert\Length(min: 2, max: 32)]
        public string $lastName,
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,
        #[Assert\NotBlank]
        #[PhoneNumber]
        public string $phoneNumber,
    ) {
    }
}
