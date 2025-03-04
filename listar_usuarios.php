<?php
// Iniciar sessão
session_start();

// Incluir arquivo de configuração
require_once 'config.php';

// Definir cabeçalhos para JSON
header('Content-Type: application/json');

// Verificar se o usuário está logado e é administrador
if (!usuarioAdmin()) {
    echo json_encode([
        'success' => false,
        'message' => 'Acesso negado. Apenas administradores podem listar usuários.'
    ]);
    exit;
}

try {
    // Conectar ao banco de dados
    $pdo = conectarBD();
    
    // Consultar todos os usuários
    $stmt = $pdo->query("SELECT `id`, `name`, `user_name`, `type`, `created_at` FROM `usuarios` ORDER BY `name`");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar os dados para o formato esperado pelo frontend
    $usuariosFormatados = array_map(function($usuario) {
        return [
            'id' => $usuario['id'],
            'name' => $usuario['name'],
            'user' => $usuario['user_name'], // Renomear para 'user' como esperado pelo frontend
            'type' => $usuario['type'],
            'created_at' => $usuario['created_at']
        ];
    }, $usuarios);
    
    // Retornar lista de usuários
    echo json_encode([
        'success' => true,
        'usuarios' => $usuariosFormatados
    ]);
    
} catch (PDOException $e) {
    // Erro de conexão com o banco de dados
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao listar usuários: ' . $e->getMessage()
    ]);
}
?> 