<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/autoload.php';

use Biblioteca\Application\ConsultaPolimorfica;
use Biblioteca\Domain\Autor;
use Biblioteca\Domain\Livro;
use Biblioteca\Infrastructure\AutorRepository;
use Biblioteca\Infrastructure\Conexao;
use Biblioteca\Infrastructure\LivroRepository;

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Este script só executa no terminal.');
}

// Isola os testes: não aceita o banco principal da demonstração.
if (getenv('DB_NAME') !== 'biblioteca_orm_teste') {
    fwrite(STDERR, "[FALHA] Defina DB_NAME=biblioteca_orm_teste para testar.\n");
    exit(1);
}

function verificar(bool $condicao, string $mensagem): void
{
    if (!$condicao) {
        throw new RuntimeException($mensagem);
    }
}

function caso(string $descricao, callable $teste): void
{
    $teste();
    echo '[OK] ' . $descricao . "\n";
}

function exigirFalhaDeFK(callable $acao): void
{
    try {
        $acao();
    } catch (PDOException $e) {
        verificar((string) $e->getCode() === '23000', 'Era esperado SQLSTATE 23000.');
        return;
    }
    throw new RuntimeException('O MySQL deveria rejeitar esta operação.');
}

try {
    if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
        throw new RuntimeException('Habilite a extensão pdo_mysql.');
    }
    $pdo = Conexao::abrir();
    $livros = new LivroRepository($pdo);
    $autores = new AutorRepository($pdo);
    $marca = 'ORM_' . bin2hex(random_bytes(5));

    $pdo->beginTransaction();
    try {
        $autor1 = new Autor('Ana ' . $marca, 'Brasil');
        $autor2 = new Autor('Bruno ' . $marca, 'Brasil');
        $livro = new Livro('Engenharia de Software ' . $marca, null, 2026);
        caso('1. INSERT e identidade do MySQL para Livro e dois Autores', static function () use ($autores, $livros, $autor1, $autor2, $livro): void {
            $autores->salvar($autor1);
            $autores->salvar($autor2);
            $livros->salvar($livro);
            verificar($autor1->id() > 0 && $autor2->id() > 0 && $livro->id() > 0, 'ID ausente.');
            verificar($autor1->id() !== $autor2->id(), 'Autores distintos não podem ter o mesmo ID.');
        });

        caso('2. SELECT por ID e busca inexistente', static function () use ($livros, $livro): void {
            $resposta = $livros->buscar($livro->id());
            verificar($resposta instanceof Livro, 'SELECT falhou.');
            verificar($resposta->titulo() === $livro->titulo(), 'Título não foi hidratado.');
            verificar($livros->buscar(2147483647) === null, 'Busca inexistente deve retornar null.');
        });

        caso('3. LISTAR de Livro e Autor', static function () use ($livros, $autores, $livro, $autor1): void {
            verificar(count(array_filter($livros->listar(), static fn($l): bool => $l->id() === $livro->id())) === 1, 'Livro não listado.');
            verificar(count(array_filter($autores->listar(), static fn($a): bool => $a->id() === $autor1->id())) === 1, 'Autor não listado.');
        });

        caso('4. UPDATE seguido de nova consulta', static function () use ($livros, $livro): void {
            $livro->alterarTitulo('POO com PDO');
            $livros->salvar($livro);
            verificar($livros->buscar($livro->id())->titulo() === 'POO com PDO', 'UPDATE falhou.');
        });

        caso('5. DELETE de livro sem vínculo', static function () use ($livros): void {
            $temporario = new Livro('Livro descartável');
            $livros->salvar($temporario);
            $livros->excluir($temporario->id());
            verificar($livros->buscar($temporario->id()) === null, 'DELETE falhou.');
        });

        caso('6. N:N com dois autores e coleção sem repetição', static function () use ($livros, $livro, $autor1, $autor2): void {
            $livros->associarAutor($livro->id(), $autor1->id());
            $livros->associarAutor($livro->id(), $autor2->id());
            $comAutores = $livros->buscarComAutores($livro->id());
            verificar($comAutores instanceof Livro && count($comAutores->autores()) === 2, 'Coleção N:N incompleta.');
            $ids = array_map(static fn(Autor $a): int => $a->id(), $comAutores->autores());
            verificar(count(array_unique($ids)) === 2, 'Autores duplicados na coleção.');
            $comAutores->adicionarAutor($comAutores->autores()[0]);
            verificar(count($comAutores->autores()) === 2, 'A entidade não evitou duplicação.');
        });

        caso('7. PK composta rejeita vínculo repetido', static function () use ($livros, $livro, $autor1): void {
            exigirFalhaDeFK(static fn() => $livros->associarAutor($livro->id(), $autor1->id()));
        });

        caso('8. FK impede excluir autor vinculado', static function () use ($autores, $autor1): void {
            exigirFalhaDeFK(static fn() => $autores->excluir($autor1->id()));
        });

        caso('9. Validação de título vazio', static function (): void {
            try {
                new Livro('  ');
            } catch (InvalidArgumentException) {
                return;
            }
            throw new RuntimeException('Título vazio foi aceito.');
        });

        caso('10. Prepared statement impede SQL injection', static function () use ($livros, $pdo): void {
            $payload = "x'); DROP TABLE autores; --";
            $livro = new Livro($payload);
            $livros->salvar($livro);
            verificar($livros->buscar($livro->id())->titulo() === $payload, 'Texto SQL não foi preservado.');
            verificar((int) $pdo->query('SELECT COUNT(*) FROM autores')->fetchColumn() >= 2, 'Tabela autores não existe.');
        });

        caso('11. Polimorfismo: interface única para ambos', static function () use ($livros, $autores, $livro, $autor1): void {
            verificar(ConsultaPolimorfica::buscar($livros, $livro->id()) instanceof Livro, 'Polimorfismo Livro falhou.');
            verificar(ConsultaPolimorfica::buscar($autores, $autor1->id()) instanceof Autor, 'Polimorfismo Autor falhou.');
        });

        caso('12. Hidratação conserva tipos e identidade', static function () use ($livros, $autores, $livro, $autor1): void {
            $l = $livros->buscar($livro->id());
            $a = $autores->buscar($autor1->id());
            verificar(is_int($l->id()) && is_int($l->anoPublicacao()), 'Tipo de Livro inválido.');
            verificar(is_int($a->id()) && $a->nacionalidade() === 'Brasil', 'Tipo de Autor inválido.');
        });
    } finally {
        if ($pdo->inTransaction()) {
            $pdo->rollBack(); // sem resíduos de registros de testes
        }
    }

    caso('13. FK inválida causa rollback sem persistir Livro', static function () use ($pdo, $livros, $marca): void {
        $titulo = 'Rollback ' . $marca;
        $pdo->beginTransaction();
        try {
            $novo = new Livro($titulo);
            $livros->salvar($novo);
            $livros->associarAutor($novo->id(), 2147483647);
            throw new RuntimeException('A FK não rejeitou autor inexistente.');
        } catch (PDOException $e) {
            verificar((string) $e->getCode() === '23000', 'Esperada falha de integridade.');
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM livros WHERE titulo = :titulo');
        $stmt->execute(['titulo' => $titulo]);
        verificar((int) $stmt->fetchColumn() === 0, 'O registro não foi desfeito.');
    });

    echo "\n[SUCESSO] 13 testes de integração aprovados; dados de teste revertidos.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "[FALHA] {$e->getMessage()}\n");
    exit(1);
}
