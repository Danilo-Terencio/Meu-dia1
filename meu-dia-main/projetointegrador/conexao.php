<?php
// MeuDia+ | Conexão PostgreSQL (Supabase)
// Configure estas variáveis como Secrets no Render. Nunca coloque a senha no GitHub.
$host = trim((string) (getenv("DB_HOST") ?: ""));
$port = trim((string) (getenv("DB_PORT") ?: "5432"));
$dbname = trim((string) (getenv("DB_NAME") ?: "postgres"));
$user = trim((string) (getenv("DB_USER") ?: ""));
$password = getenv("DB_PASSWORD");

$databaseUrl = getenv("DATABASE_URL");
if ($databaseUrl) {
    $parts = parse_url($databaseUrl);
    if ($parts === false || empty($parts["host"]) || empty($parts["user"]) || !array_key_exists("pass", $parts)) {
        error_log("MeuDia+ | DATABASE_URL inválida.");
        http_response_code(500);
        die("A conexão com o banco de dados não está configurada corretamente.");
    }
    $host = $parts["host"];
    $port = (string) ($parts["port"] ?? 5432);
    $dbname = ltrim((string) ($parts["path"] ?? "/postgres"), "/");
    $user = rawurldecode($parts["user"]);
    $password = rawurldecode($parts["pass"]);
}

if ($host === "" || $user === "" || $password === false || $password === "") {
    error_log("MeuDia+ | Faltam variáveis de conexão PostgreSQL no ambiente.");
    http_response_code(500);
    die("O banco de dados ainda não está configurado no servidor. Tente novamente mais tarde.");
}

$dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require;connect_timeout=10";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $conexao = new PDO($dsn, $user, $password, $options);
} catch (PDOException $e) {
    error_log("MeuDia+ | Falha na conexão PostgreSQL: " . $e->getMessage());
    http_response_code(500);
    die("Não foi possível acessar o sistema agora. Verifique a configuração do banco de dados.");
}
?>