<?php
// MeuDia+ | Alteração de senha via sessão autenticada
require "sessao.php";
header("Content-Type: application/json; charset=utf-8");

function responder(int $codigo, bool $ok, string $mensagem = ''): void {
    http_response_code($codigo);
    echo json_encode(["ok" => $ok, "mensagem" => $mensagem], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['usuario_id'])) responder(401, false, "Session expired.");
if ($_SERVER['REQUEST_METHOD'] !== 'POST') responder(405, false, "Invalid method.");
if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== 0) responder(415, false, "Invalid request.");

$entrada = json_decode(file_get_contents("php://input"), true);
if (!is_array($entrada) || json_last_error() !== JSON_ERROR_NONE) responder(400, false, "Invalid request.");

$atual = (string) ($entrada['atual'] ?? '');
$nova = (string) ($entrada['nova'] ?? '');
if ($atual === '' || $nova === '') responder(400, false, "Please fill in all fields.");
if (strlen($nova) < 6) responder(400, false, "Your new password must be at least 6 characters long.");
if (strlen($nova) > 72) responder(400, false, "Your new password can be up to 72 characters long.");

require "conexao.php";
$usuarioId = (int) $_SESSION['usuario_id'];

try {
    $stmt = $conexao->prepare("SELECT senha FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $usuarioId]);
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($atual, $usuario['senha'])) {
        responder(400, false, "The current password is incorrect.");
    }

    $novoHash = password_hash($nova, PASSWORD_DEFAULT);
    $stmt = $conexao->prepare("UPDATE usuarios SET senha = :senha WHERE id = :id");
    $stmt->execute([':senha' => $novoHash, ':id' => $usuarioId]);
    $conexao = null;
} catch (PDOException $e) {
    error_log("MeuDia+ | Erro ao alterar senha: " . $e->getMessage());
    responder(500, false, "Unable to change the password right now.");
}

session_regenerate_id(true);
responder(200, true, "Password changed.");
