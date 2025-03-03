<?php
session_start();
require_once 'config.php';

// Headers necessários
header('Content-Type: application/json; charset=utf-8');
// Permitir acesso tanto do domínio de produção quanto de localhost para desenvolvimento
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');

// Headers para prevenir cache - mais rigorosos
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

// Log para debug
error_log("Verificando sessão para: " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'Sessão não iniciada'));

// Responde à requisição OPTIONS do CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
}

// Verifica se o usuário está logado
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    // Verifica também no banco de dados
    $stmt = $conn->prepare("SELECT logado FROM user WHERE id = ? AND logado = 1");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Log para debug
        error_log("Usuário {$_SESSION['username']} está logado");
        
        echo json_encode([
            'logado' => true,
            'id' => $_SESSION['user_id'],
            'nome' => $_SESSION['nome'],
            'user' => $_SESSION['username'],
            'tipo' => $_SESSION['user_type']
        ]);
    } else {
        // Se não estiver logado no banco, limpa a sessão
        error_log("Usuário {$_SESSION['username']} não está logado no banco de dados");
        session_destroy();
        echo json_encode([
            'logado' => false,
            'message' => 'Sessão inválida'
        ]);
    }
} else {
    // Limpa qualquer sessão existente
    error_log("Nenhuma sessão ativa encontrada");
    session_destroy();
    echo json_encode([
        'logado' => false,
        'message' => 'Não logado'
    ]);
}
?> 