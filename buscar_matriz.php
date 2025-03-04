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
    
    // Buscar a matriz pelo ID
    $stmt = $pdo->prepare("
        SELECT * FROM matrizes WHERE id = ?
    ");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Matriz não encontrada']);
        exit;
    }
    
    $matriz = $stmt->fetch(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'matriz' => $matriz]);
    
} catch (PDOException $e) {
    // Log do erro
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . ' - Erro em buscar_matriz.php: ' . $e->getMessage() . "\n", FILE_APPEND);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar matriz: ' . $e->getMessage()]);
}
?> 