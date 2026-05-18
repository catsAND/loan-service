#!/usr/bin/env sh
set -eu

cd /app

if [ -z "${DATABASE_URL:-}" ]; then
    echo "DATABASE_URL is required." >&2
    exit 1
fi

if [ -z "${RABBITMQ_DSN:-}" ]; then
    echo "RABBITMQ_DSN is required." >&2
    exit 1
fi

wait_for_database() {
    echo "Waiting for PostgreSQL..."

    tries=0
    until php -r '
        $url = parse_url(getenv("DATABASE_URL"));
        if ($url === false || !isset($url["host"], $url["path"])) {
            exit(1);
        }

        $host = $url["host"];
        $port = $url["port"] ?? 5432;
        $database = ltrim($url["path"], "/");
        $user = $url["user"] ?? "";
        $password = $url["pass"] ?? "";

        try {
            new PDO("pgsql:host={$host};port={$port};dbname={$database}", $user, $password);
        } catch (Throwable) {
            exit(1);
        }
    '; do
        tries=$((tries + 1))
        if [ "$tries" -ge 60 ]; then
            echo "PostgreSQL is not available." >&2
            exit 1
        fi
        sleep 1
    done
}

wait_for_rabbitmq() {
    echo "Waiting for RabbitMQ..."

    tries=0
    until php -r '
        $url = parse_url(getenv("RABBITMQ_DSN"));
        if ($url === false || !isset($url["host"])) {
            exit(1);
        }

        $host = $url["host"];
        $port = $url["port"] ?? 5672;
        $socket = @fsockopen($host, (int) $port, $errno, $errstr, 1.0);
        if ($socket === false) {
            exit(1);
        }

        fclose($socket);
    '; do
        tries=$((tries + 1))
        if [ "$tries" -ge 60 ]; then
            echo "RabbitMQ is not available." >&2
            exit 1
        fi
        sleep 1
    done
}

initialize_database() {
    if [ "${APP_ENV:-prod}" = "test" ] || [ "${SKIP_DB_INIT:-0}" = "1" ]; then
        return
    fi

    echo "Checking database schema..."

    php <<'PHP'
<?php declare(strict_types=1);

$url = parse_url((string) getenv('DATABASE_URL'));
if ($url === false || !isset($url['host'], $url['path'])) {
    fwrite(STDERR, "Invalid DATABASE_URL.\n");
    exit(1);
}

$host = $url['host'];
$port = $url['port'] ?? 5432;
$database = ltrim($url['path'], '/');
$user = $url['user'] ?? '';
$password = $url['pass'] ?? '';
$pdo = new PDO("pgsql:host={$host};port={$port};dbname={$database}", $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$schemaExists = (bool) $pdo
    ->query("SELECT to_regclass('public.clients') IS NOT NULL AND to_regclass('public.applications') IS NOT NULL")
    ->fetchColumn();

if ($schemaExists) {
    echo "Database schema already exists.\n";
    exit(0);
}

$schema = file_get_contents(getcwd().'/data/database.sql');
if ($schema === false) {
    fwrite(STDERR, "Cannot read data/database.sql.\n");
    exit(1);
}

$pdo->exec($schema);
echo "Database schema applied.\n";
PHP
}

wait_for_database
wait_for_rabbitmq
initialize_database

exec "$@"
