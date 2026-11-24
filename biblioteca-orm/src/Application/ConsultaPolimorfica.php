<?php

declare(strict_types=1);

namespace Biblioteca\Application;

use Biblioteca\Infrastructure\RepositoryInterface;

/** Exemplo explícito de polimorfismo: não depende do tipo de repositório. */
final class ConsultaPolimorfica
{
    public static function buscar(RepositoryInterface $repositorio, int $id): ?object
    {
        return $repositorio->buscar($id);
    }
}
