<?php declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class PhoneNumber extends Constraint
{
    public string $message = 'Phone number "{{ value }}" is not valid format.';
}
