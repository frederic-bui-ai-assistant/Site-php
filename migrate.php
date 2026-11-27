<?php
declare(strict_types=1);

// Migration : crée la base de données et la table "tasks".
// Usage : php migrate.php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Ce script doit être lancé en ligne de commande.');
}

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$name = getenv('DB_NAME') ?: 'simple_site';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

if (!preg_match('/^[A-Za-z0-9_]+$/', $name)) {
    fwrite(STDERR, "Nom de base invalide (lettres, chiffres et _ uniquement).\n");
    exit(1);
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$name`");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tasks (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            done TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");

    echo "Migration terminée : base '$name', table 'tasks' prête.\n";
} catch (PDOException $e) {
    fwrite(STDERR, 'Erreur de migration : ' . $e->getMessage() . "\n");
    exit(1);
}
