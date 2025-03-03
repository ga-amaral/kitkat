<?php
session_start();
require_once 'config.php';

// Configurar cabeçalhos para JSON e CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://gatilzaidan.com.br');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Log para debug
error_log("Iniciando exclusão de gato");

// Responder à requisição OPTIONS do CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
}

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    error_log("Acesso não autorizado: usuário não está logado");
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

// Obter dados do corpo da requisição
$data = json_decode(file_get_contents('php://input'), true);
error_log("Dados recebidos: " . json_encode($data));

// Verificar se o ID foi fornecido
if (!isset($data['id']) || empty($data['id'])) {
    error_log("ID do gato não fornecido");
    echo json_encode(['success' => false, 'message' => 'ID do gato não fornecido']);
    exit;
}

$id = intval($data['id']);

try {
    // Iniciar transação
    $conn->begin_transaction();
    
    // Excluir tags de saúde
    $stmt = $conn->prepare("DELETE FROM gatos_tags_saude WHERE gato_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Excluir tags de personalidade
    $stmt = $conn->prepare("DELETE FROM gatos_tags_personalidade WHERE gato_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Excluir o gato
    $stmt = $conn->prepare("DELETE FROM gatos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        // Rollback se o gato não foi encontrado
        $conn->rollback();
        error_log("Gato não encontrado com ID: $id");
        echo json_encode(['success' => false, 'message' => 'Gato não encontrado']);
        exit;
    }
    
    // Commit da transação
    $conn->commit();
    
    error_log("Gato excluído com sucesso: $id");
    echo json_encode(['success' => true, 'message' => 'Gato excluído com sucesso']);
    
} catch (Exception $e) {
    // Rollback em caso de erro
    $conn->rollback();
    error_log("Erro ao excluir gato: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir gato: ' . $e->getMessage()]);
}
?>