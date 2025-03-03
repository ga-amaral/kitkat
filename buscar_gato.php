<?php
session_start();
require_once 'config.php';

// Configurar cabeçalhos para JSON e CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://gatilzaidan.com.br');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Log para debug
error_log("Iniciando busca de gato por ID");

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    error_log("Acesso não autorizado: usuário não está logado");
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    error_log("ID do gato não fornecido");
    echo json_encode(['success' => false, 'message' => 'ID do gato não fornecido']);
    exit;
}

$id = intval($_GET['id']);

try {
    // Buscar o gato no banco de dados
    $stmt = $conn->prepare("SELECT * FROM gatos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("Gato não encontrado com ID: $id");
        echo json_encode(['success' => false, 'message' => 'Gato não encontrado']);
        exit;
    }
    
    $gato = $result->fetch_assoc();
    
    // Buscar tags de saúde
    $stmt = $conn->prepare("SELECT tag FROM gatos_tags_saude WHERE gato_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tags_saude = [];
    while ($row = $result->fetch_assoc()) {
        $tags_saude[] = $row['tag'];
    }
    
    // Buscar tags de personalidade
    $stmt = $conn->prepare("SELECT tag FROM gatos_tags_personalidade WHERE gato_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tags_personalidade = [];
    while ($row = $result->fetch_assoc()) {
        $tags_personalidade[] = $row['tag'];
    }
    
    // Adicionar as tags ao objeto do gato
    $gato['tags_saude'] = $tags_saude;
    $gato['tags_personalidade'] = $tags_personalidade;
    
    error_log("Gato encontrado com sucesso: " . json_encode($gato));
    echo json_encode(['success' => true, 'gato' => $gato]);
    
} catch (Exception $e) {
    error_log("Erro ao buscar gato: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar gato: ' . $e->getMessage()]);
}
?> 