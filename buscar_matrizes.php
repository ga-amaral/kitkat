<?php
session_start();
require_once 'config.php';

// Headers necessários
header('Content-Type: application/json; charset=utf-8');
// Permitir acesso tanto do domínio de produção quanto de localhost para desenvolvimento
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');

// Log para debug
error_log("Iniciando busca de todas as matrizes");

// Responde à requisição OPTIONS do CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
}

// Verifica se o usuário está logado - comentado temporariamente para debug
/*
if (!isset($_SESSION['user_id'])) {
    error_log("Usuário não está logado");
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Usuário não está logado'
    ]);
    exit;
}
*/

try {
    // Busca todas as matrizes
    $stmt = $conn->prepare("SELECT * FROM matrizes ORDER BY nome");
    $stmt->execute();
    $matrizes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Verifica se a tabela matrizes_premios existe
    $stmt = $conn->prepare("SHOW TABLES LIKE 'matrizes_premios'");
    $stmt->execute();
    $tabelaExiste = $stmt->rowCount() > 0;
    
    if ($tabelaExiste) {
        // Para cada matriz, busca seus prêmios
        foreach ($matrizes as &$matriz) {
            $stmt = $conn->prepare("SELECT * FROM matrizes_premios WHERE matriz_id = ?");
            $stmt->execute([$matriz['id']]);
            $matriz['premios'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } else {
        // Se a tabela não existe, adiciona um array vazio para premios
        foreach ($matrizes as &$matriz) {
            $matriz['premios'] = [];
        }
    }
    
    error_log("Matrizes encontradas: " . count($matrizes));
    
    echo json_encode([
        'success' => true,
        'matrizes' => $matrizes
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar matrizes: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar matrizes: ' . $e->getMessage()
    ]);
}
?> 