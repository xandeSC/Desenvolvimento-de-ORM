# Publicar o BibliotecaORM no GitHub e entregar ao professor

**O pacote de entrega já inclui um repositório Git LOCAL e histórico real de commits.** Ele ainda não está publicado no GitHub e, portanto, um link público não pode ser fornecido antes do envio.

1. Extraia o ZIP final em uma pasta (não copie somente os arquivos isolados: mantenha a pasta oculta `.git`).
2. Entre em https://github.com/new pela sua conta GitHub. Crie um repositório chamado `biblioteca-orm-aula11`, **sem marcar** “Add README”, `.gitignore` ou License (esses itens já existem no projeto).
3. Abra a pasta do projeto no VS Code e abra **Terminal > Novo Terminal**.
4. Se precisar, ajuste o URL remoto e publique:

```powershell
git status
git log --oneline -4
git remote add origin https://github.com/brunorobertocataneo/biblioteca-orm-aula11.git
git push -u origin main
```

Se aparecer `error: remote origin already exists`, confira `git remote -v` e altere com `git remote set-url origin ...`, em vez de adicionar outra vez. A autenticação pode ocorrer pelo navegador do Git Credential Manager.

5. Confira no GitHub se aparecem **README, sql/schema.sql, fontes, testes, diagramas e relatório**. Copie o link `https://github.com/brunorobertocataneo/biblioteca-orm-aula11` para o portal do professor **somente depois** de verificar a publicação.
6. Execute o programa no notebook com `INICIAR_BIBLIOTECAORM.cmd`, confirme os 13 testes no MySQL e anexar a saída real de `evidencias/execucao-*.txt`, após revisar para evitar dados sensíveis.

Não publique senhas, `.env`, dados pessoais ou logs de clientes. Se o repositório for privado, conceda acesso ao professor.
