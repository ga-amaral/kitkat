<?php
session_start();
require_once 'config.php';

// Configurar cabeçalhos para JSON e CORS
header('Content-Type: application/json');
// Permitir acesso tanto do domínio de produção quanto de localhost para desenvolvimento
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization');
header('Access-Control-Allow-Credentials: true');

// Log para debug
error_log("Iniciando busca de todos os gatos");

// Responder à requisição OPTIONS do CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true]);
    exit;
}

// Verificar se o usuário está logado - comentado temporariamente para debug
/*
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    error_log("Acesso não autorizado: usuário não está logado");
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit;
}
*/

try {
    // Buscar todos os gatos com informações de matriz e padreador
    $query = "
        SELECT g.*, 
               m.nome as matriz_nome, 
               p.nome as padreador_nome
        FROM gatos g
        LEFT JOIN matrizes m ON g.matriz_id = m.id
        LEFT JOIN padreadores p ON g.padreador_id = p.id
        ORDER BY g.nome ASC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $gatos_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $gatos = [];
    
    foreach ($gatos_raw as $gato) {
        // Buscar tags de saúde
        $stmt_saude = $conn->prepare("SELECT tag FROM gatos_tags_saude WHERE gato_id = ?");
        $stmt_saude->execute([$gato['id']]);
        
        $tags_saude = [];
        while ($tag = $stmt_saude->fetch(PDO::FETCH_ASSOC)) {
            $tags_saude[] = $tag['tag'];
        }
        
        // Buscar tags de personalidade
        $stmt_personalidade = $conn->prepare("SELECT tag FROM gatos_tags_personalidade WHERE gato_id = ?");
        $stmt_personalidade->execute([$gato['id']]);
        
        $tags_personalidade = [];
        while ($tag = $stmt_personalidade->fetch(PDO::FETCH_ASSOC)) {
            $tags_personalidade[] = $tag['tag'];
        }
        
        // Adicionar as tags ao objeto do gato
        $gato['tags_saude'] = $tags_saude;
        $gato['tags_personalidade'] = $tags_personalidade;
        
        $gatos[] = $gato;
    }
    
    error_log("Gatos encontrados: " . count($gatos));
    echo json_encode(['success' => true, 'gatos' => $gatos]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar gatos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar gatos: ' . $e->getMessage()]);
}
?> 