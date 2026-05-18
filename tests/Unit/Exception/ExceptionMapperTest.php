<?php declare(strict_types=1);

namespace App\Tests\Unit\Exception;

use App\Dto\ErrorResponseDto;
use App\Exception\ExceptionMapper;
use App\Exception\PhoneExistsException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ExceptionMapperTest extends TestCase
{
    private ExceptionMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ExceptionMapper('/api/v1/docs');
    }

    public function testMapApiErrorException(): void
    {
        $result = $this->mapper->map(new PhoneExistsException());

        $this->assertSame(Response::HTTP_CONFLICT, $result->status);
        $this->assertSame('/api/v1/docs', $result->type);
        $this->assertSame('Phone number already exists', $result->title);
        $this->assertSame('A client with the provided phone number already exists.', $result->detail);
        $this->assertSame([
            'errors' => [
                'field' => 'phoneNumber',
                'message' => 'Phone number already exists.',
            ],
        ], $result->extensions);
    }

    public function testMapValidationException(): void
    {
        $violations = new ConstraintViolationList([
            new ConstraintViolation(
                message: 'This value should not be blank.',
                messageTemplate: 'This value should not be blank.',
                parameters: [],
                root: null,
                propertyPath: 'firstName',
                invalidValue: '',
            ),
            new ConstraintViolation(
                message: 'This value is not a valid email address.',
                messageTemplate: 'This value is not a valid email address.',
                parameters: [],
                root: null,
                propertyPath: 'email',
                invalidValue: 'invalid-email',
            ),
        ]);

        $exception = new UnprocessableEntityHttpException(
            previous: new ValidationFailedException(null, $violations),
        );

        $result = $this->mapper->map($exception);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $result->status);
        $this->assertSame('/api/v1/docs', $result->type);
        $this->assertSame('Validation Error', $result->title);
        $this->assertSame('The request data did not pass validation.', $result->detail);
        $this->assertSame([
            'errors' => [
                [
                    'field' => 'firstName',
                    'message' => 'This value should not be blank.',
                ],
                [
                    'field' => 'email',
                    'message' => 'This value is not a valid email address.',
                ],
            ],
        ], $result->extensions);
    }

    public function testMapNotFoundHttpException(): void
    {
        $result = $this->mapper->map(new NotFoundHttpException('Route not found.'));

        $this->assertSame(Response::HTTP_NOT_FOUND, $result->status);
        $this->assertSame('about:blank', $result->type);
        $this->assertSame('Not Found', $result->title);
        $this->assertSame('Route not found.', $result->detail);
        $this->assertSame([], $result->extensions);
    }

    public function testMapUnknownExceptionToInternalError(): void
    {
        $result = $this->mapper->map(new \RuntimeException('Sensitive internal message.'));

        $this->assertEquals(ErrorResponseDto::internalError(), $result);
    }
}
