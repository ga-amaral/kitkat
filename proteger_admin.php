<?php
// Iniciar sessão
session_start();

// Incluir arquivo de configuração
require_once 'config.php';

// Verificar se o usuário está logado e é administrador
if (!usuarioAdmin()) {
    // Redirecionar para a página de login
    header('Location: login.html');
    exit;
}
?> 