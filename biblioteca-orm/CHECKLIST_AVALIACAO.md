# Checklist de avaliação - BibliotecaORM

Base: **Aula11 - Avaliacao Pratica BibliotecaORM.pdf** da UNOESC (Programação II).

| Item | Prova no projeto | Estado |
|---|---|---|
| R1 Modelo relacional PK/FK | `sql/schema.sql` | Implementado |
| R2 Livro e Autor encapsulados | `src/Domain` + `tests/domain.php` | Implementado e testado sem BD |
| R3 Interface CRUD | `RepositoryInterface.php` | Implementado |
| R4 AbstractRepository + duas subclasses | `src/Infrastructure` | Implementado |
| R5 Polimorfismo em método tipado | `src/Application/ConsultaPolimorfica.php`, `demo.php` | Implementado |
| R6 CRUD + listar, autor INSERT/SELECT | `demo.php`, `tests/smoke.php` | Implementado; depende de MySQL para confirmar |
| R7 Hidratação de objetos | `LivroRepository`, `AutorRepository` | Implementado; depende de MySQL |
| R8 N:N com dois autores sem repetição | `buscarComAutores`, `demo.php`, `tests/smoke.php` | Implementado; depende de MySQL |
| R9 Prepared statements, FK, rollback | `AbstractRepository.php`, `demo.php`, `tests/smoke.php` | Implementado; depende de MySQL |
| R10 Organização e testes | `README.md`, `sql/`, `tests/` | Implementado |
| Evidências 1–7 | `demo.php` | A executar no banco e anexar saída real |
| Relatório de 2–4 páginas | `relatorio/Relatorio_Tecnico_BibliotecaORM.pdf` | Incluído |
| Diagrama UML | `diagramas/` | Incluído |
| Git com histórico | `.git` local (se existir no notebook) | Publicação remota ainda depende de um repositório GitHub |

**Importante:** marcação 'Implementado' não é confirmação de que a integração com MySQL foi executada com sucesso. A rubrica pede funcionamento e explicação. A execução deve ser registrada localmente.
