<?php
// Iniciar sessão
session_start();

// Incluir arquivo de configuração
require_once 'config.php';

// Definir cabeçalhos para JSON
header('Content-Type: application/json');

// Verificar se o usuário está logado
if (usuarioLogado()) {
    // Usuário está logado
    echo json_encode([
        'logado' => true,
        'usuario' => [
            'id' => $_SESSION['usuario_id'],
            'nome' => $_SESSION['usuario_nome'],
            'tipo' => $_SESSION['usuario_tipo']
        ]
    ]);
} else {
    // Usuário não está logado
    echo json_encode([
        'logado' => false,
        'message' => 'Usuário não está logado'
    ]);
}
?> 