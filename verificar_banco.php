<?php
require_once 'config.php';

// Habilitar exibição de erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Função para verificar se uma tabela existe
function tabelaExiste($conn, $tabela) {
    try {
        $stmt = $conn->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$tabela]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        echo "Erro ao verificar tabela {$tabela}: " . $e->getMessage() . "<br>";
        return false;
    }
}

// Função para contar registros em uma tabela
function contarRegistros($conn, $tabela) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM {$tabela}");
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        echo "Erro ao contar registros da tabela {$tabela}: " . $e->getMessage() . "<br>";
        return 0;
    }
}

// Verificar conexão com o banco
echo "<h1>Status do Banco de Dados</h1>";

try {
    $conn->query("SELECT 1");
    echo "<p style='color: green;'>✓ Conexão com o banco de dados estabelecida com sucesso!</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Erro na conexão com o banco de dados: " . $e->getMessage() . "</p>";
    exit;
}

// Verificar tabelas
$tabelas = [
    'matrizes',
    'matrizes_premios',
    'padreadores',
    'padreadores_premios',
    'gatos',
    'gatos_tags_saude',
    'gatos_tags_personalidade'
];

echo "<h2>Status das Tabelas</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Tabela</th><th>Status</th><th>Registros</th></tr>";

foreach ($tabelas as $tabela) {
    $existe = tabelaExiste($conn, $tabela);
    $registros = $existe ? contarRegistros($conn, $tabela) : 0;
    
    echo "<tr>";
    echo "<td>{$tabela}</td>";
    echo "<td style='color: " . ($existe ? "green" : "red") . ";'>" . ($existe ? "✓ Existe" : "✗ Não existe") . "</td>";
    echo "<td>{$registros}</td>";
    echo "</tr>";
}

echo "</table>";

// Verificar configurações do PHP
echo "<h2>Configurações do PHP</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Configuração</th><th>Valor</th></tr>";
echo "<tr><td>Versão do PHP</td><td>" . phpversion() . "</td></tr>";
echo "<tr><td>display_errors</td><td>" . ini_get('display_errors') . "</td></tr>";
echo "<tr><td>error_reporting</td><td>" . ini_get('error_reporting') . "</td></tr>";
echo "<tr><td>max_execution_time</td><td>" . ini_get('max_execution_time') . "</td></tr>";
echo "<tr><td>memory_limit</td><td>" . ini_get('memory_limit') . "</td></tr>";
echo "<tr><td>post_max_size</td><td>" . ini_get('post_max_size') . "</td></tr>";
echo "<tr><td>upload_max_filesize</td><td>" . ini_get('upload_max_filesize') . "</td></tr>";
echo "</table>";

// Verificar extensões do PHP
echo "<h2>Extensões do PHP</h2>";
$extensoes = ['pdo', 'pdo_mysql', 'json', 'session', 'mbstring'];
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Extensão</th><th>Status</th></tr>";

foreach ($extensoes as $extensao) {
    $carregada = extension_loaded($extensao);
    echo "<tr>";
    echo "<td>{$extensao}</td>";
    echo "<td style='color: " . ($carregada ? "green" : "red") . ";'>" . ($carregada ? "✓ Carregada" : "✗ Não carregada") . "</td>";
    echo "</tr>";
}

echo "</table>";
?> 