<?php
// Configurações do banco de dados
$config = [
    'db' => [
        'host' => '127.0.0.1',
        'dbname' => 'gatilzaidan',
        'username' => 'gatilbernardo',
        'password' => 'V6$Z2Asn95',
        'charset' => 'utf8'
    ],
    'app' => [
        'name' => 'Gatil Zaidan',
        'url' => 'https://gatilzaidan.com.br'
    ]
];

// Função para conectar ao banco de dados
function conectarBD() {
    global $config;
    
    try {
        // Log para depuração
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . ' - Tentando conectar ao banco de dados: ' . $config['db']['host'] . '/' . $config['db']['dbname'] . "\n", FILE_APPEND);
        
        $dsn = "mysql:host={$config['db']['host']};port=3306;dbname={$config['db']['dbname']};charset={$config['db']['charset']}";
        $pdo = new PDO($dsn, $config['db']['username'], $config['db']['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Log para depuração
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . ' - Conexão bem-sucedida ao banco de dados' . "\n", FILE_APPEND);
        
        return $pdo;
    } catch (PDOException $e) {
        // Log para depuração
        file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . ' - Erro de conexão ao banco de dados: ' . $e->getMessage() . "\n", FILE_APPEND);
        
        die("Erro de conexão: " . $e->getMessage());
    }
}

// Função para verificar se o usuário está logado
function usuarioLogado() {
    return isset($_SESSION['logado']) && $_SESSION['logado'] === true;
}

// Função para verificar se o usuário é administrador
function usuarioAdmin() {
    return usuarioLogado() && isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin';
}

// Função para redirecionar para a página de login
function redirecionarLogin() {
    header('Location: login.html');
    exit;
}

// Função para criptografar senha
function criptografarSenha($senha) {
    return md5($senha);
}
?> 