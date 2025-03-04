<?php
// Incluir arquivo de configuração
require_once 'config.php';
require_once 'proteger_admin.php';

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$id = (int)$_GET['id'];

try {
    // Conectar ao banco de dados usando a função do config.php
    $pdo = conectarBD();
    
    // Verificar se a matriz existe
    $stmt = $pdo->prepare("SELECT id FROM matrizes WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Matriz não encontrada']);
        exit;
    }
    
    // Verificar se existem gatos associados a esta matriz
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM gatos WHERE matriz_id = ?");
    $stmt->execute([$id]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Não é possível excluir esta matriz pois existem gatos associados a ela']);
        exit;
    }
    
    // Excluir a matriz
    $stmt = $pdo->prepare("DELETE FROM matrizes WHERE id = ?");
    $stmt->execute([$id]);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Matriz excluída com sucesso']);
    
} catch (PDOException $e) {
    // Log do erro
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . ' - Erro em excluir_matriz.php: ' . $e->getMessage() . "\n", FILE_APPEND);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir matriz: ' . $e->getMessage()]);
}
?> 