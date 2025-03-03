<?php
// Desabilitar exibição de erros
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Configurações do banco de dados
define('DB_HOST', 'localhost');           // Endereço do servidor MySQL
define('DB_USER', 'gatilbernardo');       // Usuário do MySQL
define('DB_PASS', 'V6$Z2Asn95');         // Senha do MySQL
define('DB_NAME', 'gatilzaidan');         // Nome do banco de dados

// Função para testar conexão sem banco de dados
function testarConexaoServidor() {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return true;
    } catch(PDOException $e) {
        error_log("Erro na conexão com servidor: " . $e->getMessage());
        throw new Exception("Erro ao conectar ao servidor MySQL: " . $e->getMessage());
    }
}

// Função para verificar se o banco existe
function verificarBancoDados() {
    try {
        $conn = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
        return $stmt->rowCount() > 0;
    } catch(PDOException $e) {
        error_log("Erro ao verificar banco: " . $e->getMessage());
        throw new Exception("Erro ao verificar banco de dados: " . $e->getMessage());
    }
}

// Criar conexão
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Configura o charset
    $conn->exec("SET CHARACTER SET utf8");
    $conn->exec("SET collation_connection = utf8_general_ci");

} catch(PDOException $e) {
    error_log("Erro na conexão com o banco: " . $e->getMessage());
    die(json_encode([
        'success' => false,
        'message' => 'Erro na conexão com o banco de dados'
    ]));
}

// Função para testar a conexão
function testarConexao() {
    global $conn;
    try {
        $conn->query("SELECT 1");
        return true;
    } catch(PDOException $e) {
        error_log("Erro ao testar conexão: " . $e->getMessage());
        throw new Exception("Erro ao testar conexão: " . $e->getMessage());
    }
}

// Testa a conexão
if (!testarConexao()) {
    die(json_encode([
        'success' => false,
        'message' => 'A conexão foi estabelecida mas não está respondendo'
    ]));
}
?>