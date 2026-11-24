# Atividade — Aula 09 (Banco de Dados II)

As oito questões abaixo correspondem ao arquivo de atividade fornecido, que já veio respondido. As respostas foram preservadas para consulta junto ao projeto da Aula 11.

**1. Por que a tabela `livros_autores` é necessária para representar a relação N:N entre livros e autores?**

Porque um livro pode ter vários autores e um autor pode escrever vários livros. A tabela `livros_autores` serve para relacionar essas informações, armazenando as chaves estrangeiras dos livros e dos autores.

**2. Como distinguir, em um resultado de JOIN, a repetição de uma entidade da existência de entidades distintas?**

Podemos identificar isso pela chave primária (ID). Se o mesmo ID aparecer em várias linhas, significa que é a mesma entidade sendo repetida por causa do relacionamento. Se os IDs forem diferentes, são entidades distintas.

**3. O que ocorre quando uma consulta LEFT JOIN não encontra exemplares para determinado livro?**

O livro continua aparecendo no resultado da consulta, mas os campos relacionados aos exemplares ficam com valor NULL, indicando que não existem exemplares associados.

**4. Quais restrições de integridade devem permanecer no MySQL mesmo quando a aplicação utiliza ORM?**

Devem permanecer as chaves primárias, chaves estrangeiras, restrições NOT NULL, UNIQUE e outras regras necessárias para garantir que os dados sejam válidos e que os relacionamentos estejam corretos.

**5. Qual é a diferença entre hidratar entidades completas e retornar resultados agregados?**

Hidratar entidades significa transformar os dados retornados do banco em objetos completos, com seus atributos e relacionamentos. Já os resultados agregados retornam informações resumidas, como a quantidade de livros ou o total de exemplares.

**6. Como o problema N+1 pode afetar uma listagem de livros e autores?**

O problema N+1 acontece quando o sistema faz uma consulta para buscar os livros e depois realiza outra consulta para buscar os autores de cada livro. Isso aumenta a quantidade de consultas ao banco e pode deixar o sistema mais lento.

**7. De que maneira o EXPLAIN contribui para avaliar consultas executadas por um ORM?**

O EXPLAIN permite analisar como o MySQL executa uma consulta, mostrando informações como os índices utilizados e a quantidade estimada de linhas analisadas. Assim, podemos identificar problemas de desempenho e melhorar as consultas geradas pelo ORM.

**8. Qual a diferença de responsabilidade entre uma entidade de domínio e um repositório de persistência?**

A entidade de domínio representa os dados e as regras de negócio do sistema, como um livro e suas informações. Já o repositório é responsável pela comunicação com o banco de dados, realizando operações como cadastrar, buscar, atualizar e excluir registros.
