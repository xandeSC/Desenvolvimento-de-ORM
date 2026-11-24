# BibliotecaORM — Avaliação Prática de Programação II

**UNOESC | Ciência da Computação | Professor: Leandro Otavio Cordova Vieira**  
**Projeto individual: mini-ORM Data Mapper em PHP puro, PDO e MySQL 8**

## Objetivo

Implementar mapeamento objeto-relacional próprio (sem Laravel, Doctrine ou ORM externo), com domínio desacoplado do SQL. `Livro` e `Autor` representam as entidades; `AbstractRepository` implementa CRUD reutilizável; `LivroRepository` e `AutorRepository` especializam o mapeamento; `ConsultaPolimorfica` prova a substituição por `RepositoryInterface`.

**O projeto não possui tela de navegador:** a avaliação exige um ORM e sua demonstração reproduzível no terminal, não uma interface web.

## Como executar no Windows (notebook Cataneo)

1. Instale/use **PHP 8.2+** com a extensão `pdo_mysql` e **MySQL 8.0**. Uma instalação do MySQL já pode existir no notebook.
2. **Dê dois cliques em `INICIAR_BIBLIOTECAORM.cmd`.**
3. Informe o usuário MySQL (normalmente `root`, para criar os bancos) e digite a senha **apenas no PowerShell**. A senha não é gravada no código nem no log.
4. O script cria/atualiza os bancos `biblioteca_orm` e `biblioteca_orm_teste`, verifica os 5 testes de domínio, roda 13 testes de integração e demonstra as 7 evidências exigidas.
5. Veja a saída no terminal e a cópia salva em `evidencias/execucao-AAAAMMDD-HHMMSS.txt`. O registro é gerado somente após a execução real. Se houver erro, a janela mostra a etapa que falhou.

Se a conexão falhar com `Access denied`: confira a senha e privilégios de criação de banco do usuário usado. **Não redefina a senha de um banco existente para resolver um exercício.**

### Execução manual (PowerShell)

Configure as variáveis *na sessão atual* (não coloque senhas no Git):

```powershell
$env:DB_HOST='127.0.0.1'
$env:DB_USER='seu_usuario'
# Para informar a senha sem exibí-la, prefira o iniciador .cmd
$cred = Read-Host 'Senha MySQL' -AsSecureString
$ptr = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($cred)
$env:DB_PASS = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($ptr)
[Runtime.InteropServices.Marshal]::ZeroFreeBSTR($ptr)
$env:DB_NAME='biblioteca_orm'
php preparar_bancos.php
php tests/domain.php
$env:DB_NAME='biblioteca_orm_teste'
php tests/smoke.php
$env:DB_NAME='biblioteca_orm'
php demo.php
Remove-Item Env:\DB_PASS
```

> Para instalações do PHP sem `php.ini`, o iniciador `.cmd` já carrega `pdo_mysql` apenas para o programa. Manualmente, use `php -d "extension_dir=C:\caminho\para\php\ext" -d extension=pdo_mysql` no lugar de `php`.

## Estrutura

```text
biblioteca-orm/
  src/Domain/{Livro,Autor}.php
  src/Infrastructure/{RepositoryInterface,AbstractRepository,Conexao,LivroRepository,AutorRepository}.php
  src/Application/ConsultaPolimorfica.php
  sql/{schema.sql,schema_teste.sql}
  tests/{domain.php,smoke.php}
  diagramas/{diagrama_classes.drawio,diagrama_classes.png,diagrama_classes.svg}
  relatorio/Relatorio_Tecnico_BibliotecaORM.pdf
  evidencias/README.md
  autoload.php
  demo.php
  preparar_bancos.php
  INICIAR_BIBLIOTECAORM.{cmd,ps1}
  CHECKLIST_AVALIACAO.md
  .env.example
  .gitignore
```

## Evidências da avaliação — onde observar

1. **INSERT de Livro e dois Autores + IDs:** `demo.php`, saída `[1]`.
2. **SELECT por ID + hidratação:** `demo.php`, saída `[2/4]`.
3. **UPDATE comprovado por nova busca:** `demo.php`, saída `[3]`.
4. **N:N de dois autores e reconstrução da coleção:** `LivroRepository::buscarComAutores()`, `demo.php`, saída `[2/4]`.
5. **Polimorfismo por interface:** `ConsultaPolimorfica::buscar(RepositoryInterface, int)`, `demo.php`, saída `[5]`.
6. **Transação falha + rollback:** `demo.php`, saída `[6]`; teste `smoke.php` caso 13.
7. **DELETE e restrição de FK:** `demo.php`, saída `[7]`.

Além disso, a execução exibe `listar()`, e o teste `smoke.php` verifica PK composta, FK, título vazio, prepared statements e preservação de tipos.

## Banco de dados

`sql/schema.sql` cria `biblioteca_orm`, com `livros`, `autores`, `livros_autores` e `exemplares`. A PK composta `(livro_id, autor_id)` impede associações duplicadas. Chaves estrangeiras protegem os vínculos; a exclusão de autor associado não é permitida sem desvincular.

O banco de testes **separado** `biblioteca_orm_teste` usa esquema equivalente; `tests/smoke.php` não roda no banco principal. Os testes de integração fazem `rollBack()` e não mantêm registros. **A demonstração deixa os dois autores e o livro de exemplo no banco principal**; múltiplas execuções geram exemplos adicionais identificáveis pelo sufixo aleatório.

## Princípios de POO e decisões arquiteturais

- **Encapsulamento:** IDs privados só podem ser estabelecidos inicialmente ou reatribuídos com o mesmo valor, título/nome não podem ser vazios.
- **Herança:** `AbstractRepository` oferece `buscar`, `listar`, `salvar` e `excluir`.
- **Abstração:** as subclasses definem a tabela, colunas, `hidratar()`, `extrair()` e identidade.
- **Polimorfismo:** o consumidor recebe `RepositoryInterface`, sem testar o tipo concreto.
- **Hidratação:** as linhas SQL são convertidas em `Livro` e `Autor`, preservando IDs `int`, ano `?int` e estado.
- **Segurança:** SQL usa identificadores de tabelas/colunas **fixados no código**, valores via `prepare`/`execute`, sem concatenar entradas do usuário.
- **N:N:** associação persistida em `livros_autores`, coleção reconstruída explicitamente por `buscarComAutores()`.

## Limitações conhecidas

O ORM é didático e limitado a PK inteira denominada `id`; sem migrations, query builder genérico, reflection, lazy loading, identity map, otimização de carregamento em lote, transação gerenciada automaticamente pelo repositório, controle de concorrência ou exclusão em cascata automática. `buscarComAutores()` é uma opção explícita para recuperar os autores, não um relacionamento lazy.

## Entrega no portal (prazo da avaliação: 24/11/2026)

- **Repositório Git acessível ao professor, com histórico de commits:** inicialize no seu GitHub como público ou conceda acesso ao professor. A pasta já pode incluir histórico Git local, mas isso **não equivale** à publicação.
- **Relatório técnico PDF (2 a 4 páginas):** `relatorio/Relatorio_Tecnico_BibliotecaORM.pdf`.
- **Diagrama de classes:** `diagramas/diagrama_classes.svg` (também PNG e fonte `.drawio`).
- **Evidências reais:** execute o iniciador e revise o arquivo `evidencias/execucao-*.txt` e faça commit dele (ele não é mais ignorado pelo Git).
- **Autenticidade:** o autor deve ser capaz de explicar as principais decisões e demonstrar que entende o código.

Sem senhas reais, dados pessoais de terceiros ou diretórios de dependências na publicação.
