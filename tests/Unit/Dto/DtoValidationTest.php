<?php declare(strict_types=1);

namespace App\Tests\Unit\Dto;

use App\Dto\ApplicationDto;
use App\Dto\ClientDto;
use App\Dto\UpdateApplicationDto;
use App\Enum\CurrencyEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DtoValidationTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testClientDtoWithValidData(): void
    {
        $dto = new ClientDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john.doe@mail.com',
            phoneNumber: '+37101234567',
        );

        $this->assertCount(0, $this->validator->validate($dto));
    }

    public function testClientDtoWithBlankData(): void
    {
        $dto = new ClientDto(
            firstName: '',
            lastName: '',
            email: '',
            phoneNumber: '',
        );

        $violations = $this->validator->validate($dto);

        $this->assertViolationPropertyPaths($violations, [
            'firstName',
            'lastName',
            'email',
            'phoneNumber',
        ]);
    }

    public function testClientDtoWithLongInvalidData(): void
    {
        $dto = new ClientDto(
            firstName: str_repeat('A', 33),
            lastName: str_repeat('B', 33),
            email: 'not-an-email',
            phoneNumber: '+1234567890123456',
        );

        $violations = $this->validator->validate($dto);

        $this->assertViolationPropertyPaths($violations, [
            'firstName',
            'lastName',
            'email',
            'phoneNumber',
        ]);
    }

    public function testClientDtoWithShortInvalidData(): void
    {
        $dto = new ClientDto(
            firstName: 'J',
            lastName: 'D',
            email: 'e',
            phoneNumber: '+123456',
        );

        $violations = $this->validator->validate($dto);

        $this->assertViolationPropertyPaths($violations, [
            'firstName',
            'lastName',
            'email',
            'phoneNumber',
        ]);
    }

    public function testApplicationDtoWithValidData(): void
    {
        $dto = new ApplicationDto(
            clientId: '550e8400-e29b-41d4-a716-446655440000',
            term: 30,
            amount: '3000.00',
            currency: CurrencyEnum::EUR,
        );

        $this->assertCount(0, $this->validator->validate($dto));
    }

    public function testApplicationDtoWithInvalidData(): void
    {
        $dto = new ApplicationDto(
            clientId: 'invalid-uuid',
            term: 9,
            amount: '5000.01',
            currency: CurrencyEnum::EUR,
        );

        $violations = $this->validator->validate($dto);

        $this->assertViolationPropertyPaths($violations, [
            'clientId',
            'term',
            'amount',
        ]);
    }

    public function testUpdateApplicationDtoWithValidData(): void
    {
        $dto = new UpdateApplicationDto(
            term: 10,
            amount: '100.00',
            currency: CurrencyEnum::EUR,
        );

        $this->assertCount(0, $this->validator->validate($dto));
    }

    public function testUpdateApplicationDtoWithInvalidData(): void
    {
        $dto = new UpdateApplicationDto(
            term: 31,
            amount: '99.99',
            currency: CurrencyEnum::EUR,
        );

        $violations = $this->validator->validate($dto);

        $this->assertViolationPropertyPaths($violations, [
            'term',
            'amount',
        ]);
    }

    /**
     * @param iterable<\Symfony\Component\Validator\ConstraintViolationInterface> $violations
     * @param list<string>                                                        $expectedPaths
     */
    private function assertViolationPropertyPaths(iterable $violations, array $expectedPaths): void
    {
        $actualPaths = [];
        foreach ($violations as $violation) {
            $actualPaths[] = $violation->getPropertyPath();
        }

        foreach ($expectedPaths as $expectedPath) {
            $this->assertContains($expectedPath, $actualPaths);
        }
    }
}
