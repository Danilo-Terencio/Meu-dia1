<?php
// ============================================================
// MeuDia+ | Login
// GET  -> mostra o formulário
// POST -> confere email/senha, limita tentativas erradas e abre a sessão
// ============================================================
require "sessao.php";
require "conexao.php";

// Quem já está logado vai direto para o painel
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

const MAX_TENTATIVAS_EMAIL = 5;   // erros por email em 10 minutos
const MAX_TENTATIVAS_IP    = 20;  // erros por IP em 10 minutos

function contarTentativas(mysqli $c, string $campo, string $valor): int {
    // $campo vem sempre de um texto fixo abaixo ('email' ou 'ip')
    $stmt = @$c->prepare(
        "SELECT COUNT(*) FROM login_tentativas
         WHERE $campo = ? AND criado_em > (NOW() - INTERVAL 10 MINUTE)"
    );
    if (!$stmt) return 0;
    $stmt->bind_param("s", $valor);
    $stmt->execute();
    $stmt->bind_result($n);
    $stmt->fetch();
    $stmt->close();
    return (int) $n;
}

function registrarFalha(mysqli $c, string $email, string $ip): void {
    @$c->query("DELETE FROM login_tentativas WHERE criado_em < (NOW() - INTERVAL 1 DAY)");
    $stmt = @$c->prepare("INSERT INTO login_tentativas (email, ip) VALUES (?, ?)");
    if (!$stmt) return;
    $stmt->bind_param("ss", $email, $ip);
    $stmt->execute();
    $stmt->close();
}

function limparFalhas(mysqli $c, string $email): void {
    $stmt = @$c->prepare("DELETE FROM login_tentativas WHERE email = ?");
    if (!$stmt) return;
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->close();
}

$erro      = null;
$sucesso   = null;
$emailForm = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $emailForm = mb_strtolower(trim($_POST['email'] ?? ''));
    $senha     = $_POST['senha'] ?? '';
    $ip        = $_SERVER['REMOTE_ADDR'] ?? '';

    if ($emailForm === '' || $senha === '') {
        $erro = 'campos';

    } elseif (
        contarTentativas($conexao, 'email', $emailForm) >= MAX_TENTATIVAS_EMAIL ||
        contarTentativas($conexao, 'ip', $ip) >= MAX_TENTATIVAS_IP
    ) {
        $erro = 'bloqueado';

    } else {
        $stmt = $conexao->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $emailForm);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$usuario || !password_verify($senha, $usuario['senha'])) {
            registrarFalha($conexao, $emailForm, $ip);
            $erro = 'invalido';

        } else {
            limparFalhas($conexao, $emailForm);

            // Gera um novo ID de sessão ao entrar (evita fixação de sessão)
            session_regenerate_id(true);
            $_SESSION['usuario_id']   = (int) $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];

            $conexao->close();
            header("Location: index.php");
            exit;
        }
    }

} elseif (($_GET['cadastro'] ?? '') === 'sucesso') {
    $sucesso = "Conta criada com sucesso! Faça login para continuar.";
}

$mensagensErro = [
    "campos"    => "Preencha email e senha.",
    "invalido"  => "Email ou senha incorretos.",
    "bloqueado" => "Muitas tentativas incorretas. Aguarde 10 minutos e tente novamente."
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MeuDia+ | Entrar</title>
  <script>try{if(sessionStorage.getItem("md_transicao"))document.documentElement.classList.add("md-chegando")}catch(e){}</script>
  <link rel="stylesheet" href="transicao.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-wrap">

  <aside class="auth-left">
    <span class="blob b1"></span><span class="blob b2"></span><span class="blob b3"></span>
    <div class="auth-left-inner">
      <a class="logo-card" href="inicial.html" data-transicao><img src="logo.png" alt="MeuDia+"></a>
      <h1>Que bom ter você de volta!</h1>
      <p class="auth-lead">Entre para continuar de onde parou: suas tarefas, agenda e finanças estão te esperando.</p>
      <ul class="benefits">
        <li><span class="ico"><svg class="ic " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="m8 12 3 3 5-6"/></svg></span><div><b>Tarefas e prioridades</b><small>Saiba o que fazer primeiro</small></div></li>
        <li><span class="ico"><svg class="ic " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4.5" width="18" height="16.5" rx="3"/><path d="M3 10h18M8 2.5v4M16 2.5v4"/></svg></span><div><b>Agenda e compromissos</b><small>Nenhuma data importante esquecida</small></div></li>
        <li><span class="ico"><svg class="ic " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M3 7l12-3v3M17 14h2"/></svg></span><div><b>Controle financeiro</b><small>Rendas, gastos e saldo à vista</small></div></li>
        <li><span class="ico"><svg class="ic " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2zM4 19V5"/></svg></span><div><b>Compras, notas e estudos</b><small>Tudo organizado em um só lugar</small></div></li>
      </ul>
      <div class="seguro"><svg class="ic " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/></svg> Senhas criptografadas e proteção contra tentativas em excesso.</div>
    </div>
  </aside>

  <main class="auth-right">
    <a class="voltar" href="inicial.html" data-transicao><svg class="ic " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m6-6-6 6 6 6"/></svg> Voltar ao início</a>
    <div class="auth-box">
      <div class="auth-head anim" style="--i:0"><h2>Acesse sua conta</h2><p>Use seu email e senha para entrar no painel.</p></div>

      <?php if ($sucesso): ?>
        <div class="msg msg-sucesso"><svg class="ic " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg><?= htmlspecialchars($sucesso) ?></div>
      <?php endif; ?>

      <?php if ($erro && isset($mensagensErro[$erro])): ?>
        <div class="msg msg-erro"><svg class="ic " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5m0 3h.01"/></svg><?= htmlspecialchars($mensagensErro[$erro]) ?></div>
      <?php endif; ?>

      <form class="auth-form" action="login.php" method="POST">
          <div class="field anim" style="--i:1">
            <label class="field-label" for="email">Email</label>
            <div class="input-wrap">
              <svg class="ic lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
              <input class="field-input" type="email" id="email" name="email" placeholder="Digite seu email" value="<?= htmlspecialchars($emailForm) ?>" autocomplete="email" required>
            </div>
          </div>
          <div class="field anim" style="--i:2">
            <label class="field-label" for="senha">Senha</label>
            <div class="input-wrap">
              <svg class="ic lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
              <input class="field-input" type="password" id="senha" name="senha" placeholder="Digite sua senha" autocomplete="current-password" required>
              <button type="button" class="password-toggle" onclick="togglePwd(this)" aria-label="Mostrar senha"><svg class="icon-eye" viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg><svg class="icon-eye-off" viewBox="0 0 24 24"><path d="M17.94 17.94A10.94 10.94 0 0112 19c-7 0-11-7-11-7a21.8 21.8 0 015.06-6.06M9.9 4.24A10.94 10.94 0 0112 4c7 0 11 7 11 7a21.8 21.8 0 01-3.22 4.31M14.12 14.12a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg></button>
            </div>
          </div>
        <button class="auth-btn anim" style="--i:3" type="submit">ENTRAR</button>
      </form>

      <div class="auth-switch anim" style="--i:4">Ainda não tem conta? <a href="cadastro.php" data-transicao>Cadastre-se</a></div>
    </div>
  </main>

</div>

  <script src="transicao.js"></script>
  <script src="auth.js"></script>
</body>
</html>
