<?php
session_start();
require_once 'config.php';

// Headers necessários
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

try {
    // Verifica se o usuário está logado
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['logado' => false, 'mensagem' => 'Usuário não está logado']);
        exit;
    }

    // Verifica no banco de dados
    $stmt = $conn->prepare("SELECT id, name, user, type, logado FROM user WHERE id = ? AND logado = 1");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        session_destroy();
        http_response_code(401);
        echo json_encode(['logado' => false, 'mensagem' => 'Sessão inválida']);
        exit;
    }

    http_response_code(200);
    echo json_encode([
        'logado' => true,
        'id' => $user['id'],
        'nome' => $user['name'],
        'user' => $user['user'],
        'tipo' => $user['type']
    ]);

} catch (PDOException $e) {
    error_log("Erro de banco de dados na verificação de sessão: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['logado' => false, 'mensagem' => 'Erro interno do servidor']);
} catch (Exception $e) {
    error_log("Erro na verificação de sessão: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['logado' => false, 'mensagem' => 'Erro interno do servidor']);
}
?> 