<?php declare(strict_types=1);

namespace App\Exception;

use App\Dto\ErrorResponseDto;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ExceptionMapper
{
    public function map(\Throwable $exception): ErrorResponseDto
    {
        return match (true) {
            $this->isApiErrorException($exception) => $this->mapApiError($exception),
            $this->isValidationException($exception) => $this->mapValidation($exception),
            default => ErrorResponseDto::internalError($exception),
        };
    }

    private function isApiErrorException(\Throwable $exception): bool
    {
        return $exception instanceof ApiErrorException;
    }

    private function isValidationException(\Throwable $exception): bool
    {
        return $exception instanceof UnprocessableEntityHttpException
            && $exception->getPrevious() instanceof ValidationFailedException;
    }

    private function mapApiError(ApiErrorException $exception): ErrorResponseDto
    {
        return new ErrorResponseDto(
            status: $exception->getStatus(),
            type: $exception->getType(),
            title: $exception->getTitle(),
            detail: $exception->getDetail(),
            extensions: $exception->getExtensions(),
        );
    }

    private function mapValidation(\Throwable $exception): ErrorResponseDto
    {
        $violations = $exception
            ->getPrevious()
            ->getViolations();

        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        return new ErrorResponseDto(
            status: Response::HTTP_UNPROCESSABLE_ENTITY,
            type: 'https://api.example.com/errors/not-found', // FIXME:,
            title: 'Validation Error',
            detail: 'The request data did not pass validation.',
            extensions: ['errors' => $errors],
        );
    }
}
