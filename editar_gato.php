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
    !isset($dados['cor']) || !isset($dados['foto']) || !isset($dados['status']) || 
    !isset($dados['matriz_id']) || !isset($dados['padreador_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

try {
    // Conectar ao banco de dados usando a função do config.php
    $pdo = conectarBD();
    
    // Verificar se a matriz existe
    $stmt = $pdo->prepare("SELECT id FROM matrizes WHERE id = ?");
    $stmt->execute([$dados['matriz_id']]);
    if ($stmt->rowCount() === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Matriz não encontrada']);
        exit;
    }
    
    // Verificar se o padreador existe
    $stmt = $pdo->prepare("SELECT id FROM padreadores WHERE id = ?");
    $stmt->execute([$dados['padreador_id']]);
    if ($stmt->rowCount() === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Padreador não encontrado']);
        exit;
    }
    
    // Verificar se o gato existe
    $stmt = $pdo->prepare("SELECT id FROM gatos WHERE id = ?");
    $stmt->execute([$dados['id']]);
    if ($stmt->rowCount() === 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Gato não encontrado']);
        exit;
    }
    
    // Preparar tags
    $tags_saude = isset($dados['tags_saude']) ? json_encode($dados['tags_saude'], JSON_UNESCAPED_UNICODE) : '[]';
    $tags_personalidade = isset($dados['tags_personalidade']) ? json_encode($dados['tags_personalidade'], JSON_UNESCAPED_UNICODE) : '[]';
    
    // Atualizar o gato
    $stmt = $pdo->prepare("
        UPDATE gatos 
        SET nome = ?, data_nascimento = ?, cor = ?, foto = ?, descricao = ?, 
            status = ?, matriz_id = ?, padreador_id = ?, 
            tags_saude = ?, tags_personalidade = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $dados['nome'],
        $dados['data_nascimento'],
        $dados['cor'],
        $dados['foto'],
        $dados['descricao'] ?? '',
        $dados['status'],
        $dados['matriz_id'],
        $dados['padreador_id'],
        $tags_saude,
        $tags_personalidade,
        $dados['id']
    ]);
    
    // Verificar se a atualização foi bem-sucedida
    if ($stmt->rowCount() > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Gato atualizado com sucesso']);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Nenhuma alteração foi feita']);
    }
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar gato: ' . $e->getMessage()]);
}
?> 