<?php
session_start();
require_once 'config.php';

// Headers necessários
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://gatilzaidan.com.br');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Accept');
header('Access-Control-Allow-Credentials: true');

// Log para debug
error_log("Iniciando cadastrar_usuario.php");

// Responde à requisição OPTIONS do CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Verifica se o usuário está logado e é admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    error_log("Usuário não autorizado: user_id=" . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'não definido') . ", user_type=" . (isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'não definido'));
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Usuário não autorizado'
    ]);
    exit;
}

// Recebe os dados do POST
$input = file_get_contents('php://input');
error_log("Dados brutos recebidos: " . $input);

$dados = json_decode($input, true);
error_log("Dados decodificados: " . print_r($dados, true));

// Validação dos dados
if (!isset($dados['nome']) || empty($dados['nome']) ||
    !isset($dados['username']) || empty($dados['username']) ||
    !isset($dados['password']) || empty($dados['password']) ||
    !isset($dados['tipo']) || empty($dados['tipo'])) {
    
    error_log("Dados incompletos: " . print_r($dados, true));
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Dados incompletos',
        'dados' => $dados
    ]);
    exit;
}

try {
    // Verifica se o usuário já existe
    $stmt = $conn->prepare("SELECT id FROM user WHERE user = ?");
    $stmt->execute([$dados['username']]);
    
    if ($stmt->fetch()) {
        error_log("Usuário já existe: " . $dados['username']);
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Nome de usuário já existe'
        ]);
        exit;
    }

    // Insere o novo usuário
    $stmt = $conn->prepare("INSERT INTO user (name, user, password, type) VALUES (?, ?, ?, ?)");
    $result = $stmt->execute([
        $dados['nome'],
        $dados['username'],
        md5($dados['password']),
        $dados['tipo']
    ]);

    if ($result) {
        error_log("Usuário cadastrado com sucesso: " . $dados['username']);
        echo json_encode([
            'success' => true,
            'message' => 'Usuário cadastrado com sucesso'
        ]);
    } else {
        throw new Exception('Erro ao inserir usuário no banco de dados');
    }
} catch (Exception $e) {
    error_log("Erro ao cadastrar usuário: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao cadastrar usuário: ' . $e->getMessage()
    ]);
}
?> 