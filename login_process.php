<?php
// Buffer de saída
ob_start();

session_start();
require_once 'config.php';

// Limpa qualquer saída do config.php
ob_clean();

// Desabilita exibição de erros
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Headers necessários
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://gatilzaidan.com.br');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Modo debug
$debug = isset($_GET['debug']) && $_GET['debug'] === 'true';
$debug_messages = [];

function debugLog($message) {
    global $debug, $debug_messages;
    if ($debug) {
        $debug_messages[] = $message;
    }
}

$response = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['user'];
    $password = $_POST['password'];
    
    try {
        // Gera o hash MD5 da senha fornecida
        $password_hash = md5($password);
        
        // Busca o usuário
        $stmt = $conn->prepare("SELECT * FROM user WHERE user = ? AND password = ?");
        $stmt->execute([$username, $password_hash]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Login bem-sucedido
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['user'];
            $_SESSION['user_type'] = $user['type'];
            $_SESSION['nome'] = $user['name'];
            $_SESSION['logado'] = true;
            $_SESSION['tipo'] = $user['type'];

            // Atualiza o status de logado no banco
            $stmt = $conn->prepare("UPDATE user SET logado = 1 WHERE id = ?");
            $stmt->execute([$user['id']]);

            $response = [
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'usuario' => [
                    'id' => $user['id'],
                    'nome' => $user['name'],
                    'user' => $user['user'],
                    'type' => $user['type']
                ]
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Usuário ou senha inválidos'
            ];
        }
    } catch (PDOException $e) {
        error_log("Erro no login: " . $e->getMessage());
        $response = [
            'success' => false,
            'message' => 'Erro ao realizar login'
        ];
    }
} else {
    $response = [
        'success' => false,
        'message' => 'Método não permitido'
    ];
}

// Adiciona mensagens de debug à resposta se estiver em modo debug
if ($debug) {
    $response['debug'] = $debug_messages;
}

// Limpa qualquer saída anterior
ob_clean();

// Retorna sempre JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
?> 