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
error_log("Iniciando exclusão de matriz");

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
    error_log("ID da matriz não informado");
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID da matriz não informado'
    ]);
    exit;
}

try {
    // Verifica se a matriz existe
    $stmt = $conn->prepare("SELECT id FROM matrizes WHERE id = ?");
    $stmt->execute([$dados['id']]);
    
    if ($stmt->rowCount() === 0) {
        error_log("Matriz não encontrada: " . $dados['id']);
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Matriz não encontrada'
        ]);
        exit;
    }
    
    // Verifica se existem gatos associados a esta matriz
    $stmt = $conn->prepare("SELECT COUNT(*) FROM gatos WHERE matriz_id = ?");
    $stmt->execute([$dados['id']]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        error_log("Existem gatos associados a esta matriz: " . $count);
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Não é possível excluir esta matriz pois existem ' . $count . ' gatos associados a ela'
        ]);
        exit;
    }
    
    // Exclui a matriz
    $stmt = $conn->prepare("DELETE FROM matrizes WHERE id = ?");
    $stmt->execute([$dados['id']]);
    
    error_log("Matriz excluída com sucesso. ID: " . $dados['id']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Matriz excluída com sucesso'
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao excluir matriz: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao excluir matriz: ' . $e->getMessage()
    ]);
}
?> 