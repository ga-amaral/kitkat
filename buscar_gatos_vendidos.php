<?php
// Log para depuração
file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . ' - Acessou buscar_gatos_vendidos.php' . "\n", FILE_APPEND);

// Incluir arquivo de configuração
require_once 'config.php';

// Definir cabeçalhos para JSON e CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Conectar ao banco de dados
    $pdo = conectarBD();
    
    // Verificar se a tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'gatos'");
    if ($stmt->rowCount() === 0) {
        // Tabela não existe
        echo json_encode([
            'success' => false,
            'message' => 'Tabela de gatos não existe'
        ]);
        exit;
    }
    
    // Consultar gatos vendidos
    $query = "
        SELECT g.*, 
               m.nome as matriz_nome, 
               p.nome as padreador_nome
        FROM `gatos` g
        LEFT JOIN `matrizes` m ON g.matriz_id = m.id
        LEFT JOIN `padreadores` p ON g.padreador_id = p.id
        WHERE g.status = 'vendido'
        ORDER BY g.nome ASC
    ";
    
    $stmt = $pdo->query($query);
    $gatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Log do número de gatos encontrados
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . ' - Encontrados ' . count($gatos) . ' gatos vendidos' . "\n", FILE_APPEND);
    
    // Retornar gatos vendidos
    echo json_encode([
        'success' => true,
        'gatos' => $gatos
    ]);
    
} catch (PDOException $e) {
    // Log do erro
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . ' - Erro: ' . $e->getMessage() . "\n", FILE_APPEND);
    
    // Erro de conexão com o banco de dados
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar gatos vendidos: ' . $e->getMessage()
    ]);
}
?> 