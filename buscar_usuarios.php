<?php
// Ativa a exibição de erros para debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Garante que nenhuma saída foi enviada antes
ob_start();

try {
    session_start();
    require_once 'config.php';

    // Headers necessários
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Accept');
    header('Access-Control-Allow-Credentials: true');

    // Log para debug
    error_log("Iniciando busca de usuários");
    error_log("Sessão: " . print_r($_SESSION, true));

    // Responde à requisição OPTIONS do CORS
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        echo json_encode(['success' => true]);
        exit;
    }

    // Verifica se o usuário está logado e é admin
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Usuário não está logado");
    }

    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
        throw new Exception("Acesso permitido apenas para administradores");
    }

    // Verifica se a conexão está funcionando
    if (!$conn) {
        throw new Exception("Erro na conexão com o banco de dados");
    }

    error_log("Preparando query para buscar usuários");
    
    // Busca todos os usuários
    $stmt = $conn->prepare("
        SELECT id, name, user, type 
        FROM user 
        ORDER BY name
    ");

    error_log("Executando query");
    if (!$stmt->execute()) {
        throw new Exception("Erro ao executar a query: " . implode(" ", $stmt->errorInfo()));
    }
    
    error_log("Buscando resultados");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($usuarios === false) {
        throw new Exception("Erro ao buscar dados dos usuários");
    }
    
    error_log("Total de usuários encontrados: " . count($usuarios));

    // Limpa qualquer saída anterior
    ob_clean();

    echo json_encode([
        'success' => true,
        'usuarios' => $usuarios
    ]);

} catch (PDOException $e) {
    error_log("Erro PDO ao buscar usuários: " . $e->getMessage());
    http_response_code(500);
    
    // Limpa qualquer saída anterior
    ob_clean();
    
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar usuários no banco de dados',
        'error' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Erro ao buscar usuários: " . $e->getMessage());
    http_response_code(500);
    
    // Limpa qualquer saída anterior
    ob_clean();
    
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar usuários',
        'error' => $e->getMessage()
    ]);
}

// Garante que a saída foi enviada e encerra o buffer
ob_end_flush();
?> 