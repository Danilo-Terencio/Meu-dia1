<?php
// MeuDia+ | Alteração de senha via sessão autenticada
require "sessao.php";
header("Content-Type: application/json; charset=utf-8");

function responder(int $codigo, bool $ok, string $mensagem = ''): void {
    http_response_code($codigo);
    echo json_encode(["ok" => $ok, "mensagem" => $mensagem], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['usuario_id'])) responder(401, false, "Sessão expirada.");
if ($_SERVER['REQUEST_METHOD'] !== 'POST') responder(405, false, "Método inválido.");
if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== 0) responder(415, false, "Requisição inválida.");

$entrada = json_decode(file_get_contents("php://input"), true);
if (!is_array($entrada) || json_last_error() !== JSON_ERROR_NONE) responder(400, false, "Requisição inválida.");

$atual = (string) ($entrada['atual'] ?? '');
$nova = (string) ($entrada['nova'] ?? '');
if ($atual === '' || $nova === '') responder(400, false, "Preencha todos os campos.");
if (strlen($nova) < 6) responder(400, false, "A nova senha precisa ter pelo menos 6 caracteres.");
if (strlen($nova) > 72) responder(400, false, "A nova senha pode ter no máximo 72 caracteres.");

require "conexao.php";
$usuarioId = (int) $_SESSION['usuario_id'];

try {
    $stmt = $conexao->prepare("SELECT senha FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $usuarioId]);
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($atual, $usuario['senha'])) {
        responder(400, false, "A senha atual está incorreta.");
    }

    $novoHash = password_hash($nova, PASSWORD_DEFAULT);
    $stmt = $conexao->prepare("UPDATE usuarios SET senha = :senha WHERE id = :id");
    $stmt->execute([':senha' => $novoHash, ':id' => $usuarioId]);
    $conexao = null;
} catch (PDOException $e) {
    error_log("MeuDia+ | Erro ao alterar senha: " . $e->getMessage());
    responder(500, false, "Não foi possível alterar a senha agora.");
}

session_regenerate_id(true);
responder(200, true, "Senha alterada.");
