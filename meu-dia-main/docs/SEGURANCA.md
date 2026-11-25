# Segurança, privacidade e limitações conhecidas

Este documento separa os mecanismos que já aparecem no código das melhorias que ainda precisam ser avaliadas antes de considerar a aplicação pronta para produção.

## 1. Controles implementados no MeuDia+

### Autenticação

- O cadastro valida nome, e-mail, confirmação de senha e comprimento da senha.
- O e-mail é normalizado para letras minúsculas antes de ser consultado ou gravado.
- O banco possui restrição de unicidade para o e-mail.
- As senhas são transformadas em hash com `password_hash`; o código não precisa armazenar a senha original.
- O login verifica o hash com `password_verify`.
- Após autenticar, o código regenera o ID da sessão com `session_regenerate_id(true)`.

### Sessões

O arquivo `sessao.php` configura o cookie da sessão com:

- `HttpOnly`: impede leitura do cookie pelo JavaScript do navegador.
- `SameSite=Lax`: reduz o envio do cookie em determinados contextos de navegação entre sites.
- `Secure`: ativado quando a requisição é reconhecida como HTTPS.
- Duração de sessão até o encerramento do navegador, pois o tempo de vida do cookie é zero.

As páginas protegidas verificam `$_SESSION['usuario_id']` antes de exibir ou alterar dados.

### Consultas ao banco

As operações de cadastro, login, leitura e gravação usam consultas preparadas com parâmetros PDO. A API identifica o usuário pelo ID da sessão, em vez de aceitar o ID do proprietário enviado pelo navegador.

### Limitação de tentativas

O login limita falhas em uma janela de dez minutos:

- Cinco tentativas por e-mail.
- Vinte tentativas por endereço IP.

O sistema registra falhas na tabela `login_tentativas`, remove registros antigos durante a rotina de registro e limpa as falhas daquele e-mail após autenticação bem-sucedida.

### API de persistência

O endpoint `api.php`:

- Exige uma sessão autenticada.
- Aceita apenas POST.
- Exige `Content-Type: application/json`.
- Rejeita JSON inválido.
- Aceita somente chaves explicitamente permitidas.
- Limita o JSON de cada valor a 1.000.000 de bytes.
- Usa o ID da sessão para determinar a conta que receberá os dados.

### Conexão e mensagens de erro

A conexão PostgreSQL exige SSL. Mensagens técnicas são direcionadas a `error_log` em diversos fluxos, enquanto a resposta ao usuário apresenta mensagens mais genéricas.

## 2. Dados e isolamento entre contas

A tabela `dados_usuario` relaciona cada registro ao usuário proprietário. O código de leitura filtra por `usuario_id` e a API utiliza o mesmo ID a partir da sessão.

A chave primária composta `(usuario_id, chave)` permite apenas um registro por coleção/preferência para cada conta. A chave estrangeira com `ON DELETE CASCADE` remove os dados vinculados se uma conta for excluída do banco.

A aplicação armazena coleções e preferências como JSON serializado em texto. Isso facilita salvar o estado do painel, mas exige que o formato seja mantido compatível com o JavaScript que lê esses valores.

## 3. Limitações identificadas no código analisado

Os pontos abaixo não significam que a aplicação esteja necessariamente vulnerável em todos os cenários; indicam controles que não foram identificados ou comportamentos que merecem revisão:

1. **O esquema PostgreSQL precisa ser instalado corretamente.** O arquivo `database.sql` versionado é MySQL, mas a aplicação principal usa PostgreSQL. Se a tabela `login_tentativas` não existir ou não puder ser consultada, a função de contagem registra o erro e retorna zero. Nesse cenário, o bloqueio por quantidade de tentativas pode deixar de funcionar.
2. **Não foi identificado token CSRF explícito** nos formulários e endpoints principais. `SameSite=Lax` é uma proteção complementar, não substitui uma estratégia CSRF completa.
3. **O comprimento mínimo atual de senha é seis caracteres.** Avalie uma política mais forte e mensagens de orientação adequadas.
4. **Não foi identificado fluxo de recuperação de senha por e-mail, autenticação multifator ou verificação de e-mail.**
5. **O logout é acionado por GET.** Uma ação que altera estado de autenticação é preferencialmente tratada por POST com proteção apropriada.
6. **O controle por IP usa `REMOTE_ADDR`.** Em ambientes com proxy reverso, confirme como o endereço do cliente é obtido. Não confie cegamente em cabeçalhos encaminhados pelo cliente.
7. **Não foram encontrados testes automatizados** para autenticação, autorização, persistência ou isolamento entre contas.
8. **O módulo legado VALORIZA+ contém configurações de conexão MySQL diretamente nos scripts.** Ele deve ser isolado ou desativado antes de disponibilizar publicamente o contêiner.

## 4. Recomendações antes de produção

- Instalar e verificar o esquema PostgreSQL descrito em [BANCO-DE-DADOS.md](BANCO-DE-DADOS.md).
- Remover do diretório público ou isolar os arquivos legados VALORIZA+.
- Manter segredos somente nas configurações privadas do serviço.
- Adotar proteção CSRF explícita para operações autenticadas.
- Rever o limite e a política de senha.
- Considerar limitação de tentativas que falhe de forma segura quando o armazenamento de tentativas estiver indisponível.
- Revisar logs para não registrar senhas, tokens ou URLs de banco.
- Definir política de backup, retenção e exclusão de dados de usuários.
- Adicionar testes de integração para cadastro, login, logout, API e isolamento de dados.
- Validar HTTPS e o atributo `Secure` do cookie no ambiente real.

## 5. Gestão de segredos

Nunca versionar:

- Senhas do PostgreSQL.
- URLs de conexão com credenciais.
- Chaves privadas ou tokens de serviços.
- Arquivos locais contendo credenciais reais.

Use Secrets ou variáveis privadas da plataforma de hospedagem. Caso uma credencial real tenha sido publicada, substitua-a no provedor e atualize o ambiente da aplicação; apagar o valor do arquivo não invalida uma credencial já exposta.
