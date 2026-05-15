<?php declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class PhoneNumberValidator extends ConstraintValidator
{
    private const string REGEX_PATTERN = '/^\+[1-9]\d{6,14}$/';

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PhoneNumber) {
            throw new UnexpectedTypeException($constraint, PhoneNumber::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!preg_match(self::REGEX_PATTERN, $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
