<?php
// Iniciar sessão
session_start();

// Incluir arquivo de configuração
require_once 'config.php';

// Definir cabeçalhos para JSON
header('Content-Type: application/json');

// Verificar se a requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método de requisição inválido'
    ]);
    exit;
}

// Obter dados do formulário
$user = isset($_POST['user']) ? trim($_POST['user']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// Validar dados
if (empty($user) || empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Usuário e senha são obrigatórios'
    ]);
    exit;
}

try {
    // Conectar ao banco de dados
    $pdo = conectarBD();
    
    // Criptografar senha com MD5
    $password_md5 = criptografarSenha($password);
    
    // Consultar usuário no banco de dados
    $stmt = $pdo->prepare("SELECT * FROM `usuarios` WHERE `user_name` = ? AND `password` = ? LIMIT 1");
    $stmt->execute([$user, $password_md5]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verificar se o usuário existe
    if ($usuario) {
        // Remover a senha do array antes de enviar para o cliente
        unset($usuario['password']);
        
        // Salvar dados na sessão
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['name'];
        $_SESSION['usuario_tipo'] = $usuario['type'];
        $_SESSION['logado'] = true;
        
        // Retornar sucesso
        echo json_encode([
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'usuario' => [
                'id' => $usuario['id'],
                'name' => $usuario['name'],
                'user_name' => $usuario['user_name'],
                'type' => $usuario['type']
            ]
        ]);
    } else {
        // Usuário não encontrado ou senha incorreta
        echo json_encode([
            'success' => false,
            'message' => 'Usuário ou senha incorretos'
        ]);
    }
} catch (PDOException $e) {
    // Erro de conexão com o banco de dados
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao conectar ao banco de dados: ' . $e->getMessage()
    ]);
}
?> 