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
    // Busca todas as matrizes com contagem de filhotes
    $stmt = $conn->prepare("
        SELECT m.*,
               (SELECT COUNT(*) FROM gatos WHERE matriz_id = m.id) as total_filhotes
        FROM matrizes m
        ORDER BY m.nome
    ");
    $stmt->execute();
    $matrizes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Busca os prêmios de cada matriz
    foreach ($matrizes as &$matriz) {
        $stmt = $conn->prepare("
            SELECT premio FROM matrizes_premios 
            WHERE matriz_id = ?
            ORDER BY id
        ");
        $stmt->execute([$matriz['id']]);
        $matriz['premios'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    echo json_encode([
        'success' => true,
        'matrizes' => $matrizes
    ]);
} catch (Exception $e) {
    error_log("Erro ao buscar matrizes: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar matrizes: ' . $e->getMessage()
    ]);
}
?> 