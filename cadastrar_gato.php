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
        'message' => 'Acesso negado. Apenas administradores podem cadastrar gatos.'
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
$cor = isset($data['cor']) ? trim($data['cor']) : '';
$foto = isset($data['foto']) ? trim($data['foto']) : '';
$descricao = isset($data['descricao']) ? trim($data['descricao']) : '';
$status = isset($data['status']) ? trim($data['status']) : 'disponivel';
$matriz_id = isset($data['matriz_id']) ? intval($data['matriz_id']) : 0;
$padreador_id = isset($data['padreador_id']) ? intval($data['padreador_id']) : 0;
$tags_saude = isset($data['tags_saude']) ? $data['tags_saude'] : [];
$tags_personalidade = isset($data['tags_personalidade']) ? $data['tags_personalidade'] : [];

// Validar dados
if (empty($nome) || empty($data_nascimento) || empty($cor) || empty($foto) || empty($descricao)) {
    echo json_encode([
        'success' => false,
        'message' => 'Todos os campos são obrigatórios'
    ]);
    exit;
}

// Validar status
if ($status !== 'disponivel' && $status !== 'vendido') {
    $status = 'disponivel'; // Valor padrão
}

try {
    // Conectar ao banco de dados
    $pdo = conectarBD();
    
    // Criar tabela de gatos se não existir
    $pdo->exec("CREATE TABLE IF NOT EXISTS `gatos` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `nome` VARCHAR(100) NOT NULL,
        `data_nascimento` DATE NOT NULL,
        `cor` VARCHAR(100) NOT NULL,
        `foto` VARCHAR(255) NOT NULL,
        `descricao` TEXT NOT NULL,
        `status` ENUM('disponivel', 'vendido') NOT NULL DEFAULT 'disponivel',
        `matriz_id` INT(11),
        `padreador_id` INT(11),
        `tags_saude` TEXT,
        `tags_personalidade` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`matriz_id`) REFERENCES `matrizes`(`id`) ON DELETE SET NULL,
        FOREIGN KEY (`padreador_id`) REFERENCES `padreadores`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    
    // Converter arrays de tags para strings JSON
    $tags_saude_json = json_encode($tags_saude);
    $tags_personalidade_json = json_encode($tags_personalidade);
    
    // Inserir novo gato
    $stmt = $pdo->prepare("INSERT INTO `gatos` (`nome`, `data_nascimento`, `cor`, `foto`, `descricao`, `status`, `matriz_id`, `padreador_id`, `tags_saude`, `tags_personalidade`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nome, $data_nascimento, $cor, $foto, $descricao, $status, $matriz_id ?: null, $padreador_id ?: null, $tags_saude_json, $tags_personalidade_json]);
    
    // Obter ID do gato inserido
    $id = $pdo->lastInsertId();
    
    // Retornar sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Gato cadastrado com sucesso',
        'gato' => [
            'id' => $id,
            'nome' => $nome,
            'data_nascimento' => $data_nascimento,
            'cor' => $cor,
            'foto' => $foto,
            'descricao' => $descricao,
            'status' => $status,
            'matriz_id' => $matriz_id,
            'padreador_id' => $padreador_id,
            'tags_saude' => $tags_saude,
            'tags_personalidade' => $tags_personalidade
        ]
    ]);
    
} catch (PDOException $e) {
    // Erro de conexão com o banco de dados
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao cadastrar gato: ' . $e->getMessage()
    ]);
}
?> 