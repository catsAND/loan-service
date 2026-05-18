<?php declare(strict_types=1);

namespace App\Tests\Unit\Transformer;

use App\Entity\Client;
use App\Transformer\ClientTransformer;
use PHPUnit\Framework\TestCase;

final class ClientTransformerTest extends TestCase
{
    private ClientTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new ClientTransformer();
    }

    public function testTransformSingleClient(): void
    {
        $client = new Client();
        $client->setFirstName('John');
        $client->setLastName('Doe');
        $client->setEmail('john.doe@mail.com');
        $client->setPhone('+37101234567');

        $result = $this->transformer->transform($client);

        $this->assertArrayHasKey('firstName', $result);
        $this->assertArrayHasKey('lastName', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('phoneNumber', $result);
        $this->assertArrayHasKey('id', $result);

        $this->assertSame('John', $result['firstName']);
        $this->assertSame('Doe', $result['lastName']);
        $this->assertSame('john.doe@mail.com', $result['email']);
        $this->assertSame('+37101234567', $result['phoneNumber']);
    }

    public function testTransformCollectionWithEmptyArray(): void
    {
        $result = $this->transformer->transformCollection([]);

        $this->assertEmpty($result);
    }

    public function testTransformCollectionWithMultipleClients(): void
    {
        $clients = [];
        for ($i = 1; $i <= 3; ++$i) {
            $client = new Client();
            $client->setFirstName("John{$i}");
            $client->setLastName("Doe{$i}");
            $client->setEmail("john.doe{$i}@mail.com");
            $client->setPhone("+3710123456{$i}");
            $clients[] = $client;
        }

        $result = $this->transformer->transformCollection($clients);

        $this->assertCount(3, $result);
        foreach ($result as $index => $transformed) {
            $expectedNum = $index + 1;
            $this->assertSame("John{$expectedNum}", $transformed['firstName']);
            $this->assertSame("Doe{$expectedNum}", $transformed['lastName']);
            $this->assertSame("john.doe{$expectedNum}@mail.com", $transformed['email']);
            $this->assertSame("+3710123456{$expectedNum}", $transformed['phoneNumber']);
        }
    }
}
