<?php
// MeuDia+ | Inicia a sessão com cookies mais seguros (usado por todas as páginas PHP)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,      // o JavaScript não consegue ler o cookie
        'samesite' => 'Lax'      // protege contra envios vindos de outros sites
    ]);
    session_start();
}
