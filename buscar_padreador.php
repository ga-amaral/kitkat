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
    
    // Buscar o padreador pelo ID
    $stmt = $pdo->prepare("
        SELECT * FROM padreadores WHERE id = ?
    ");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Padreador não encontrado']);
        exit;
    }
    
    $padreador = $stmt->fetch(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'padreador' => $padreador]);
    
} catch (PDOException $e) {
    // Log do erro
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . ' - Erro em buscar_padreador.php: ' . $e->getMessage() . "\n", FILE_APPEND);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar padreador: ' . $e->getMessage()]);
}
?> 