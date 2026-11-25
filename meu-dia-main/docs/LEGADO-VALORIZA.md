# Arquivos legados e protótipos VALORIZA+

O repositório contém arquivos de uma aplicação anterior chamada **VALORIZA+**, voltada ao cadastro de funcionários, avaliação de competências e relatórios gerenciais. Esses arquivos coexistem com o MeuDia+, mas não usam a mesma arquitetura.

## 1. Diferenças de arquitetura

| MeuDia+ atual | VALORIZA+ legado |
|---|---|
| PHP 8.3 + Apache no Dockerfile. | Scripts PHP antigos. |
| PostgreSQL via PDO_PGSQL. | MySQL local via PDO. |
| Configuração por variáveis de ambiente. | Scripts contêm configuração de conexão diretamente no código. |
| Sessão com `usuario_id`. | Fluxos antigos usam campos de sessão diferentes, como CPF ou ID. |
| Dados do painel em `dados_usuario`. | Tabelas específicas de funcionários, competências e eleições mensais. |

O código VALORIZA+ não deve ser tratado como parte funcional integrada ao MeuDia+ sem uma migração e uma revisão de autenticação, banco e permissões.

## 2. Funcionalidades identificadas no código legado

### Cadastro de funcionários

O arquivo `cadastro_funcionario.php` recebe nome, e-mail e CPF, valida os campos e grava funcionários no banco antigo. O próprio código comenta que o CPF era usado como senha inicial e que a senha era armazenada como hash.

Usar um documento pessoal como senha inicial não é uma prática recomendada. Esse fluxo precisa ser substituído por convite ou criação de senha segura, com procedimento de primeiro acesso.

### Avaliação mensal de competências

O arquivo `principal.php` contém o fluxo de avaliação de competências. O código lista 14 competências, entre elas amabilidade, autonomia, comunicação, trabalho em equipe, criatividade, empatia, ética, gestão do tempo, proatividade e resiliência.

A lógica consulta a tabela `eleicoes_mensais` para verificar se o avaliador já registrou uma votação naquela competência e naquele mês. A restrição única prevista no script antigo impede repetir o mesmo voto para a combinação de avaliador, mês e competência.

### Relatórios gerenciais

O arquivo `relatorio.php` apresenta relatórios com filtros de mês, período, funcionário e competência. O código contém consultas para funcionários reconhecidos, competências valorizadas, participação e evolução das avaliações.

## 3. Arquivos relacionados

| Arquivo | Classificação / conteúdo |
|---|---|
| `principal.php` | Fluxo antigo de avaliação mensal de competências. |
| `cadastro_funcionario.php` | Cadastro legado de funcionários. |
| `relatorio.php` | Relatórios gerenciais do VALORIZA+. |
| `index.html` | Tela antiga identificada como VALORIZA+. |
| `login.html` | Página HTML antiga de encaminhamento/login. |
| `projeto.html` | Protótipo de interface relacionado ao projeto de avaliação. |
| `projetointegrador.html` | Protótipo estático de painel. |
| `paginaprincipal.html.txt` | Arquivo textual de referência antiga. |
| `script do banco valoriza.txt` | Script antigo MySQL para usuários, competências e eleições mensais. |
| `principal.css`, `estilo.css`, `relatorio.css` | Estilos de telas antigas. |

O arquivo `files.zip` também está versionado, mas o Dockerfile o remove da cópia pública do contêiner.

## 4. Banco de dados legado

O arquivo `script do banco valoriza.txt` descreve tabelas do banco antigo:

- `usuarios`: cadastro de funcionários, e-mail, CPF e hash da senha.
- `competencias`: catálogo de competências e indicador de atividade.
- `eleicoes_mensais`: registros de avaliação, avaliador, avaliado, competência, mês de referência e data do voto.

O script usa sintaxe MySQL/MariaDB, incluindo `AUTO_INCREMENT` e `USE`. Ele não cria o banco PostgreSQL usado pelo MeuDia+.

## 5. Atenção à implantação

O Dockerfile copia a pasta inteira `projetointegrador/` para `/var/www/html/`. Ele remove alguns arquivos de apoio, como scripts SQL/textuais e ZIP, mas não exclui os scripts PHP legados.

Consequentemente, os arquivos PHP antigos continuam dentro da raiz pública do Apache e podem ser chamados por URL, ainda que não façam parte do menu principal do MeuDia+. Além disso, os scripts de avaliação e relatórios tentam acessar um banco MySQL local e possuem configurações de conexão diretamente no código.

**Antes de disponibilizar a aplicação publicamente, esses scripts precisam ser isolados, desativados ou migrados e revisados.** A documentação não os removeu nem alterou seu comportamento.

## 6. Organização recomendada

Para uma próxima etapa de manutenção, escolha uma das opções:

1. Mover o legado para um repositório separado, sem implantação junto ao MeuDia+.
2. Guardar os arquivos históricos fora do diretório público do Apache.
3. Se o VALORIZA+ ainda for necessário, tratá-lo como aplicação independente, com configuração por ambiente, autenticação própria revisada, banco definido e testes.

Não basta renomear os arquivos: a regra deve impedir que scripts PHP antigos sejam executados pelo servidor público.
