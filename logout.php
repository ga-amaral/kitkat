<?php
// Iniciar sessão
session_start();

// Definir cabeçalhos para JSON
header('Content-Type: application/json');

// Destruir todas as variáveis de sessão
$_SESSION = array();

// Se for necessário destruir o cookie de sessão
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir a sessão
session_destroy();

// Retornar sucesso
echo json_encode([
    'success' => true,
    'message' => 'Logout realizado com sucesso'
]);
?> 