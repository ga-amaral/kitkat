<?php
// Iniciar sessão
session_start();

// Incluir arquivo de configuração
require_once 'config.php';

// Definir cabeçalhos para JSON
header('Content-Type: application/json');

// Verificar se o usuário está logado
if (!usuarioLogado()) {
    echo json_encode([
        'success' => false,
        'message' => 'Acesso negado. Usuário não está logado.'
    ]);
    exit;
}

try {
    // Conectar ao banco de dados
    $pdo = conectarBD();
    
    // Verificar se a tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'gatos'");
    if ($stmt->rowCount() === 0) {
        // Tabela não existe, retornar array vazio
        echo json_encode([
            'success' => true,
            'gatos' => []
        ]);
        exit;
    }
    
    // Consultar todos os gatos com informações das matrizes e padreadores
    $query = "
        SELECT g.*, 
               m.nome as matriz_nome, 
               p.nome as padreador_nome
        FROM `gatos` g
        LEFT JOIN `matrizes` m ON g.matriz_id = m.id
        LEFT JOIN `padreadores` p ON g.padreador_id = p.id
        ORDER BY g.nome
    ";
    
    $stmt = $pdo->query($query);
    $gatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Processar os dados para converter as tags de JSON para arrays
    foreach ($gatos as &$gato) {
        if (isset($gato['tags_saude'])) {
            $gato['tags_saude'] = json_decode($gato['tags_saude'], true) ?: [];
        } else {
            $gato['tags_saude'] = [];
        }
        
        if (isset($gato['tags_personalidade'])) {
            $gato['tags_personalidade'] = json_decode($gato['tags_personalidade'], true) ?: [];
        } else {
            $gato['tags_personalidade'] = [];
        }
    }
    
    // Retornar lista de gatos
    echo json_encode([
        'success' => true,
        'gatos' => $gatos
    ]);
    
} catch (PDOException $e) {
    // Erro de conexão com o banco de dados
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar gatos: ' . $e->getMessage()
    ]);
}
?> 