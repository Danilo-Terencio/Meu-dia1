# Instalação e implantação

## 1. Pré-requisitos

Para executar a aplicação em contêiner:

- Docker instalado.
- Um banco PostgreSQL acessível pela aplicação.
- Credenciais de conexão PostgreSQL.
- Esquema criado conforme [BANCO-DE-DADOS.md](BANCO-DE-DADOS.md).

O contêiner fornece PHP 8.3 com Apache. O Dockerfile instala as bibliotecas e extensões `pdo_pgsql` e `mbstring`, ativa o módulo Apache `rewrite` e expõe a porta 80.

## 2. Preparar o banco

1. Crie um banco PostgreSQL ou um projeto Supabase.
2. Abra o SQL Editor ou conecte-se por um cliente PostgreSQL.
3. Execute o esquema de [BANCO-DE-DADOS.md](BANCO-DE-DADOS.md).
4. Confirme que existem as tabelas `usuarios`, `dados_usuario` e `login_tentativas`.

Não use o arquivo MySQL antigo `projetointegrador/database.sql` para configurar a versão atual.

## 3. Construir a imagem localmente

Na raiz do repositório:

```bash
docker build -t meu-dia-plus .
```

O Dockerfile:

1. Usa a imagem base `php:8.3-apache`.
2. Instala `libonig-dev` e `libpq-dev`.
3. Compila as extensões PHP `pdo_pgsql` e `mbstring`.
4. Ativa `mod_rewrite` no Apache.
5. Copia o conteúdo de `projetointegrador/` para `/var/www/html/`.
6. Remove do diretório público os arquivos SQL/textuais e o arquivo ZIP indicado no próprio Dockerfile.
7. Define `inicial.html` como primeira opção de `DirectoryIndex`, seguida por `index.php` e `index.html`.

O arquivo `login.php` que está na raiz do repositório não é copiado para a imagem. O login usado pelo aplicativo está em `projetointegrador/login.php`.

## 4. Executar localmente

### Opção A — variáveis individuais

```bash
docker run --rm --name meu-dia-plus \
  -p 8080:80 \
  -e DB_HOST=SEU_HOST_POSTGRES \
  -e DB_PORT=5432 \
  -e DB_NAME=postgres \
  -e DB_USER=SEU_USUARIO \
  -e DB_PASSWORD=SUA_SENHA \
  meu-dia-plus
```

### Opção B — URL completa

```bash
docker run --rm --name meu-dia-plus \
  -p 8080:80 \
  -e DATABASE_URL='postgresql://USUARIO:SENHA@HOST:5432/postgres' \
  meu-dia-plus
```

A aplicação tenta ler `DATABASE_URL` primeiro. Se essa variável estiver definida, os valores individuais são ignorados. Codifique caracteres especiais da URL, principalmente os presentes na senha.

Abra [http://localhost:8080](http://localhost:8080).

Para parar o contêiner, use `Ctrl+C` no terminal em primeiro plano. Com a opção `--rm`, o contêiner é removido ao parar; os dados permanecem no PostgreSQL externo.

## 5. Variáveis de ambiente

| Variável | Padrão / requisito | Uso |
|---|---|---|
| `DATABASE_URL` | Opcional; tem prioridade | URL completa do PostgreSQL. |
| `DB_HOST` | Obrigatória sem URL | Host do PostgreSQL. |
| `DB_PORT` | `5432` | Porta do serviço. |
| `DB_NAME` | `postgres` | Nome do banco. |
| `DB_USER` | Obrigatória sem URL | Usuário PostgreSQL. |
| `DB_PASSWORD` | Obrigatória sem URL | Senha PostgreSQL. |

A conexão é construída com `sslmode=require` e tempo máximo de conexão de 10 segundos. Verifique se o host permite conexões a partir do serviço de hospedagem.

## 6. Implantação no Render

O repositório possui um Dockerfile na raiz, mas não contém um arquivo `render.yaml`. Portanto, a configuração do serviço precisa ser feita no painel do Render ou adicionada posteriormente como Blueprint.

Fluxo de configuração:

1. Crie um Web Service conectado ao repositório.
2. Selecione a implantação por Docker e mantenha o contexto na raiz do repositório.
3. Confirme que o Dockerfile selecionado é `Dockerfile`, na raiz.
4. Cadastre as variáveis PostgreSQL como Environment Variables/Secrets.
5. Execute o deploy e verifique os logs de build e de inicialização.
6. Abra o endereço público e teste cadastro, login e persistência.

A imagem declara `EXPOSE 80` e inicia o Apache da imagem oficial PHP nessa porta. A porta configurada para o serviço precisa corresponder à porta em que o Apache está escutando.

Não adicione credenciais reais ao repositório, ao Dockerfile ou a este documento.

## 7. Checklist de validação

Depois da implantação, faça os testes abaixo:

- [ ] A página pública abre na raiz do domínio.
- [ ] O cadastro aceita dados válidos e rejeita campos inválidos.
- [ ] O cadastro impede e-mails duplicados.
- [ ] O login válido abre o painel.
- [ ] O login inválido apresenta uma mensagem genérica.
- [ ] O painel carrega os dados da conta autenticada.
- [ ] Uma tarefa pode ser criada e permanece após atualizar a página.
- [ ] Agenda, notas, gastos, compras, estudos e trabalho podem ser salvos.
- [ ] Dados de contas diferentes não se misturam.
- [ ] O tema escolhido permanece após novo login.
- [ ] A alteração de senha exige a senha atual.
- [ ] A exportação CSV gera um arquivo.
- [ ] O logout encerra a sessão e impede acesso direto ao painel.

## 8. Diagnóstico de problemas

| Sintoma | Verificações |
|---|---|
| Mensagem de banco não configurado | Confirme `DB_HOST`, `DB_USER` e `DB_PASSWORD`, ou defina `DATABASE_URL`. |
| Erro de conexão | Confira host, porta, nome do banco, usuário, senha, regras de rede e SSL. |
| Login falha sempre | Verifique se a tabela `usuarios` existe, se a conta foi criada e se o hash de senha foi armazenado corretamente. |
| Login não aplica limite de tentativas | Confirme a existência e as permissões da tabela `login_tentativas`. |
| Dados não são salvos | Inspecione a resposta de `api.php`, a sessão, o cabeçalho JSON, a tabela `dados_usuario` e os logs PHP. |
| Página em branco ou HTTP 500 | Consulte os logs do serviço e os logs de erro PHP/Apache. Não exiba mensagens internas ou credenciais ao visitante. |
| Arquivos CSS/JS antigos aparecem | O painel usa `filemtime` para versionar `painel.css` e `painel.js`; confirme se a nova imagem foi implantada. |

## 9. Arquivos que não são necessários para o build principal

Não há etapa de compilação JavaScript. A imagem Docker já instala as extensões PHP necessárias. O Dockerfile remove do diretório servido alguns arquivos de apoio: `database.sql`, `files.zip`, `script do banco valoriza.txt` e `paginaprincipal.html.txt`.

Isso não significa que todos os arquivos antigos foram isolados. Veja [LEGADO-VALORIZA.md](LEGADO-VALORIZA.md) e [SEGURANCA.md](SEGURANCA.md) antes de disponibilizar o serviço publicamente.
