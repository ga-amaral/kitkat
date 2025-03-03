<?php
session_start();
require_once 'config.php';

// Headers necessários
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://gatilzaidan.com.br');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Access-Control-Allow-Credentials: true');

// Log para debug
error_log("Iniciando exclusão de padreador");

// Responde à requisição OPTIONS do CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
}

// Verifica se o usuário está logado e é admin
if (!isset($_SESSION['user_id'])) {
    error_log("Usuário não está logado");
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Usuário não está logado'
    ]);
    exit;
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    error_log("Usuário não é administrador");
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Acesso permitido apenas para administradores'
    ]);
    exit;
}

// Recebe e decodifica os dados JSON
$json = file_get_contents('php://input');
$dados = json_decode($json, true);

error_log("Dados recebidos: " . print_r($dados, true));

// Verifica se o ID foi enviado
if (!isset($dados['id'])) {
    error_log("ID do padreador não fornecido");
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID do padreador não fornecido'
    ]);
    exit;
}

try {
    // Verifica se o padreador existe
    $stmt = $conn->prepare("SELECT id FROM padreadores WHERE id = ?");
    $stmt->execute([$dados['id']]);
    
    if ($stmt->rowCount() === 0) {
        error_log("Padreador não encontrado: " . $dados['id']);
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Padreador não encontrado'
        ]);
        exit;
    }
    
    // Verifica se existem gatos associados a este padreador
    $stmt = $conn->prepare("SELECT id FROM gatos WHERE padreador_id = ?");
    $stmt->execute([$dados['id']]);
    
    if ($stmt->rowCount() > 0) {
        error_log("Padreador possui gatos associados: " . $dados['id']);
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Não é possível excluir este padreador pois existem gatos associados a ele'
        ]);
        exit;
    }
    
    // Exclui os prêmios associados ao padreador
    $stmt = $conn->prepare("DELETE FROM padreadores_premios WHERE padreador_id = ?");
    $stmt->execute([$dados['id']]);
    
    // Exclui o padreador
    $stmt = $conn->prepare("DELETE FROM padreadores WHERE id = ?");
    $stmt->execute([$dados['id']]);
    
    error_log("Padreador excluído com sucesso. ID: " . $dados['id']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Padreador excluído com sucesso'
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao excluir padreador: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao excluir padreador: ' . $e->getMessage()
    ]);
}
?> 