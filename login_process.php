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
// Permitir acesso tanto do domínio de produção quanto de localhost para desenvolvimento
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');

// Headers para prevenir cache
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

// Responde à requisição OPTIONS do CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
}

// Modo debug
$debug = isset($_GET['debug']) && $_GET['debug'] === 'true';
$debug_messages = [];

function debugLog($message) {
    global $debug, $debug_messages;
    if ($debug) {
        $debug_messages[] = $message;
    }
    // Sempre registra no log do servidor
    error_log($message);
}

$response = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['user'];
    $password = $_POST['password'];
    
    debugLog("Tentativa de login para usuário: $username");
    
    try {
        // Limpa qualquer sessão anterior
        session_regenerate_id(true);
        $_SESSION = array();
        
        // Gera o hash MD5 da senha fornecida
        $password_hash = md5($password);
        
        // Busca o usuário
        $stmt = $conn->prepare("SELECT * FROM user WHERE user = ? AND password = ?");
        $stmt->execute([$username, $password_hash]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Login bem-sucedido
            debugLog("Login bem-sucedido para: $username (ID: {$user['id']})");
            
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
            debugLog("Falha no login para: $username - Credenciais inválidas");
            $response = [
                'success' => false,
                'message' => 'Usuário ou senha inválidos'
            ];
        }
    } catch (PDOException $e) {
        debugLog("Erro no login para $username: " . $e->getMessage());
        error_log("Erro no login: " . $e->getMessage());
        $response = [
            'success' => false,
            'message' => 'Erro ao realizar login'
        ];
    }
} else {
    debugLog("Método não permitido: " . $_SERVER['REQUEST_METHOD']);
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