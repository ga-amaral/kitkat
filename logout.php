<?php
session_start();
require_once 'config.php';

// Headers necessários
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://gatilzaidan.com.br');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Atualiza o status de logado no banco
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("UPDATE user SET logado = 0 WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

// Destrói a sessão
session_destroy();

// Retorna sucesso
echo json_encode([
    'success' => true,
    'message' => 'Logout realizado com sucesso'
]);
?> 