<?php
require_once 'config.php';

// Habilitar exibição de erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// SQL para criar as tabelas
$sql = "
-- Tabela de Matrizes
CREATE TABLE IF NOT EXISTS matrizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    data_nascimento DATE NOT NULL,
    raca VARCHAR(100) NOT NULL,
    ninhadas INT DEFAULT 0,
    caracteristicas_filhotes TEXT,
    foto VARCHAR(255) NOT NULL,
    linhagem TEXT
);

-- Tabela de Prêmios das Matrizes
CREATE TABLE IF NOT EXISTS matrizes_premios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matriz_id INT NOT NULL,
    premio VARCHAR(255) NOT NULL,
    FOREIGN KEY (matriz_id) REFERENCES matrizes(id) ON DELETE CASCADE
);

-- Tabela de Padreadores
CREATE TABLE IF NOT EXISTS padreadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    data_nascimento DATE NOT NULL,
    raca VARCHAR(100) NOT NULL,
    caracteristicas_filhotes TEXT,
    foto VARCHAR(255) NOT NULL,
    linhagem TEXT
);

-- Tabela de Prêmios dos Padreadores
CREATE TABLE IF NOT EXISTS padreadores_premios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    padreador_id INT NOT NULL,
    premio VARCHAR(255) NOT NULL,
    FOREIGN KEY (padreador_id) REFERENCES padreadores(id) ON DELETE CASCADE
);

-- Tabela de Gatos
CREATE TABLE IF NOT EXISTS gatos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    data_nascimento DATE NOT NULL,
    cor VARCHAR(100) NOT NULL,
    descricao TEXT,
    foto VARCHAR(255) NOT NULL,
    status ENUM('disponivel', 'vendido') DEFAULT 'disponivel',
    data_venda DATE,
    matriz_id INT,
    padreador_id INT,
    FOREIGN KEY (matriz_id) REFERENCES matrizes(id) ON DELETE SET NULL,
    FOREIGN KEY (padreador_id) REFERENCES padreadores(id) ON DELETE SET NULL
);

-- Tabela de Tags de Saúde dos Gatos
CREATE TABLE IF NOT EXISTS gatos_tags_saude (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gato_id INT NOT NULL,
    tag VARCHAR(50) NOT NULL,
    FOREIGN KEY (gato_id) REFERENCES gatos(id) ON DELETE CASCADE
);

-- Tabela de Tags de Personalidade dos Gatos
CREATE TABLE IF NOT EXISTS gatos_tags_personalidade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gato_id INT NOT NULL,
    tag VARCHAR(50) NOT NULL,
    FOREIGN KEY (gato_id) REFERENCES gatos(id) ON DELETE CASCADE
);
";

echo "<h1>Executando SQL para criar tabelas</h1>";

try {
    // Executar o SQL
    $conn->exec($sql);
    echo "<p style='color: green;'>✓ SQL executado com sucesso! Todas as tabelas foram criadas ou já existiam.</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Erro ao executar SQL: " . $e->getMessage() . "</p>";
}

// Verificar se as tabelas foram criadas
$tabelas = [
    'matrizes',
    'matrizes_premios',
    'padreadores',
    'padreadores_premios',
    'gatos',
    'gatos_tags_saude',
    'gatos_tags_personalidade'
];

echo "<h2>Verificação das tabelas</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Tabela</th><th>Status</th></tr>";

foreach ($tabelas as $tabela) {
    $stmt = $conn->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$tabela]);
    $existe = $stmt->rowCount() > 0;
    
    echo "<tr>";
    echo "<td>{$tabela}</td>";
    echo "<td style='color: " . ($existe ? "green" : "red") . ";'>" . ($existe ? "✓ Existe" : "✗ Não existe") . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<p>Processo concluído! Agora você pode <a href='verificar_banco.php'>verificar o status do banco de dados</a> ou <a href='inserir_dados_exemplo.php'>inserir dados de exemplo</a>.</p>";
?> 