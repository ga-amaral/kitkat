<?php
// Iniciar sessão
session_start();

// Incluir arquivo de configuração
require_once 'config.php';

// Definir cabeçalhos para JSON
header('Content-Type: application/json');

// Verificar se o usuário está logado e é administrador
if (!usuarioAdmin()) {
    echo json_encode([
        'success' => false,
        'message' => 'Acesso negado. Apenas administradores podem excluir usuários.'
    ]);
    exit;
}

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID do usuário não fornecido ou inválido'
    ]);
    exit;
}

$id = (int) $_GET['id'];

// Impedir que o usuário exclua a si mesmo
if ($id === (int) $_SESSION['usuario_id']) {
    echo json_encode([
        'success' => false,
        'message' => 'Você não pode excluir seu próprio usuário'
    ]);
    exit;
}

try {
    // Conectar ao banco de dados
    $pdo = conectarBD();
    
    // Verificar se o usuário existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `usuarios` WHERE `id` = ?");
    $stmt->execute([$id]);
    $count = $stmt->fetchColumn();
    
    if ($count === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Usuário não encontrado'
        ]);
        exit;
    }
    
    // Excluir o usuário
    $stmt = $pdo->prepare("DELETE FROM `usuarios` WHERE `id` = ?");
    $stmt->execute([$id]);
    
    // Retornar sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Usuário excluído com sucesso'
    ]);
    
} catch (PDOException $e) {
    // Erro de conexão com o banco de dados
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao excluir usuário: ' . $e->getMessage()
    ]);
}
?> 