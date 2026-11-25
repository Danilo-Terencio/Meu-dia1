<?php
// MeuDia+ | API dos dados de cada conta (PostgreSQL)
require "sessao.php";
header("Content-Type: application/json; charset=utf-8");

function responderApi(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['usuario_id'])) {
    responderApi(401, ["ok" => false, "erro" => "nao_logado"]);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderApi(405, ["ok" => false, "erro" => "metodo"]);
}
if (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== 0) {
    responderApi(415, ["ok" => false, "erro" => "tipo_invalido"]);
}

$entrada = json_decode(file_get_contents("php://input"), true);
if (!is_array($entrada) || json_last_error() !== JSON_ERROR_NONE) {
    responderApi(400, ["ok" => false, "erro" => "json_invalido"]);
}

$chave = $entrada['chave'] ?? '';
$valor = $entrada['valor'] ?? null;
$permitidas = [
    "tarefas", "agenda", "notas", "gastos", "compras", "estudos", "trabalhos",
    "renda", "nome", "cidade", "profissao", "objetivo", "sobre", "tema"
];
if (!is_string($chave) || !in_array($chave, $permitidas, true)) {
    responderApi(400, ["ok" => false, "erro" => "chave_invalida"]);
}
$json = json_encode($valor, JSON_UNESCAPED_UNICODE);
if ($json === false || strlen($json) > 1000000) {
    responderApi(400, ["ok" => false, "erro" => "valor_invalido"]);
}

require "conexao.php";
try {
    $stmt = $conexao->prepare(
        "INSERT INTO dados_usuario (usuario_id, chave, valor)
         VALUES (:usuario_id, :chave, :valor)
         ON CONFLICT (usuario_id, chave)
         DO UPDATE SET valor = EXCLUDED.valor, atualizado_em = NOW()"
    );
    $stmt->execute([
        ':usuario_id' => (int) $_SESSION['usuario_id'],
        ':chave' => $chave,
        ':valor' => $json
    ]);
    responderApi(200, ["ok" => true]);
} catch (PDOException $e) {
    error_log("MeuDia+ | Erro ao salvar dados: " . $e->getMessage());
    responderApi(500, ["ok" => false, "erro" => "falha_ao_salvar"]);
}
?>