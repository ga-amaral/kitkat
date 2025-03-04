<?php
// Incluir arquivo de configuração
require_once 'config.php';
require_once 'proteger_admin.php';

// Verificar se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Obter dados do corpo da requisição
$dados = json_decode(file_get_contents('php://input'), true);

// Verificar se os dados necessários foram fornecidos
if (!isset($dados['id']) || !isset($dados['nome']) || !isset($dados['data_nascimento']) || 
    !isset($dados['raca']) || !isset($dados['ninhadas']) || !isset($dados['caracteristicas']) || 
    !isset($dados['foto']) || !isset($dados['linhagem'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

try {
    // Conectar ao banco de dados usando a função do config.php
    $pdo = conectarBD();
    
    // Verificar se a matriz existe
    $stmt = $pdo->prepare("SELECT id FROM matrizes WHERE id = ?");
    $stmt->execute([$dados['id']]);
    if ($stmt->rowCount() === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Matriz não encontrada']);
        exit;
    }
    
    // Atualizar a matriz
    $stmt = $pdo->prepare("
        UPDATE matrizes 
        SET nome = ?, data_nascimento = ?, raca = ?, ninhadas = ?, 
            caracteristicas = ?, foto = ?, linhagem = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $dados['nome'],
        $dados['data_nascimento'],
        $dados['raca'],
        $dados['ninhadas'],
        $dados['caracteristicas'],
        $dados['foto'],
        $dados['linhagem'],
        $dados['id']
    ]);
    
    // Verificar se a atualização foi bem-sucedida
    if ($stmt->rowCount() > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Matriz atualizada com sucesso']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Nenhuma alteração foi feita']);
    }
    
} catch (PDOException $e) {
    // Log do erro
    file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . ' - Erro em editar_matriz.php: ' . $e->getMessage() . "\n", FILE_APPEND);
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar matriz: ' . $e->getMessage()]);
}
?> 