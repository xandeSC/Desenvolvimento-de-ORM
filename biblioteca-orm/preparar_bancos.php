<?php

declare(strict_types=1);

// Assistente local de instalação. Roda exclusivamente no terminal, sem exibir credenciais.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Execute pelo terminal.\n");
}

try {
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $usuario = getenv('DB_USER') ?: 'biblioteca';
    $senha = getenv('DB_PASS') ?: '';
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $usuario, $senha, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    // Os schemas da aula têm somente comandos DDL simples separados por ';'.
    foreach (['schema.sql', 'schema_teste.sql'] as $arquivo) {
        $conteudo = file_get_contents(__DIR__ . '/sql/' . $arquivo);
        if ($conteudo === false) {
            throw new RuntimeException('Arquivo SQL não encontrado: ' . $arquivo);
        }
        $conteudo = preg_replace('/^\s*--[^\r\n]*/m', '', $conteudo);
        foreach (explode(';', $conteudo) as $instrucao) {
            if (trim($instrucao) !== '') {
                $pdo->exec($instrucao);
            }
        }
        echo '[OK] Estrutura do banco: ' . $arquivo . "\n";
    }
} catch (Throwable $e) {
    fwrite(STDERR, '[FALHA] Não foi possível preparar o MySQL: ' . $e->getMessage() . "\n");
    exit(1);
}
