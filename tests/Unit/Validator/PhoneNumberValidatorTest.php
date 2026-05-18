<?php declare(strict_types=1);

namespace App\Tests\Unit\Validator;

use App\Validator\PhoneNumber;
use App\Validator\PhoneNumberValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

final class PhoneNumberValidatorTest extends TestCase
{
    private PhoneNumberValidator $validator;
    private ExecutionContext&MockObject $context;

    protected function setUp(): void
    {
        $this->validator = new PhoneNumberValidator();
        $this->context = $this->createMock(ExecutionContext::class);
        $this->validator->initialize($this->context);
    }

    public function testValidateWithValidPhoneNumber(): void
    {
        // Arrange
        $constraint = new PhoneNumber();
        $value = '+1234567890';

        $this->context
            ->expects($this->never())
            ->method('buildViolation');

        // Act & Assert
        $this->validator->validate($value, $constraint);
    }

    public function testValidateWithValidPhoneNumber2(): void
    {
        // Arrange
        $constraint = new PhoneNumber();
        $value = '+37101234567';

        $this->context
            ->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate($value, $constraint);
    }

    public function testValidateWithLongValidPhoneNumber(): void
    {
        $constraint = new PhoneNumber();
        $value = '+123456789012345';

        $this->context
            ->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate($value, $constraint);
    }

    public function testValidateWithShortValidPhoneNumber(): void
    {
        $constraint = new PhoneNumber();
        $value = '+1234567';

        $this->context
            ->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate($value, $constraint);
    }

    public function testValidateWithInvalidPhoneNumberMissingPlus(): void
    {
        $constraint = new PhoneNumber();
        $value = '1234567890';

        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->with('{{ value }}', $value)
            ->willReturnSelf();
        $violationBuilder
            ->expects($this->once())
            ->method('addViolation');

        $this->context
            ->expects($this->once())
            ->method('buildViolation')
            ->with($constraint->message)
            ->willReturn($violationBuilder);

        $this->validator->validate($value, $constraint);
    }

    public function testValidateWithInvalidPhoneNumberStartingWithZero(): void
    {
        $constraint = new PhoneNumber();
        $value = '+0123456789';

        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->willReturnSelf();
        $violationBuilder
            ->expects($this->once())
            ->method('addViolation');

        $this->context
            ->expects($this->once())
            ->method('buildViolation')
            ->willReturn($violationBuilder);

        $this->validator->validate($value, $constraint);
    }

    public function testValidateWithInvalidPhoneNumberTooShort(): void
    {
        $constraint = new PhoneNumber();
        $value = '+12345';

        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->willReturnSelf();
        $violationBuilder
            ->expects($this->once())
            ->method('addViolation');

        $this->context
            ->expects($this->once())
            ->method('buildViolation')
            ->willReturn($violationBuilder);

        $this->validator->validate($value, $constraint);
    }

    public function testValidateWithInvalidPhoneNumberTooLong(): void
    {
        $constraint = new PhoneNumber();
        $value = '+1234567890123456';

        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->willReturnSelf();
        $violationBuilder
            ->expects($this->once())
            ->method('addViolation');

        $this->context
            ->expects($this->once())
            ->method('buildViolation')
            ->willReturn($violationBuilder);

        $this->validator->validate($value, $constraint);
    }

    public function testValidateWithInvalidPhoneNumberContainsLetters(): void
    {
        $constraint = new PhoneNumber();
        $value = '+123ABC';

        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->willReturnSelf();
        $violationBuilder
            ->expects($this->once())
            ->method('addViolation');

        $this->context
            ->expects($this->once())
            ->method('buildViolation')
            ->willReturn($violationBuilder);

        $this->validator->validate($value, $constraint);
    }

    public function testValidateWithInvalidPhoneNumberContainsSpecialCharacters(): void
    {
        $constraint = new PhoneNumber();
        $value = '+123-45-678-90';

        $violationBuilder = $this->createMock(ConstraintViolationBuilder::class);
        $violationBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->willReturnSelf();
        $violationBuilder
            ->expects($this->once())
            ->method('addViolation');

        $this->context
            ->expects($this->once())
            ->method('buildViolation')
            ->willReturn($violationBuilder);

        $this->validator->validate($value, $constraint);
    }

    public function testValidateWithNullValue(): void
    {
        $constraint = new PhoneNumber();
        $value = null;

        $this->context
            ->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate($value, $constraint);
    }

    public function testValidateWithEmptyString(): void
    {
        $constraint = new PhoneNumber();
        $value = '';

        $this->context
            ->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate($value, $constraint);
    }

    public function testValidateWithInvalidConstraintType(): void
    {
        $constraint = $this->createMock(\Symfony\Component\Validator\Constraint::class);

        $this->expectException(\Symfony\Component\Validator\Exception\UnexpectedTypeException::class);
        $this->validator->validate('+1234567890', $constraint);
    }
}
