<?php declare(strict_types=1);

namespace App\Tests\Unit\Normalizer;

use App\Normalizer\EmailNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class EmailNormalizerTest extends TestCase
{
    private EmailNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new EmailNormalizer();
    }

    #[DataProvider('normalizeDataProvider')]
    public function testNormalize(string $email, string $expected): void
    {
        $this->assertSame($expected, $this->normalizer->normalize($email));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function normalizeDataProvider(): iterable
    {
        yield 'lowercase email is unchanged' => [
            'john.doe@mail.com',
            'john.doe@mail.com',
        ];

        yield 'uppercase email is lowercased' => [
            'JOHN.DOE@MAIL.COM',
            'john.doe@mail.com',
        ];

        yield 'surrounding whitespace is trimmed' => [
            '  John.Doe@Mail.com  ',
            'john.doe@mail.com',
        ];
    }
}
