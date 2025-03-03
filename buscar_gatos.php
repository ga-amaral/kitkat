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
    // Busca todos os gatos com informações das matrizes e padreadores
    $stmt = $conn->prepare("
        SELECT g.*, m.nome as matriz_nome, p.nome as padreador_nome
        FROM gatos g
        LEFT JOIN matrizes m ON g.matriz_id = m.id
        LEFT JOIN padreadores p ON g.padreador_id = p.id
        ORDER BY g.nome
    ");
    $stmt->execute();
    $gatos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Para cada gato, busca suas tags de saúde e personalidade
    foreach ($gatos as &$gato) {
        // Busca tags de saúde
        $stmt = $conn->prepare("
            SELECT tag FROM gatos_tags_saude 
            WHERE gato_id = ?
        ");
        $stmt->execute([$gato['id']]);
        $gato['saude'] = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'tag');

        // Busca tags de personalidade
        $stmt = $conn->prepare("
            SELECT tag FROM gatos_tags_personalidade 
            WHERE gato_id = ?
        ");
        $stmt->execute([$gato['id']]);
        $gato['personalidade'] = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'tag');
    }

    echo json_encode([
        'success' => true,
        'gatos' => $gatos
    ]);
} catch (Exception $e) {
    error_log("Erro ao buscar gatos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar gatos: ' . $e->getMessage()
    ]);
}
?> 