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
    $stmt = $pdo->query("SHOW TABLES LIKE 'matrizes'");
    if ($stmt->rowCount() === 0) {
        // Tabela não existe, retornar array vazio
        echo json_encode([
            'success' => true,
            'matrizes' => []
        ]);
        exit;
    }
    
    // Consultar todas as matrizes
    $stmt = $pdo->query("SELECT * FROM `matrizes` ORDER BY `nome`");
    $matrizes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Retornar lista de matrizes
    echo json_encode([
        'success' => true,
        'matrizes' => $matrizes
    ]);
    
} catch (PDOException $e) {
    // Erro de conexão com o banco de dados
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar matrizes: ' . $e->getMessage()
    ]);
}
?> 