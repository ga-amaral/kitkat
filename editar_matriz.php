<?php
session_start();
require_once 'config.php';

// Headers necessários
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://gatilzaidan.com.br');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Access-Control-Allow-Credentials: true');

// Log para debug
error_log("Iniciando edição de matriz");

// Responde à requisição OPTIONS do CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
}

// Verifica se o usuário está logado e é admin
if (!isset($_SESSION['user_id'])) {
    error_log("Usuário não está logado");
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Usuário não está logado'
    ]);
    exit;
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    error_log("Usuário não é administrador");
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Acesso permitido apenas para administradores'
    ]);
    exit;
}

// Recebe e decodifica os dados JSON
$json = file_get_contents('php://input');
$dados = json_decode($json, true);

error_log("Dados recebidos: " . print_r($dados, true));

// Verifica se todos os campos necessários foram enviados
if (!isset($dados['id']) || !isset($dados['nome']) || !isset($dados['data_nascimento']) || 
    !isset($dados['raca']) || !isset($dados['ninhadas']) || !isset($dados['caracteristicas']) || 
    !isset($dados['foto']) || !isset($dados['linhagem'])) {
    
    error_log("Dados incompletos");
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Todos os campos são obrigatórios'
    ]);
    exit;
}

try {
    // Verifica se a matriz existe
    $stmt = $conn->prepare("SELECT id FROM matrizes WHERE id = ?");
    $stmt->execute([$dados['id']]);
    
    if ($stmt->rowCount() === 0) {
        error_log("Matriz não encontrada: " . $dados['id']);
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Matriz não encontrada'
        ]);
        exit;
    }
    
    // Verifica se já existe outra matriz com o mesmo nome
    $stmt = $conn->prepare("SELECT id FROM matrizes WHERE nome = ? AND id != ?");
    $stmt->execute([$dados['nome'], $dados['id']]);
    
    if ($stmt->rowCount() > 0) {
        error_log("Matriz com nome já existente: " . $dados['nome']);
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Já existe outra matriz com este nome'
        ]);
        exit;
    }
    
    // Atualiza a matriz
    $stmt = $conn->prepare("
        UPDATE matrizes 
        SET nome = ?, 
            data_nascimento = ?, 
            raca = ?, 
            ninhadas = ?, 
            caracteristicas_filhotes = ?, 
            foto = ?, 
            linhagem = ?
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
    
    error_log("Matriz atualizada com sucesso. ID: " . $dados['id']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Matriz atualizada com sucesso'
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao atualizar matriz: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao atualizar matriz: ' . $e->getMessage()
    ]);
}
?> 