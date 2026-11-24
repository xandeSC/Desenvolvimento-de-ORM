<?php

declare(strict_types=1);

require_once __DIR__ . '/autoload.php';

use Biblioteca\Application\ConsultaPolimorfica;
use Biblioteca\Domain\Autor;
use Biblioteca\Domain\Livro;
use Biblioteca\Infrastructure\AutorRepository;
use Biblioteca\Infrastructure\Conexao;
use Biblioteca\Infrastructure\LivroRepository;

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("A demonstração deve ser executada pelo terminal: php demo.php\n");
}

function conferirDemo(bool $condicao, string $mensagem): void
{
    if (!$condicao) {
        throw new RuntimeException($mensagem);
    }
}

try {
    $pdo = Conexao::abrir();
    $livros = new LivroRepository($pdo);
    $autores = new AutorRepository($pdo);
    $tag = substr(bin2hex(random_bytes(4)), 0, 8); // evita confundir execuções repetidas

    echo "=== BibliotecaORM: demonstração completa ===\n";
    echo "R1-R2-R4: modelo relacional, entidades encapsuladas e repositórios especializados\n\n";

    // EVIDÊNCIA 1 e R9: dois autores e um livro com vínculos em uma transação.
    $pdo->beginTransaction();
    try {
        $autor1 = new Autor('Ana Silva ' . $tag, 'Brasil');
        $autor2 = new Autor('Carlos Pereira ' . $tag, 'Brasil');
        $autores->salvar($autor1);
        $autores->salvar($autor2);
        $livro = new Livro('Introdução à Programação ' . $tag, null, 2026);
        $livros->salvar($livro);
        $livros->associarAutor($livro->id(), $autor1->id());
        $livros->associarAutor($livro->id(), $autor2->id());
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
    echo "[1] INSERT + IDs MySQL: livro #{$livro->id()}, autores #{$autor1->id()} e #{$autor2->id()}\n";

    // EVIDÊNCIAS 2 e 4: reconstrução da entidade e de sua coleção N:N.
    $recuperado = $livros->buscarComAutores($livro->id());
    conferirDemo($recuperado instanceof Livro, 'Livro não foi recuperado.');
    conferirDemo(count($recuperado->autores()) === 2, 'Era esperado exatamente 2 autores.');
    $ids = array_map(static fn(Autor $a): int => $a->id(), $recuperado->autores());
    conferirDemo(count(array_unique($ids)) === 2, 'Existem autores duplicados na coleção.');
    echo "[2/4] SELECT + hidratação: #{$recuperado->id()} '{$recuperado->titulo()}', ano {$recuperado->anoPublicacao()}\n";
    echo '      Autores recuperados: ' . implode(' e ', array_map(static fn(Autor $a) => $a->nome(), $recuperado->autores())) . "\n";

    // R6: LISTAR, além do SELECT por id.
    $listagem = $livros->listar();
    conferirDemo(in_array($livro->id(), array_map(static fn(Livro $x): int => $x->id(), $listagem), true), 'O livro não apareceu na listagem.');
    echo '[R6] LISTAR livros: ' . count($listagem) . " registro(s), incluindo o livro #{$livro->id()}\n";

    // EVIDÊNCIA 3: UPDATE e consulta posterior.
    $recuperado->alterarTitulo('POO, PDO e MySQL ' . $tag);
    $livros->salvar($recuperado);
    conferirDemo($livros->buscar($livro->id())->titulo() === $recuperado->titulo(), 'UPDATE não persistiu.');
    echo '[3] UPDATE e nova consulta: ' . $livros->buscar($livro->id())->titulo() . "\n";

    // EVIDÊNCIA 5: uma única função recebe os dois tipos de repositório.
    $consultaLivro = ConsultaPolimorfica::buscar($livros, $livro->id());
    $consultaAutor = ConsultaPolimorfica::buscar($autores, $autor1->id());
    conferirDemo($consultaLivro instanceof Livro && $consultaAutor instanceof Autor, 'Consulta polimórfica falhou.');
    echo '[5] POLIMORFISMO via RepositoryInterface: ' . get_class($consultaLivro) . ' / ' . get_class($consultaAutor) . "\n";

    // EVIDÊNCIA 6: inserção invalidada por FK e desfeita por rollback.
    $tituloRollback = 'Teste de rollback ' . $tag;
    $pdo->beginTransaction();
    try {
        $invalido = new Livro($tituloRollback);
        $livros->salvar($invalido);
        $livros->associarAutor($invalido->id(), 2147483647);
        throw new RuntimeException('A FK deveria ter rejeitado a associação inválida.');
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        conferirDemo($e->getCode() === '23000', 'Esperada violação de FK SQLSTATE 23000.');
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM livros WHERE titulo = :titulo');
        $stmt->execute(['titulo' => $tituloRollback]);
        conferirDemo((int) $stmt->fetchColumn() === 0, 'Rollback deixou registro no banco.');
        echo "[6] ROLLBACK após FK inválida: livro desfeito (0 registros)\n";
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }

    // EVIDÊNCIA 7: exclusão permitida e restrição de exclusão do livro vinculado.
    $temporario = new Livro('Livro temporário ' . $tag);
    $livros->salvar($temporario);
    $idTemp = $temporario->id();
    $livros->excluir($idTemp);
    conferirDemo($livros->buscar($idTemp) === null, 'DELETE temporário falhou.');
    echo "[7] DELETE controlado: livro temporário #{$idTemp} removido\n";
    try {
        $autores->excluir($autor1->id());
        throw new RuntimeException('MySQL deveria ter impedido excluir autor com livro vinculado.');
    } catch (PDOException $e) {
        conferirDemo($e->getCode() === '23000', 'Esperada restrição de FK.');
        echo "[7] Integridade referencial: FK bloqueou exclusão de autor vinculado\n";
    }

    echo "\n[SUCESSO] 7 evidências da avaliação demonstradas.\n";
    echo "Os registros principais foram mantidos para consulta no banco; novas execuções inserem exemplos adicionais.\n";
} catch (Throwable $erro) {
    fwrite(STDERR, "[FALHA] {$erro->getMessage()}\n");
    exit(1);
}
