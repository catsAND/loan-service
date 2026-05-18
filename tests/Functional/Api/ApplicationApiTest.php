<?php declare(strict_types=1);

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

final class ApplicationApiTest extends ApiTestCase
{
    private const string APPLICATIONS_URL = '/api/v1/applications';
    private const string CLIENTS_URL = '/api/v1/clients';

    public function testCreateApplicationSuccess(): void
    {
        $browser = static::createClient();
        $client = $this->createClientResource($browser, 'app.create.client@mail.com', '+2234567890');

        $data = $this->jsonRequest($browser, 'POST', self::APPLICATIONS_URL, [
            'clientId' => $client['id'],
            'term' => 12,
            'amount' => '1500.50',
            'currency' => 'EUR',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertArrayHasKey('id', $data);
        self::assertSame($client['id'], $data['clientId']);
        self::assertSame(12, $data['term']);
        self::assertSame('1500.50', $data['amount']);
        self::assertSame('EUR', $data['currency']);
    }

    public function testCreateApplicationValidationError(): void
    {
        $browser = static::createClient();

        $data = $this->jsonRequest($browser, 'POST', self::APPLICATIONS_URL, [
            'clientId' => '',
            'term' => 9,
            'amount' => '5000.01',
            'currency' => 'EUR',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertProblemJsonResponse($browser, Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $data);
        self::assertNotEmpty($data['errors']);
    }

    public function testCreateApplicationClientNotFound(): void
    {
        $browser = static::createClient();

        $this->jsonRequest($browser, 'POST', self::APPLICATIONS_URL, [
            'clientId' => '550e8400-e29b-41d4-a716-446655440000',
            'term' => 12,
            'amount' => '1500.50',
            'currency' => 'EUR',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertProblemJsonResponse($browser, Response::HTTP_NOT_FOUND);
    }

    public function testGetApplicationSuccess(): void
    {
        $browser = static::createClient();
        $client = $this->createClientResource($browser, 'app.get.client@mail.com', '+2234567891');
        $application = $this->createApplicationResource($browser, $client['id']);

        $data = $this->jsonRequest($browser, 'GET', self::APPLICATIONS_URL.'/'.$application['id']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame($application['id'], $data['id']);
        self::assertSame($client['id'], $data['clientId']);
    }

    public function testGetApplicationNotFound(): void
    {
        $browser = static::createClient();

        $this->jsonRequest($browser, 'GET', self::APPLICATIONS_URL.'/550e8400-e29b-41d4-a716-446655440001');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertProblemJsonResponse($browser, Response::HTTP_NOT_FOUND);
    }

    public function testGetApplicationWithInvalidUuid(): void
    {
        $browser = static::createClient();

        $this->jsonRequest($browser, 'GET', self::APPLICATIONS_URL.'/not-a-uuid');

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertProblemJsonResponse($browser, Response::HTTP_BAD_REQUEST);
    }

    public function testListApplicationsSuccess(): void
    {
        $browser = static::createClient();
        $client = $this->createClientResource($browser, 'app.list.client@mail.com', '+2234567892');
        $this->createApplicationResource($browser, $client['id']);

        $data = $this->jsonRequest($browser, 'GET', self::APPLICATIONS_URL.'?page=1&limit=200');

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertArrayHasKey('data', $data);
        self::assertArrayHasKey('pagination', $data);
        self::assertSame(1, $data['pagination']['page']);
        self::assertSame(100, $data['pagination']['limit']);
        self::assertGreaterThanOrEqual(1, $data['pagination']['total']);
    }

    public function testUpdateApplicationSuccess(): void
    {
        $browser = static::createClient();
        $client = $this->createClientResource($browser, 'app.update.client@mail.com', '+2234567893');
        $application = $this->createApplicationResource($browser, $client['id']);

        $data = $this->jsonRequest($browser, 'PUT', self::APPLICATIONS_URL.'/'.$application['id'], [
            'term' => 24,
            'amount' => '2500.00',
            'currency' => 'EUR',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame($application['id'], $data['id']);
        self::assertSame(24, $data['term']);
        self::assertSame('2500.00', $data['amount']);
        self::assertSame('EUR', $data['currency']);
    }

    public function testUpdateApplicationValidationError(): void
    {
        $browser = static::createClient();
        $client = $this->createClientResource($browser, 'app.update.validation.client@mail.com', '+2234567894');
        $application = $this->createApplicationResource($browser, $client['id']);

        $data = $this->jsonRequest($browser, 'PUT', self::APPLICATIONS_URL.'/'.$application['id'], [
            'term' => 31,
            'amount' => '99.99',
            'currency' => 'EUR',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertProblemJsonResponse($browser, Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertArrayHasKey('errors', $data);
    }

    public function testDeleteApplicationSuccess(): void
    {
        $browser = static::createClient();
        $client = $this->createClientResource($browser, 'app.delete.client@mail.com', '+2234567895');
        $application = $this->createApplicationResource($browser, $client['id']);

        $this->jsonRequest($browser, 'DELETE', self::APPLICATIONS_URL.'/'.$application['id']);

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $this->jsonRequest($browser, 'GET', self::APPLICATIONS_URL.'/'.$application['id']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /**
     * @return array<string, mixed>
     */
    private function createClientResource(KernelBrowser $browser, string $email, string $phoneNumber): array
    {
        $data = $this->jsonRequest($browser, 'POST', self::CLIENTS_URL, [
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => $email,
            'phoneNumber' => $phoneNumber,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function createApplicationResource(KernelBrowser $browser, string $clientId): array
    {
        $data = $this->jsonRequest($browser, 'POST', self::APPLICATIONS_URL, [
            'clientId' => $clientId,
            'term' => 12,
            'amount' => '1500.50',
            'currency' => 'EUR',
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
