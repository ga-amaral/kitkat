<?php
// Log para depuração
file_put_contents('debug_log.txt', date('Y-m-d H:i:s') . ' - Acessou buscar_gato.php - ID: ' . ($_GET['id'] ?? 'não informado') . "\n", FILE_APPEND);

// Incluir arquivo de configuração
require_once 'config.php';

// Definir cabeçalhos para JSON e CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar se o ID foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID do gato não fornecido ou inválido'
    ]);
    exit;
}

$id = (int)$_GET['id'];

try {
    // Conectar ao banco de dados
    $pdo = conectarBD();
    
    // Verificar se a tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'gatos'");
    if ($stmt->rowCount() === 0) {
        // Tabela não existe
        echo json_encode([
            'success' => false,
            'message' => 'Tabela de gatos não existe'
        ]);
        exit;
    }
    
    // Consultar gato pelo ID com informações das matrizes e padreadores, incluindo linhagem
    $query = "
        SELECT g.*, 
               m.nome as matriz_nome, 
               m.raca as matriz_raca,
               m.foto as matriz_foto,
               m.ninhadas as matriz_ninhadas,
               m.linhagem as matriz_linhagem,
               p.nome as padreador_nome,
               p.raca as padreador_raca,
               p.foto as padreador_foto,
               p.linhagem as padreador_linhagem
        FROM `gatos` g
        LEFT JOIN `matrizes` m ON g.matriz_id = m.id
        LEFT JOIN `padreadores` p ON g.padreador_id = p.id
        WHERE g.id = ?
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Gato não encontrado'
        ]);
        exit;
    }
    
    $gato = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Processar os dados para converter as tags de JSON para arrays
    if (isset($gato['tags_saude'])) {
        $gato['tags_saude'] = json_decode($gato['tags_saude'], true) ?: [];
    } else {
        $gato['tags_saude'] = [];
    }
    
    if (isset($gato['tags_personalidade'])) {
        $gato['tags_personalidade'] = json_decode($gato['tags_personalidade'], true) ?: [];
    } else {
        $gato['tags_personalidade'] = [];
    }
    
    // Retornar detalhes do gato
    echo json_encode([
        'success' => true,
        'gato' => $gato
    ]);
    
} catch (PDOException $e) {
    // Erro de conexão com o banco de dados
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar gato: ' . $e->getMessage()
    ]);
}
?> 