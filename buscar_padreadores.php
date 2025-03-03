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
error_log("Iniciando busca de todos os padreadores");

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
    // Busca todos os padreadores
    $stmt = $conn->prepare("SELECT * FROM padreadores ORDER BY nome");
    $stmt->execute();
    $padreadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Verifica se a tabela padreadores_premios existe
    $stmt = $conn->prepare("SHOW TABLES LIKE 'padreadores_premios'");
    $stmt->execute();
    $tabelaExiste = $stmt->rowCount() > 0;
    
    if ($tabelaExiste) {
        // Para cada padreador, busca seus prêmios
        foreach ($padreadores as &$padreador) {
            $stmt = $conn->prepare("SELECT * FROM padreadores_premios WHERE padreador_id = ?");
            $stmt->execute([$padreador['id']]);
            $padreador['premios'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } else {
        // Se a tabela não existe, adiciona um array vazio para premios
        foreach ($padreadores as &$padreador) {
            $padreador['premios'] = [];
        }
    }
    
    error_log("Padreadores encontrados: " . count($padreadores));
    
    echo json_encode([
        'success' => true,
        'padreadores' => $padreadores
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar padreadores: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar padreadores: ' . $e->getMessage()
    ]);
}
?> 