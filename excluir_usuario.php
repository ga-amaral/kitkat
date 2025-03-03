<?php
session_start();
require_once 'config.php';

// Headers necessários
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://gatilzaidan.com.br');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Access-Control-Allow-Credentials: true');

// Log para debug
error_log("Iniciando excluir_usuario.php");

// Responde à requisição OPTIONS do CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Verifica se o usuário está logado e é admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    error_log("Usuário não autorizado: user_id=" . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'não definido') . ", user_type=" . (isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'não definido'));
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Usuário não autorizado'
    ]);
    exit;
}

// Recebe os dados do POST
$input = file_get_contents('php://input');
error_log("Dados brutos recebidos: " . $input);

$dados = json_decode($input, true);
error_log("Dados decodificados: " . print_r($dados, true));

// Validação dos dados
if (!isset($dados['id']) || empty($dados['id'])) {
    error_log("ID do usuário não fornecido");
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID do usuário não fornecido'
    ]);
    exit;
}

try {
    // Verifica se o usuário existe e não é o adminamaral
    $stmt = $conn->prepare("SELECT user FROM user WHERE id = ?");
    $stmt->execute([$dados['id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        error_log("Usuário não encontrado: ID " . $dados['id']);
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Usuário não encontrado'
        ]);
        exit;
    }

    // Impede a exclusão do usuário adminamaral
    if ($usuario['user'] === 'adminamaral') {
        error_log("Tentativa de excluir usuário adminamaral");
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Não é permitido excluir o usuário administrador principal'
        ]);
        exit;
    }

    // Exclui o usuário
    $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
    $result = $stmt->execute([$dados['id']]);

    if ($result) {
        error_log("Usuário excluído com sucesso: ID " . $dados['id']);
        echo json_encode([
            'success' => true,
            'message' => 'Usuário excluído com sucesso'
        ]);
    } else {
        throw new Exception('Erro ao excluir usuário do banco de dados');
    }
} catch (Exception $e) {
    error_log("Erro ao excluir usuário: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao excluir usuário: ' . $e->getMessage()
    ]);
}
?> 