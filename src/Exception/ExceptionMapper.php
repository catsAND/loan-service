<?php declare(strict_types=1);

namespace App\Exception;

use App\Dto\ErrorResponseDto;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ExceptionMapper
{
    public function __construct(
        private readonly string $apiDocsUrl = ApiErrorException::DEFAULT_TYPE,
    ) {
    }

    public function map(\Throwable $exception): ErrorResponseDto
    {
        if ($exception instanceof ApiErrorException) {
            return $this->mapApiError($exception);
        }

        if ($exception instanceof UnprocessableEntityHttpException
            && $exception->getPrevious() instanceof ValidationFailedException) {
            return $this->mapValidation($exception);
        }

        if ($exception instanceof NotFoundHttpException) {
            return $this->mapNotFound($exception);
        }

        return ErrorResponseDto::internalError();
    }

    private function mapNotFound(NotFoundHttpException $exception): ErrorResponseDto
    {
        return new ErrorResponseDto(
            status: Response::HTTP_NOT_FOUND,
            type: 'about:blank',
            title: 'Not Found',
            detail: $exception->getMessage(),
        );
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

    private function mapValidation(UnprocessableEntityHttpException $exception): ErrorResponseDto
    {
        $previous = $exception->getPrevious();
        if (!$previous instanceof ValidationFailedException) {
            return ErrorResponseDto::internalError();
        }

        $violations = $previous->getViolations();

        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        return new ErrorResponseDto(
            status: Response::HTTP_UNPROCESSABLE_ENTITY,
            type: $this->apiDocsUrl,
            title: 'Validation Error',
            detail: 'The request data did not pass validation.',
            extensions: ['errors' => $errors],
        );
    }
}
