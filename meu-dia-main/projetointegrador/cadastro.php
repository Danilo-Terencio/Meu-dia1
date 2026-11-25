<?php
// MeuDia+ | Cadastro usando PostgreSQL no Supabase
require "sessao.php";
require "conexao.php";

if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$erro = null;
$nomeForm = '';
$emailForm = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomeForm = trim($_POST['nome'] ?? '');
    $emailForm = mb_strtolower(trim($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';
    $confirmarPassword = $_POST['confirmar_senha'] ?? '';

    if ($nomeForm === '' || $emailForm === '' || $senha === '' || $confirmarPassword === '') {
        $erro = 'campos';
    } elseif (mb_strlen($nomeForm) < 2 || mb_strlen($nomeForm) > 100) {
        $erro = 'nome_invalido';
    } elseif (!filter_var($emailForm, FILTER_VALIDATE_EMAIL) || strlen($emailForm) > 150) {
        $erro = 'email_invalido';
    } elseif (strlen($senha) < 6) {
        $erro = 'senha_curta';
    } elseif (strlen($senha) > 72) {
        $erro = 'senha_longa';
    } elseif ($senha !== $confirmarPassword) {
        $erro = 'senha_diferente';
    } else {
        try {
            $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $emailForm]);
            $existe = $stmt->fetchColumn() !== false;

            if ($existe) {
                $erro = 'email_existe';
            } else {
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $conexao->prepare(
                    "INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)"
                );
                $stmt->execute([
                    ':nome' => $nomeForm,
                    ':email' => $emailForm,
                    ':senha' => $senhaHash
                ]);
                $conexao = null;
                header("Location: login.php?cadastro=sucesso");
                exit;
            }
        } catch (PDOException $e) {
            error_log("MeuDia+ | Erro no cadastro: " . $e->getMessage());
            $erro = ($e->getCode() === '23505') ? 'email_existe' : 'erro_servidor';
        }
    }
}

$mensagensErro = [
    "campos" => "Please fill in all fields.",
    "nome_invalido" => "Your name must be between 2 and 100 characters.",
    "email_invalido" => "Enter a valid email address.",
    "email_existe" => "This email is already registered.",
    "senha_curta" => "Your password must be at least 6 characters long.",
    "senha_longa" => "Your password can be up to 72 characters long.",
    "senha_diferente" => "The passwords do not match.",
    "erro_servidor" => "Unable to create your account right now. Please try again."
];

$olho = '<button type="button" class="password-toggle" onclick="togglePwd(this)" aria-label="Show password"><svg class="icon-eye" viewBox="0 0 24 24"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg><svg class="icon-eye-off" viewBox="0 0 24 24"><path d="M17.94 17.94A10.94 10.94 0 0112 19c-7 0-11-7-11-7a21.8 21.8 0 015.06-6.06M9.9 4.24A10.94 10.94 0 0112 4c7 0 11 7 11 7a21.8 21.8 0 01-3.22 4.31M14.12 14.12a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg></button>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MeuDia+ | Create account</title>
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
      <h1>Create your account and start organizing your day</h1>
      <p class="auth-lead">It takes less than a minute. Then log in and get your routine organized.</p>
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
      <div class="auth-head anim" style="--i:0"><h2>Create account</h2><p>Fill in the details below to get started.</p></div>

      <?php if ($erro && isset($mensagensErro[$erro])): ?>
        <div class="msg msg-erro"><svg class="ic " viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5m0 3h.01"/></svg><?= htmlspecialchars($mensagensErro[$erro]) ?></div>
      <?php endif; ?>

      <form class="auth-form" action="cadastro.php" method="POST">
          <div class="field anim" style="--i:1">
            <label class="field-label" for="nome">Name</label>
            <div class="input-wrap">
              <svg class="ic lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
              <input class="field-input" type="text" id="nome" name="nome" placeholder="Enter your name" value="<?= htmlspecialchars($nomeForm) ?>" maxlength="100" autocomplete="name" required>
            </div>
          </div>
          <div class="field anim" style="--i:2">
            <label class="field-label" for="email">Email</label>
            <div class="input-wrap">
              <svg class="ic lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
              <input class="field-input" type="email" id="email" name="email" placeholder="Enter your email" value="<?= htmlspecialchars($emailForm) ?>" maxlength="150" autocomplete="email" required>
            </div>
          </div>
          <div class="field anim" style="--i:3">
            <label class="field-label" for="senha">Password</label>
            <div class="input-wrap">
              <svg class="ic lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
              <input class="field-input" type="password" id="senha" name="senha" placeholder="Create a password (at least 6 characters)" minlength="6" maxlength="72" autocomplete="new-password" required>
              <?= $olho ?>
            </div>
            <div class="forca" id="forca" data-n="0"><i></i><i></i><i></i><i></i><span></span></div>
          </div>
          <div class="field anim" style="--i:4">
            <label class="field-label" for="confirmar_senha">Confirm password</label>
            <div class="input-wrap">
              <svg class="ic lead" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
              <input class="field-input" type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Re-enter your password" minlength="6" maxlength="72" autocomplete="new-password" required>
              <?= $olho ?>
            </div>
            <div class="dica" id="dica-conf"></div>
          </div>
        <button class="auth-btn anim" style="--i:5" type="submit">CREATE ACCOUNT</button>
      </form>

      <div class="auth-switch anim" style="--i:6">Already have an account? <a href="login.php" data-transicao>Log in</a></div>
    </div>
  </main>

</div>

  <script src="transicao.js"></script>
  <script src="auth.js"></script>
</body>
</html>
