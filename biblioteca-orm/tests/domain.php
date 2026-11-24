<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/autoload.php';

use Biblioteca\Domain\Autor;
use Biblioteca\Domain\Livro;

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Execute pelo terminal: php tests/domain.php');
}

function verificar(bool $condicao, string $mensagem): void
{
    if (!$condicao) {
        throw new \RuntimeException($mensagem);
    }
}

function esperarExcecao(string $tipo, callable $funcao): void
{
    try {
        $funcao();
    } catch (\Throwable $erro) {
        if ($erro instanceof $tipo) {
            return;
        }
        throw $erro;
    }
    throw new \RuntimeException("A exceção $tipo era esperada e não ocorreu.");
}

$casos = [
    'Título obrigatório' => static function (): void {
        esperarExcecao(\InvalidArgumentException::class, static fn () => new Livro('   '));
    },
    'Nome de autor obrigatório' => static function (): void {
        esperarExcecao(\InvalidArgumentException::class, static fn () => new Autor(' '));
    },
    'Título normalizado e atributos' => static function (): void {
        $livro = new Livro('  Livro exemplo  ', '1234567890123', 2026);
        verificar($livro->titulo() === 'Livro exemplo', 'Título não foi normalizado.');
        verificar($livro->isbn() === '1234567890123', 'ISBN diferente do informado.');
        verificar($livro->anoPublicacao() === 2026, 'Ano incorreto.');
        verificar($livro->id() === null, 'Um objeto novo não deve ter ID.');
    },
    'Identidade inválida e identidade imutável' => static function (): void {
        $livro = new Livro('Título');
        esperarExcecao(\LogicException::class, static fn () => $livro->atribuirId(0));
        $livro->atribuirId(10);
        $livro->atribuirId(10);
        esperarExcecao(\LogicException::class, static fn () => $livro->atribuirId(11));
        verificar($livro->id() === 10, 'O ID não deve mudar depois de atribuído.');
    },
    'Associação sem autor duplicado' => static function (): void {
        $livro = new Livro('Livro com autores');
        $autorA = new Autor('Autora A', 'Brasil');
        $autorB = new Autor('Outra instância de A');
        $autorA->atribuirId(15);
        $autorB->atribuirId(15);
        $livro->adicionarAutor($autorA);
        $livro->adicionarAutor($autorA);
        $livro->adicionarAutor($autorB);
        verificar(count($livro->autores()) === 1, 'Autor duplicado na entidade.');
    },
];

$total = 0;
try {
    foreach ($casos as $nome => $caso) {
        $caso();
        ++$total;
        echo "[OK] $nome\n";
    }
    echo "\n$total testes de domínio aprovados (sem precisar de MySQL).\n";
} catch (\Throwable $erro) {
    fwrite(STDERR, "[FALHOU] $nome: {$erro->getMessage()}\n");
    exit(1);
}
