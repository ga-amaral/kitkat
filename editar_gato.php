<?php
session_start();
require_once 'config.php';

// Configurar cabeçalhos para JSON e CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://gatilzaidan.com.br');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Log para debug
error_log("Iniciando edição de gato");

// Responder à requisição OPTIONS do CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
}

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    error_log("Acesso não autorizado: usuário não está logado");
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

// Obter dados do corpo da requisição
$data = json_decode(file_get_contents('php://input'), true);
error_log("Dados recebidos: " . json_encode($data));

// Verificar se o ID foi fornecido
if (!isset($data['id']) || empty($data['id'])) {
    error_log("ID do gato não fornecido");
    echo json_encode(['success' => false, 'message' => 'ID do gato não fornecido']);
    exit;
}

// Verificar se todos os campos obrigatórios foram fornecidos
$campos_obrigatorios = ['nome', 'data_nascimento', 'cor', 'foto', 'descricao', 'status'];
foreach ($campos_obrigatorios as $campo) {
    if (!isset($data[$campo]) || empty($data[$campo])) {
        error_log("Campo obrigatório não fornecido: $campo");
        echo json_encode(['success' => false, 'message' => "Campo obrigatório não fornecido: $campo"]);
        exit;
    }
}

try {
    // Verificar se o gato existe
    $stmt = $conn->prepare("SELECT id FROM gatos WHERE id = ?");
    $stmt->bind_param("i", $data['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("Gato não encontrado com ID: " . $data['id']);
        echo json_encode(['success' => false, 'message' => 'Gato não encontrado']);
        exit;
    }
    
    // Iniciar transação
    $conn->begin_transaction();
    
    // Atualizar o gato
    $stmt = $conn->prepare("
        UPDATE gatos SET 
            nome = ?, 
            data_nascimento = ?, 
            cor = ?, 
            foto = ?, 
            descricao = ?, 
            status = ?, 
            matriz_id = ?, 
            padreador_id = ?
        WHERE id = ?
    ");
    
    // Converter IDs vazios para NULL
    $matriz_id = !empty($data['matriz_id']) ? $data['matriz_id'] : null;
    $padreador_id = !empty($data['padreador_id']) ? $data['padreador_id'] : null;
    
    $stmt->bind_param(
        "ssssssiis", 
        $data['nome'], 
        $data['data_nascimento'], 
        $data['cor'], 
        $data['foto'], 
        $data['descricao'], 
        $data['status'], 
        $matriz_id, 
        $padreador_id,
        $data['id']
    );
    
    $stmt->execute();
    
    // Excluir tags de saúde existentes
    $stmt = $conn->prepare("DELETE FROM gatos_tags_saude WHERE gato_id = ?");
    $stmt->bind_param("i", $data['id']);
    $stmt->execute();
    
    // Inserir novas tags de saúde
    if (isset($data['tags_saude']) && is_array($data['tags_saude'])) {
        $stmt = $conn->prepare("INSERT INTO gatos_tags_saude (gato_id, tag) VALUES (?, ?)");
        
        foreach ($data['tags_saude'] as $tag) {
            $stmt->bind_param("is", $data['id'], $tag);
            $stmt->execute();
        }
    }
    
    // Excluir tags de personalidade existentes
    $stmt = $conn->prepare("DELETE FROM gatos_tags_personalidade WHERE gato_id = ?");
    $stmt->bind_param("i", $data['id']);
    $stmt->execute();
    
    // Inserir novas tags de personalidade
    if (isset($data['tags_personalidade']) && is_array($data['tags_personalidade'])) {
        $stmt = $conn->prepare("INSERT INTO gatos_tags_personalidade (gato_id, tag) VALUES (?, ?)");
        
        foreach ($data['tags_personalidade'] as $tag) {
            $stmt->bind_param("is", $data['id'], $tag);
            $stmt->execute();
        }
    }
    
    // Commit da transação
    $conn->commit();
    
    error_log("Gato atualizado com sucesso: " . $data['id']);
    echo json_encode(['success' => true, 'message' => 'Gato atualizado com sucesso']);
    
} catch (Exception $e) {
    // Rollback em caso de erro
    $conn->rollback();
    error_log("Erro ao atualizar gato: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar gato: ' . $e->getMessage()]);
}
?> 