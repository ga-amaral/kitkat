<?php
session_start();
require_once 'config.php';

// Headers necessários
header('Content-Type: application/json; charset=utf-8');
// Permitir acesso tanto do domínio de produção quanto de localhost para desenvolvimento
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');

// Headers para prevenir cache
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

// Responde à requisição OPTIONS do CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
}

// Log para debug
error_log("Logout iniciado para: " . (isset($_SESSION['username']) ? $_SESSION['username'] : 'Sessão não identificada'));

// Atualiza o status de logado no banco
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $conn->prepare("UPDATE user SET logado = 0 WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        error_log("Status de logado atualizado para o usuário ID: {$_SESSION['user_id']}");
    } catch (Exception $e) {
        error_log("Erro ao atualizar status de logado: " . $e->getMessage());
    }
}

// Salva o nome de usuário para o log
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'desconhecido';

// Destrói a sessão
$_SESSION = array();
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}
session_destroy();

error_log("Logout concluído para: $username");

// Retorna sucesso
echo json_encode([
    'success' => true,
    'message' => 'Logout realizado com sucesso'
]);
?> 