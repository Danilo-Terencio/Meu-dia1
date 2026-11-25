<?php
// MeuDia+ | Login PostgreSQL, com limite de tentativas por e-mail e IP
require "sessao.php";
require "conexao.php";

if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

const MAX_TENTATIVAS_EMAIL = 5;
const MAX_TENTATIVAS_IP = 20;

function contarTentativas(PDO $c, string $campo, string $valor): int {
    if (!in_array($campo, ['email', 'ip'], true)) {
        return 0;
    }
    try {
        $stmt = $c->prepare(
            "SELECT COUNT(*) FROM login_tentativas
             WHERE {$campo} = :valor AND criado_em > (NOW() - INTERVAL '10 minutes')"
        );
        $stmt->execute([':valor' => $valor]);
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("MeuDia+ | Falha ao contar tentativas de login: " . $e->getMessage());
        return 0;
    }
}

function registrarFalha(PDO $c, string $email, string $ip): void {
    try {
        $c->exec("DELETE FROM login_tentativas WHERE criado_em < (NOW() - INTERVAL '1 day')");
        $stmt = $c->prepare("INSERT INTO login_tentativas (email, ip) VALUES (:email, :ip)");
        $stmt->execute([':email' => $email, ':ip' => $ip]);
    } catch (PDOException $e) {
        error_log("MeuDia+ | Falha ao registrar tentativa de login: " . $e->getMessage());
    }
}

function limparFalhas(PDO $c, string $email): void {
    try {
        $stmt = $c->prepare("DELETE FROM login_tentativas WHERE email = :email");
        $stmt->execute([':email' => $email]);
    } catch (PDOException $e) {
        error_log("MeuDia+ | Falha ao limpar tentativas de login: " . $e->getMessage());
    }
}

$erro = null;
$sucesso = null;
$emailForm = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailForm = mb_strtolower(trim($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    if ($emailForm === '' || $senha === '') {
        $erro = 'campos';
    } elseif (
        contarTentativas($conexao, 'email', $emailForm) >= MAX_TENTATIVAS_EMAIL ||
        contarTentativas($conexao, 'ip', $ip) >= MAX_TENTATIVAS_IP
    ) {
        $erro = 'bloqueado';
    } else {
        try {
            $stmt = $conexao->prepare("SELECT id, nome, senha FROM usuarios WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $emailForm]);
            $usuario = $stmt->fetch();

            if (!$usuario || !password_verify($senha, $usuario['senha'])) {
                registrarFalha($conexao, $emailForm, $ip);
                $erro = 'invalido';
            } else {
                limparFalhas($conexao, $emailForm);
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = (int) $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $conexao = null;
                header("Location: index.php");
                exit;
            }
        } catch (PDOException $e) {
            error_log("MeuDia+ | Erro no login: " . $e->getMessage());
            $erro = 'erro_servidor';
        }
    }
} elseif (($_GET['cadastro'] ?? '') === 'sucesso') {
    $sucesso = "Account created successfully! Log in to continue.";
}

$mensagensErro = [
    "campos" => "Please enter your email and password.",
    "invalido" => "Incorrect email or password.",
    "bloqueado" => "Too many unsuccessful attempts. Wait 10 minutes and try again.",
    "erro_servidor" => "Unable to log in right now. Please try again."
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MeuDia+ | Log in</title>
  <script>try{if(sessionStorage.getItem("md_transicao"))document.documentElement.classList.add("md-chegando")}catch(e){}</script>
  <link rel="stylesheet" href="transicao.css">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="auth-page.css">
</head>
<body class="auth-page">
<div class="auth-wrap">

  <aside class="auth-left">
    <span class="blob b1"></span><span class="blob b2"></span><span class="blob b3"></span>
    <div class="auth-left-inner">
      <a class="logo-card" href="inicial.html" data-transicao><img src="logo.png" alt="MeuDia+"></a>
      <h1>Welcome back!</h1>
      <p class="auth-lead">Log in to pick up where you left off. Your tasks, calendar, and finances are waiting for you.</p>
      <ul class="benefits">
        <li><span class="ico"><svg class="ic " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="m8 12 3 3 5-6"/></svg></span><div><b>Tasks and priorities</b><small>Know what to do first</small></div></li>
        <li><span class="ico"><svg class="ic " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4.5" width="18" height="16.5" rx="3"/><path d="M3 10h18M8 2.5v4M16 2.5v4"/></svg></span><div><b>Calendar and events</b><small>Never forget an important date</small></div></li>
        <li><span class="ico"><svg class="ic " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M3 7l12-3v3M17 14h2"/></svg></span><div><b>Financial management</b><small>Income, expenses, and balance at a glance</small></div></li>
        <li><span class="ico"><svg class="ic " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5a2 2 0 0 1 2-2h13v16H6a2 2 0 0 0-2 2zM4 19V5"/></svg></span><div><b>Shopping, notes, and studies</b><small>Everything organized in one place</small></div></li>
      </ul>
      <div class="seguro"><svg class="ic " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/></svg> Encrypted passwords and protection against repeated login attempts.</div>
    </div>
  </aside>

  <main class="auth-right">
    <a class="voltar" href="inicial.html" data-transicao><svg class="ic " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5m6-6-6 6 6 6"/></svg> Back to home</a>
    <div class="auth-box">
      <div class="auth-head anim" style="--i:0"><h2>Log in to your account</h2><p>Enter your email and password to access your dashboard.</p></div>

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
              <input class="field-input" type="email" id="email" name="email" placeholder="Enter your email" value="<?= htmlspecialchars($emailForm) ?>" autocomplete="email" required>
            </div>
          </div>
          <div class="field anim" style="--i:2">
            <label class="field-label" for="senha">Password</label>
            <div class="input-wrap">
              <svg class="ic lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
              <input class="field-input" type="password" id="senha" name="senha" placeholder="Enter your password" autocomplete="current-password" required>
              <button type="button" class="password-toggle" onclick="togglePwd(this)" aria-label="Show password"><svg class="icon-eye" viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg><svg class="icon-eye-off" viewBox="0 0 24 24"><path d="M17.94 17.94A10.94 10.94 0 0112 19c-7 0-11-7-11-7a21.8 21.8 0 015.06-6.06M9.9 4.24A10.94 10.94 0 0112 4c7 0 11 7 11 7a21.8 21.8 0 01-3.22 4.31M14.12 14.12a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg></button>
            </div>
          </div>
        <button class="auth-btn anim" style="--i:3" type="submit">LOG IN</button>
      </form>

      <div class="auth-switch anim" style="--i:4">Don't have an account? <a href="cadastro.php" data-transicao>Sign up</a></div>
    </div>
  </main>

</div>

  <script src="transicao.js"></script>
  <script src="auth.js"></script>
</body>
</html>
