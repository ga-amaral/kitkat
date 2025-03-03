<?php
session_start();
require_once 'config.php';

// Headers necessários
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://gatilzaidan.com.br');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Access-Control-Allow-Credentials: true');

// Responde à requisição OPTIONS do CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Busca todos os padreadores com contagem de filhotes
    $stmt = $conn->prepare("
        SELECT p.*,
               (SELECT COUNT(*) FROM gatos WHERE padreador_id = p.id) as total_filhotes
        FROM padreadores p
        ORDER BY p.nome
    ");
    $stmt->execute();
    $padreadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'padreadores' => $padreadores
    ]);
} catch (Exception $e) {
    error_log("Erro ao buscar padreadores: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar padreadores: ' . $e->getMessage()
    ]);
}
?> 