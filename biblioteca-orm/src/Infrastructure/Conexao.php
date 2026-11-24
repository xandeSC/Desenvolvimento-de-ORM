<?php

declare(strict_types=1);

namespace Biblioteca\Infrastructure;

final class Conexao
{
    public static function abrir(): \PDO
    {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $nome = getenv('DB_NAME') ?: 'biblioteca_orm';
        $usuario = getenv('DB_USER') ?: 'biblioteca';
        $senha = getenv('DB_PASS') ?: '';

        return new \PDO(
            "mysql:host=$host;dbname=$nome;charset=utf8mb4",
            $usuario,
            $senha,
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
}
