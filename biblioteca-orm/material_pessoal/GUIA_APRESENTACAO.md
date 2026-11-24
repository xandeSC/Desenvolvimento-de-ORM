# Guia para explicar a avaliação de Programação II

## Resumo para falar em 1 minuto

Meu projeto é um mini-ORM em PHP puro e MySQL, construído no padrão **Data Mapper**. Uma entidade (`Livro` ou `Autor`) guarda os dados e validações, mas **não faz SQL**. Os repositórios convertem objetos em registros e registros em objetos. A classe abstrata contém CRUD comum; os repositórios concretos indicam tabela, colunas e como mapear. A interface `RepositoryInterface` me permite buscar entidades sem saber qual repositório concreto foi passado.

## Demonstração no terminal

Execute `INICIAR_BIBLIOTECAORM.cmd` com usuário e senha MySQL da sua máquina. Ele prepara bancos, executa 5 testes de domínio e 13 de integração e mostra 7 evidências em `demo.php`. Arquivos de log ficam em `evidencias/execucao-*.txt`, somente depois de rodar.

1. **INSERT:** cria Livro + dois Autores na mesma transação e mostra IDs MySQL.
2. **SELECT:** recupera o Livro e verifica hidratação (ID, título, ano).
3. **UPDATE:** altera título e mostra nova consulta.
4. **N:N:** associa dois autores e monta a lista em `buscarComAutores()`; `adicionarAutor` evita repetição.
5. **Polimorfismo:** `ConsultaPolimorfica::buscar()` recebe `RepositoryInterface` e é chamado com os dois repositórios.
6. **Rollback:** insere livro e tenta associar autor inexistente; FK falha; rollback desfaz INSERT.
7. **DELETE e FK:** exclui Livro temporário sem vínculos e mostra bloqueio ao excluir Autor vinculado.

## Perguntas que o professor pode fazer

- **Por que isto é ORM e não só PDO?** PDO é driver; os métodos `hidratar()` e `extrair()` mapeiam dados entre linhas e objetos, com separação de responsabilidades.
- **Onde há herança?** `LivroRepository extends AbstractRepository` e `AutorRepository extends AbstractRepository`.
- **Onde está a interface?** `RepositoryInterface` define `buscar`, `listar`, `salvar` e `excluir`.
- **Como funcionam INSERT vs UPDATE?** `AbstractRepository::salvar()` identifica `id() === null` para inserir; com ID ele atualiza.
- **Como evita SQL injection?** parâmetros são passados separadamente a `prepare/execute`. Nomes de tabela/coluna vêm apenas do código.
- **Por que N:N precisa de tabela intermediária?** um Livro pode ter muitos Autores e um Autor pode ter muitos Livros; a PK composta impede vínculos repetidos.
- **Qual o limite do mini-ORM?** não tem lazy loading, identity map, migrations, detecção de atualização concorrente nem batch loading.
- **Por que FK ainda é útil?** uma regra do MySQL protege os dados mesmo que o código PHP tenha bugs.
- **Por que usar duas bases?** a demonstração mantém exemplos; testes de integração usam `biblioteca_orm_teste` com rollback.

**A avaliação é individual:** estude o código e execute o projeto pessoalmente antes de apresentar ou postar.
