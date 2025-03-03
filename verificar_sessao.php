<?php
session_start();
require_once 'config.php';

// Headers necessários
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://gatilzaidan.com.br');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Headers para prevenir cache
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Verifica se o usuário está logado
if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    // Verifica também no banco de dados
    $stmt = $conn->prepare("SELECT logado FROM user WHERE id = ? AND logado = 1");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode([
            'logado' => true,
            'id' => $_SESSION['user_id'],
            'nome' => $_SESSION['nome'],
            'user' => $_SESSION['username'],
            'tipo' => $_SESSION['user_type']
        ]);
    } else {
        // Se não estiver logado no banco, limpa a sessão
        session_destroy();
        echo json_encode([
            'logado' => false
        ]);
    }
} else {
    // Limpa qualquer sessão existente
    session_destroy();
    echo json_encode([
        'logado' => false
    ]);
}
?> 