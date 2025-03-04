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
        'message' => 'Acesso negado. Apenas administradores podem cadastrar usuários.'
    ]);
    exit;
}

// Verificar se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método de requisição inválido'
    ]);
    exit;
}

// Obter dados do corpo da requisição (JSON)
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Se não conseguir decodificar o JSON, tenta obter do $_POST
if (json_last_error() !== JSON_ERROR_NONE) {
    $data = $_POST;
}

// Obter dados do formulário
$name = isset($data['nome']) ? trim($data['nome']) : '';
$user_name = isset($data['username']) ? trim($data['username']) : '';
$password = isset($data['password']) ? trim($data['password']) : '';
$type = isset($data['tipo']) ? trim($data['tipo']) : 'user';

// Validar dados
if (empty($name) || empty($user_name) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Todos os campos são obrigatórios'
    ]);
    exit;
}

// Validar tipo de usuário
if ($type !== 'admin' && $type !== 'user' && $type !== 'usuario') {
    $type = 'user'; // Valor padrão
}

// Converter 'usuario' para 'user' para compatibilidade
if ($type === 'usuario') {
    $type = 'user';
}

try {
    // Conectar ao banco de dados
    $pdo = conectarBD();
    
    // Verificar se o usuário já existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `usuarios` WHERE `user_name` = ?");
    $stmt->execute([$user_name]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Nome de usuário já está em uso'
        ]);
        exit;
    }
    
    // Criptografar senha com MD5
    $password_md5 = criptografarSenha($password);
    
    // Inserir novo usuário
    $stmt = $pdo->prepare("INSERT INTO `usuarios` (`name`, `user_name`, `password`, `type`) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $user_name, $password_md5, $type]);
    
    // Obter ID do usuário inserido
    $id = $pdo->lastInsertId();
    
    // Retornar sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Usuário cadastrado com sucesso',
        'usuario' => [
            'id' => $id,
            'name' => $name,
            'user_name' => $user_name,
            'type' => $type
        ]
    ]);
    
} catch (PDOException $e) {
    // Erro de conexão com o banco de dados
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao cadastrar usuário: ' . $e->getMessage()
    ]);
}
?> 