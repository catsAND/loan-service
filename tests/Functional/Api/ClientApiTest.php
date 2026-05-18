<?php declare(strict_types=1);

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

final class ClientApiTest extends ApiTestCase
{
    private const string BASE_URL = '/api/v1/clients';

    public function testCreateClientSuccess(): void
    {
        $browser = static::createClient();

        $data = $this->jsonRequest($browser, 'POST', self::BASE_URL, [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'JOHN.DOE@mail.com',
            'phoneNumber' => '+37101234567',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertArrayHasKey('id', $data);
        self::assertSame('John', $data['firstName']);
        self::assertSame('Doe', $data['lastName']);
        self::assertSame('john.doe@mail.com', $data['email']);
        self::assertSame('+37101234567', $data['phoneNumber']);
    }

    public function testCreateClientValidationError(): void
    {
        $browser = static::createClient();

        $data = $this->jsonRequest($browser, 'POST', self::BASE_URL, [
            'firstName' => '',
            'lastName' => '',
            'email' => 'invalid',
            'phoneNumber' => '123',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertProblemJsonResponse($browser, Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $data);
        self::assertNotEmpty($data['errors']);
    }

    public function testCreateClientConflict(): void
    {
        $browser = static::createClient();
        $this->createClientResource($browser, email: 'duplicate@mail.com', phoneNumber: '+1234567890');

        $this->jsonRequest($browser, 'POST', self::BASE_URL, [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'duplicate@mail.com',
            'phoneNumber' => '+1234567891',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        $this->assertProblemJsonResponse($browser, Response::HTTP_CONFLICT);
    }

    public function testGetClientSuccess(): void
    {
        $browser = static::createClient();
        $created = $this->createClientResource($browser, email: 'get.client@mail.com', phoneNumber: '+1234567892');

        $data = $this->jsonRequest($browser, 'GET', self::BASE_URL.'/'.$created['id']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame($created['id'], $data['id']);
        self::assertSame('get.client@mail.com', $data['email']);
    }

    public function testGetClientNotFound(): void
    {
        $browser = static::createClient();

        $this->jsonRequest($browser, 'GET', self::BASE_URL.'/550e8400-e29b-41d4-a716-446655440000');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertProblemJsonResponse($browser, Response::HTTP_NOT_FOUND);
    }

    public function testGetClientWithInvalidUuid(): void
    {
        $browser = static::createClient();

        $this->jsonRequest($browser, 'GET', self::BASE_URL.'/not-a-uuid');

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertProblemJsonResponse($browser, Response::HTTP_BAD_REQUEST);
    }

    public function testListClientsSuccess(): void
    {
        $browser = static::createClient();
        $this->createClientResource($browser, email: 'list.client@mail.com', phoneNumber: '+1234567893');

        $data = $this->jsonRequest($browser, 'GET', self::BASE_URL.'?page=1&limit=200');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertArrayHasKey('data', $data);
        self::assertArrayHasKey('pagination', $data);
        self::assertSame(1, $data['pagination']['page']);
        self::assertSame(100, $data['pagination']['limit']);
        self::assertGreaterThanOrEqual(1, $data['pagination']['total']);
    }

    public function testUpdateClientSuccess(): void
    {
        $browser = static::createClient();
        $created = $this->createClientResource($browser, email: 'update.client@mail.com', phoneNumber: '+1234567894');

        $data = $this->jsonRequest($browser, 'PUT', self::BASE_URL.'/'.$created['id'], [
            'firstName' => 'Jone',
            'lastName' => 'Doe',
            'email' => 'jone.doe1@mail.com',
            'phoneNumber' => '+1234567895',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame($created['id'], $data['id']);
        self::assertSame('Jone', $data['firstName']);
        self::assertSame('Doe', $data['lastName']);
        self::assertSame('jone.doe1@mail.com', $data['email']);
        self::assertSame('+1234567895', $data['phoneNumber']);
    }

    public function testUpdateClientConflict(): void
    {
        $browser = static::createClient();
        $first = $this->createClientResource($browser, email: 'first.conflict@mail.com', phoneNumber: '+1234567896');
        $this->createClientResource($browser, email: 'second.conflict@mail.com', phoneNumber: '+1234567897');

        $this->jsonRequest($browser, 'PUT', self::BASE_URL.'/'.$first['id'], [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'second.conflict@mail.com',
            'phoneNumber' => '+1234567896',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
        $this->assertProblemJsonResponse($browser, Response::HTTP_CONFLICT);
    }

    public function testDeleteClientSuccess(): void
    {
        $browser = static::createClient();
        $created = $this->createClientResource($browser, email: 'delete.client@mail.com', phoneNumber: '+1234567898');

        $this->jsonRequest($browser, 'DELETE', self::BASE_URL.'/'.$created['id']);

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->jsonRequest($browser, 'GET', self::BASE_URL.'/'.$created['id']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @return array<string, mixed>
     */
    private function createClientResource(KernelBrowser $browser, string $email, string $phoneNumber): array
    {
        $data = $this->jsonRequest($browser, 'POST', self::BASE_URL, [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => $email,
            'phoneNumber' => $phoneNumber,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        return $data;
    }

    /**
     * @param array<string, mixed>|null $payload
     *
     * @return array<string, mixed>
     */
    private function jsonRequest(KernelBrowser $browser, string $method, string $url, ?array $payload = null): array
    {
        $browser->request(
            $method,
            $url,
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: null === $payload ? null : json_encode($payload, JSON_THROW_ON_ERROR),
        );

        $content = $browser->getResponse()->getContent();
        if ('' === $content) {
            return [];
        }

        $data = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        self::assertIsArray($data);

        return $data;
    }

    private function assertProblemJsonResponse(KernelBrowser $browser, int $status): void
    {
        self::assertResponseHeaderSame('Content-Type', 'application/problem+json');

        $data = json_decode((string) $browser->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($status, $data['status']);
        self::assertArrayHasKey('type', $data);
        self::assertArrayHasKey('title', $data);
        self::assertArrayHasKey('detail', $data);
        self::assertArrayHasKey('instance', $data);
        self::assertArrayHasKey('request_id', $data);
    }
}
