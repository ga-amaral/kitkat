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
error_log("Iniciando busca de padreador");

// Responde à requisição OPTIONS do CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
}

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    error_log("Usuário não está logado");
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Usuário não está logado'
    ]);
    exit;
}

// Verifica se o ID foi fornecido
if (!isset($_GET['id'])) {
    error_log("ID do padreador não fornecido");
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID do padreador não fornecido'
    ]);
    exit;
}

$id = $_GET['id'];
error_log("Buscando padreador com ID: " . $id);

try {
    // Busca o padreador pelo ID
    $stmt = $conn->prepare("SELECT * FROM padreadores WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() === 0) {
        error_log("Padreador não encontrado: " . $id);
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Padreador não encontrado'
        ]);
        exit;
    }
    
    $padreador = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Busca os prêmios do padreador
    $stmt = $conn->prepare("SELECT * FROM padreadores_premios WHERE padreador_id = ?");
    $stmt->execute([$id]);
    $premios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $padreador['premios'] = $premios;
    
    error_log("Padreador encontrado com sucesso. ID: " . $id);
    
    echo json_encode([
        'success' => true,
        'padreador' => $padreador
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar padreador: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar padreador: ' . $e->getMessage()
    ]);
}
?> 