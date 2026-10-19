<?php

declare(strict_types=1);

$url = getenv('MYSQL_URL') ?: getenv('DB_URL') ?: '';
$host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: '3306';
$db = getenv('MYSQLDATABASE') ?: getenv('DB_DATABASE') ?: 'railway';
$user = getenv('MYSQLUSER') ?: getenv('DB_USERNAME') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD') ?: '';

if (! empty($url)) {
    $parts = parse_url($url);
    if (! empty($parts['host'])) {
        $host = $parts['host'];
    }
    if (! empty($parts['port'])) {
        $port = (string) $parts['port'];
    }
    if (! empty($parts['user'])) {
        $user = $parts['user'];
    }
    if (! empty($parts['pass'])) {
        $pass = $parts['pass'];
    }
    if (! empty($parts['path'])) {
        $db = ltrim($parts['path'], '/');
    }
}

$connected = false;
$error = '';

if (! empty($host) && $host !== '127.0.0.1' && ! empty($pass)) {
    try {
        $dsn = "mysql:host={$host};port={$port};dbname={$db}";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_TIMEOUT => 3,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $connected = true;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

if ($connected) {
    echo "STATUS=CONNECTED\n";
    echo "DB_CONNECTION=mysql\n";
    echo "DB_HOST={$host}\n";
    echo "DB_PORT={$port}\n";
    echo "DB_DATABASE={$db}\n";
    echo "DB_USERNAME={$user}\n";
    echo "DB_PASSWORD={$pass}\n";
} else {
    if (! empty($error)) {
        fwrite(STDERR, "MySQL connection notice: {$error}\n");
    }
    echo "STATUS=FALLBACK\n";
    echo "DB_CONNECTION=sqlite\n";
    echo "DB_DATABASE=/var/www/html/database/database.sqlite\n";
    echo "DB_HOST=127.0.0.1\n";
    echo "DB_PORT=3306\n";
    echo "DB_USERNAME=root\n";
    echo "DB_PASSWORD=\n";
}
