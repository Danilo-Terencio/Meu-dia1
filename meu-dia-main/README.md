# MeuDia+

Aplicação web de organização pessoal. O MeuDia+ reúne tarefas, agenda, anotações, finanças, listas de compras, estudos e atividades profissionais em um painel associado à conta de cada usuário.

A aplicação principal utiliza **PHP 8.3 com Apache**, **PostgreSQL** (incluindo Supabase como provedor compatível), **HTML**, **CSS** e **JavaScript puro**. O projeto possui uma imagem Docker para execução e implantação.

> **Atenção sobre o banco:** o arquivo `projetointegrador/database.sql` contém uma versão antiga do esquema em MySQL. A aplicação principal atual usa PostgreSQL e PDO_PGSQL. Para preparar o banco de produção, utilize o esquema PostgreSQL documentado em [docs/BANCO-DE-DADOS.md](docs/BANCO-DE-DADOS.md).

## Funcionalidades

- Cadastro, login e encerramento de sessão.
- Painel individual com tarefas, prioridades e prazos.
- Agenda de compromissos com data, hora e local.
- Anotações com título e conteúdo.
- Controle financeiro com renda, gastos, categorias, saldo e histórico.
- Lista de compras com marcação de itens concluídos.
- Organização de atividades de estudo e trabalho.
- Busca de informações cadastradas no painel.
- Perfil com nome, cidade, profissão, objetivo e apresentação pessoal.
- Tema claro/escuro.
- Alteração de senha e exportação de dados em CSV.
- Atalhos informativos de emergência disponíveis na interface.

Os dados do painel são isolados por usuário e persistidos no PostgreSQL.

## Tecnologias

| Camada | Tecnologia |
|---|---|
| Servidor web | Apache |
| Backend | PHP 8.3 |
| Acesso ao banco | PDO |
| Banco principal | PostgreSQL |
| Extensões PHP | `pdo_pgsql`, `mbstring` |
| Interface | HTML5 e CSS3 |
| Interatividade | JavaScript puro |
| Empacotamento | Docker |

Não há `package.json` ou `composer.json` no projeto atual; a interface não depende de um framework JavaScript e o backend não declara dependências Composer.

## Execução local com Docker

### 1. Preparar o PostgreSQL

Crie um banco PostgreSQL ou um projeto Supabase. Execute o script de criação de tabelas disponível em [docs/BANCO-DE-DADOS.md](docs/BANCO-DE-DADOS.md) no banco escolhido.

### 2. Construir a imagem

Na raiz do repositório:

```bash
docker build -t meu-dia-plus .
```

### 3. Iniciar o contêiner

Configure os dados de conexão do seu PostgreSQL:

```bash
docker run --rm -p 8080:80 \
  -e DB_HOST=SEU_HOST \
  -e DB_PORT=5432 \
  -e DB_NAME=postgres \
  -e DB_USER=SEU_USUARIO \
  -e DB_PASSWORD=SUA_SENHA \
  meu-dia-plus
```

Também é possível usar uma única variável `DATABASE_URL`:

```bash
docker run --rm -p 8080:80 \
  -e DATABASE_URL='postgresql://USUARIO:SENHA@HOST:5432/postgres' \
  meu-dia-plus
```

A variável `DATABASE_URL`, quando definida, tem prioridade sobre as variáveis individuais. Caracteres especiais no usuário ou na senha devem estar codificados para uso em uma URL.

### 4. Acessar

Abra [http://localhost:8080](http://localhost:8080). O Apache está configurado para abrir `inicial.html` como página inicial.

## Variáveis de ambiente

| Variável | Obrigatória | Descrição |
|---|---:|---|
| `DATABASE_URL` | Alternativa | URL completa de conexão PostgreSQL. Se definida, substitui as variáveis individuais. |
| `DB_HOST` | Sim, sem `DATABASE_URL` | Host do servidor PostgreSQL. |
| `DB_PORT` | Não | Porta PostgreSQL. Padrão: `5432`. |
| `DB_NAME` | Não | Nome do banco. Padrão: `postgres`. |
| `DB_USER` | Sim, sem `DATABASE_URL` | Usuário do banco. |
| `DB_PASSWORD` | Sim, sem `DATABASE_URL` | Senha do banco. |

A conexão PDO exige SSL (`sslmode=require`). Cadastre credenciais como variáveis privadas/Secrets do ambiente de hospedagem. Não coloque senhas em arquivos versionados.

## Estrutura principal

```text
.
├── Dockerfile
├── README.md
├── login.php                     # arquivo antigo na raiz; não é copiado pela imagem
├── docs/
└── projetointegrador/
    ├── inicial.html              # página pública inicial
    ├── login.php                 # login atual do MeuDia+
    ├── cadastro.php              # cadastro atual
    ├── index.php                 # painel autenticado
    ├── conexao.php               # conexão PostgreSQL
    ├── sessao.php                # configuração da sessão
    ├── api.php                   # persistência dos dados do painel
    ├── senha.php                 # alteração de senha
    ├── logout.php                # encerramento da sessão
    ├── painel.js                 # lógica dos módulos do painel
    ├── painel.css                # estilos do painel
    └── ...
```

O repositório também contém arquivos antigos e protótipos do sistema VALORIZA+. Eles não fazem parte do fluxo principal do MeuDia+ e são descritos em [docs/LEGADO-VALORIZA.md](docs/LEGADO-VALORIZA.md).

## Documentação

- [Arquitetura e funcionalidades](docs/ARQUITETURA.md)
- [Banco de dados PostgreSQL](docs/BANCO-DE-DADOS.md)
- [Instalação e implantação](docs/INSTALACAO-DEPLOY.md)
- [Segurança e limitações conhecidas](docs/SEGURANCA.md)
- [Arquivos legados do VALORIZA+](docs/LEGADO-VALORIZA.md)

## Pontos importantes antes de produção

1. Criar as três tabelas PostgreSQL descritas na documentação. O script MySQL antigo não é compatível com a conexão PostgreSQL atual.
2. Revisar e isolar os arquivos legados VALORIZA+ que continuam dentro da pasta copiada para o diretório público do Apache.
3. Configurar as credenciais do banco exclusivamente por Secrets/variáveis de ambiente.
4. Validar cadastro, login, persistência, alteração de senha e exportação no ambiente de implantação.

**Estado da documentação:** baseada na estrutura e no código da branch `main`, revisados em 03/11/2026.
