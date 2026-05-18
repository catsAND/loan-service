<?php declare(strict_types=1);

namespace App\Tests\Unit\Transformer;

use App\Entity\Application;
use App\Entity\Client;
use App\Enum\CurrencyEnum;
use App\Transformer\ApplicationTransformer;
use PHPUnit\Framework\TestCase;

final class ApplicationTransformerTest extends TestCase
{
    private ApplicationTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new ApplicationTransformer();
    }

    private function createClient(): Client
    {
        $client = new Client();
        $client->setFirstName('John');
        $client->setLastName('Doe');
        $client->setEmail('john.doe@mail.com');
        $client->setPhone('+37101234567');

        return $client;
    }

    public function testTransformSingleApplication(): void
    {
        $client = $this->createClient();

        $application = new Application();
        $application->setClient($client);
        $application->setTerm(12);
        $application->setAmount('1500.50');
        $application->setCurrency(CurrencyEnum::EUR);

        $result = $this->transformer->transform($application);

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('clientId', $result);
        $this->assertArrayHasKey('term', $result);
        $this->assertArrayHasKey('amount', $result);
        $this->assertArrayHasKey('currency', $result);

        $this->assertSame(12, $result['term']);
        $this->assertSame('1500.50', $result['amount']);
        $this->assertSame(CurrencyEnum::EUR, $result['currency']);
        $this->assertIsString($result['amount']);
    }

    public function testTransformCollectionWithEmptyArray(): void
    {
        $result = $this->transformer->transformCollection([]);

        $this->assertEmpty($result);
    }

    public function testTransformCollectionWithMultipleApplications(): void
    {
        $client = $this->createClient();

        $app1 = new Application();
        $app1->setClient($client);
        $app1->setTerm(12);
        $app1->setAmount('1500.00');
        $app1->setCurrency(CurrencyEnum::EUR);

        $app2 = new Application();
        $app2->setClient($client);
        $app2->setTerm(24);
        $app2->setAmount('2500.00');
        $app2->setCurrency(CurrencyEnum::EUR);

        $applications = [$app1, $app2];

        $result = $this->transformer->transformCollection($applications);

        $this->assertCount(2, $result);
        $this->assertSame(12, $result[0]['term']);
        $this->assertSame(24, $result[1]['term']);
        $this->assertSame('1500.00', $result[0]['amount']);
        $this->assertSame('2500.00', $result[1]['amount']);
    }

    public function testTransformApplicationContainsClientId(): void
    {
        $client = $this->createClient();

        $application = new Application();
        $application->setClient($client);
        $application->setTerm(12);
        $application->setAmount('1500.00');
        $application->setCurrency(CurrencyEnum::EUR);

        $result = $this->transformer->transform($application);

        $this->assertArrayHasKey('clientId', $result);
    }
}
