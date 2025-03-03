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
        echo json_encode([
            'success' => false, 
            'message' => 'Usuário não está logado'
        ]);
        exit;
    }

    // Verifica se o usuário atual é admin
    $stmt = $conn->prepare("SELECT type FROM user WHERE id = ? AND logado = 1");
    $stmt->execute([$_SESSION['user_id']]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentUser || $currentUser['type'] !== 'admin') {
        http_response_code(403);
        echo json_encode([
            'success' => false, 
            'message' => 'Acesso não autorizado'
        ]);
        exit;
    }

    // Busca todos os usuários
    $stmt = $conn->prepare("SELECT id, name, user, type FROM user ORDER BY name");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($usuarios === false) {
        throw new PDOException("Erro ao buscar usuários");
    }

    echo json_encode([
        'success' => true,
        'usuarios' => $usuarios
    ]);

} catch (PDOException $e) {
    error_log("Erro de banco de dados ao buscar usuários: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar usuários do banco de dados: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Erro ao buscar usuários: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
?> 