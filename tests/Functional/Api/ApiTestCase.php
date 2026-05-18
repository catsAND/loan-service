<?php declare(strict_types=1);

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ApiTestCase extends WebTestCase
{
    private static bool $databasePrepared = false;

    /**
     * @param array<string, mixed>  $options
     * @param array<string, string> $server
     */
    protected static function createClient(array $options = [], array $server = []): KernelBrowser
    {
        self::prepareDatabase();

        return parent::createClient($options, $server);
    }

    private static function prepareDatabase(): void
    {
        if (self::$databasePrepared) {
            return;
        }

        $databaseUrl = (string) ($_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? '');
        if ('' === $databaseUrl) {
            throw new \RuntimeException('DATABASE_URL is required for functional tests.');
        }

        $parts = parse_url($databaseUrl);
        if (false === $parts || ($parts['scheme'] ?? '') !== 'postgresql') {
            throw new \RuntimeException('Functional tests support only PostgreSQL DATABASE_URL.');
        }

        $host = $parts['host'] ?? 'localhost';
        $port = (int) ($parts['port'] ?? 5432);
        $user = $parts['user'] ?? '';
        $password = $parts['pass'] ?? '';
        $baseDatabase = ltrim($parts['path'] ?? '', '/');
        $testDatabase = $baseDatabase.'_test';

        $admin = new \PDO(
            sprintf('pgsql:host=%s;port=%d;dbname=postgres', $host, $port),
            $user,
            $password,
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION],
        );

        $exists = $admin->prepare('SELECT 1 FROM pg_database WHERE datname = :database');
        $exists->execute(['database' => $testDatabase]);

        if (false === $exists->fetchColumn()) {
            $admin->exec(sprintf('CREATE DATABASE %s', self::quoteIdentifier($testDatabase)));
        }

        $testConnection = new \PDO(
            sprintf('pgsql:host=%s;port=%d;dbname=%s', $host, $port, $testDatabase),
            $user,
            $password,
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION],
        );

        $schemaSql = file_get_contents(dirname(__DIR__, 3).'/data/database.sql');
        if (false === $schemaSql) {
            throw new \RuntimeException('Cannot read data/database.sql.');
        }

        $testConnection->exec($schemaSql);

        self::$databasePrepared = true;
    }

    private static function quoteIdentifier(string $identifier): string
    {
        return '"'.str_replace('"', '""', $identifier).'"';
    }
}
