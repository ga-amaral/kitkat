<?php
session_start();
require_once 'config.php';

// Headers necessários
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://gatilzaidan.com.br');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Access-Control-Allow-Credentials: true');

// Log para debug
error_log("Iniciando busca de matriz por ID");

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

// Verifica se o ID foi enviado
if (!isset($_GET['id'])) {
    error_log("ID da matriz não informado");
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID da matriz não informado'
    ]);
    exit;
}

$id = $_GET['id'];
error_log("Buscando matriz com ID: " . $id);

try {
    // Busca a matriz pelo ID
    $stmt = $conn->prepare("
        SELECT * FROM matrizes WHERE id = ?
    ");
    $stmt->execute([$id]);
    $matriz = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$matriz) {
        error_log("Matriz não encontrada: " . $id);
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Matriz não encontrada'
        ]);
        exit;
    }
    
    error_log("Matriz encontrada: " . print_r($matriz, true));
    
    echo json_encode([
        'success' => true,
        'matriz' => $matriz
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar matriz: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar matriz: ' . $e->getMessage()
    ]);
}
?> 