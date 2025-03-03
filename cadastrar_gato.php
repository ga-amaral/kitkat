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
error_log("Iniciando cadastro de gato");

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
    // Iniciar transação
    $conn->begin_transaction();
    
    // Inserir o gato
    $stmt = $conn->prepare("
        INSERT INTO gatos (
            nome, data_nascimento, cor, foto, descricao, status, matriz_id, padreador_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    // Converter IDs vazios para NULL
    $matriz_id = !empty($data['matriz_id']) ? $data['matriz_id'] : null;
    $padreador_id = !empty($data['padreador_id']) ? $data['padreador_id'] : null;
    
    $stmt->bind_param(
        "ssssssii", 
        $data['nome'], 
        $data['data_nascimento'], 
        $data['cor'], 
        $data['foto'], 
        $data['descricao'], 
        $data['status'], 
        $matriz_id, 
        $padreador_id
    );
    
    $stmt->execute();
    $gato_id = $conn->insert_id;
    
    // Inserir tags de saúde
    if (isset($data['tags_saude']) && is_array($data['tags_saude'])) {
        $stmt = $conn->prepare("INSERT INTO gatos_tags_saude (gato_id, tag) VALUES (?, ?)");
        
        foreach ($data['tags_saude'] as $tag) {
            $stmt->bind_param("is", $gato_id, $tag);
            $stmt->execute();
        }
    }
    
    // Inserir tags de personalidade
    if (isset($data['tags_personalidade']) && is_array($data['tags_personalidade'])) {
        $stmt = $conn->prepare("INSERT INTO gatos_tags_personalidade (gato_id, tag) VALUES (?, ?)");
        
        foreach ($data['tags_personalidade'] as $tag) {
            $stmt->bind_param("is", $gato_id, $tag);
            $stmt->execute();
        }
    }
    
    // Commit da transação
    $conn->commit();
    
    error_log("Gato cadastrado com sucesso: $gato_id");
    echo json_encode(['success' => true, 'message' => 'Gato cadastrado com sucesso', 'id' => $gato_id]);
    
} catch (Exception $e) {
    // Rollback em caso de erro
    $conn->rollback();
    error_log("Erro ao cadastrar gato: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar gato: ' . $e->getMessage()]);
}
?> 