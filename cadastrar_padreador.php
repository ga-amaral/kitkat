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
        'message' => 'Acesso negado. Apenas administradores podem cadastrar padreadores.'
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
$nome = isset($data['nome']) ? trim($data['nome']) : '';
$data_nascimento = isset($data['data_nascimento']) ? trim($data['data_nascimento']) : '';
$raca = isset($data['raca']) ? trim($data['raca']) : '';
$caracteristicas = isset($data['caracteristicas']) ? trim($data['caracteristicas']) : '';
$foto = isset($data['foto']) ? trim($data['foto']) : '';
$linhagem = isset($data['linhagem']) ? trim($data['linhagem']) : '';

// Validar dados
if (empty($nome) || empty($data_nascimento) || empty($raca) || empty($caracteristicas) || empty($foto)) {
    echo json_encode([
        'success' => false,
        'message' => 'Todos os campos são obrigatórios'
    ]);
    exit;
}

try {
    // Conectar ao banco de dados
    $pdo = conectarBD();
    
    // Criar tabela de padreadores se não existir
    $pdo->exec("CREATE TABLE IF NOT EXISTS `padreadores` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `nome` VARCHAR(100) NOT NULL,
        `data_nascimento` DATE NOT NULL,
        `raca` VARCHAR(100) NOT NULL,
        `caracteristicas` TEXT NOT NULL,
        `foto` VARCHAR(255) NOT NULL,
        `linhagem` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    
    // Inserir novo padreador
    $stmt = $pdo->prepare("INSERT INTO `padreadores` (`nome`, `data_nascimento`, `raca`, `caracteristicas`, `foto`, `linhagem`) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nome, $data_nascimento, $raca, $caracteristicas, $foto, $linhagem]);
    
    // Obter ID do padreador inserido
    $id = $pdo->lastInsertId();
    
    // Retornar sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Padreador cadastrado com sucesso',
        'padreador' => [
            'id' => $id,
            'nome' => $nome,
            'data_nascimento' => $data_nascimento,
            'raca' => $raca,
            'caracteristicas' => $caracteristicas,
            'foto' => $foto,
            'linhagem' => $linhagem
        ]
    ]);
    
} catch (PDOException $e) {
    // Erro de conexão com o banco de dados
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao cadastrar padreador: ' . $e->getMessage()
    ]);
}
?> 