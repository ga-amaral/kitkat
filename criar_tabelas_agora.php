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

// Array com as definições das tabelas
$tabelas = [
    'matrizes' => "
        CREATE TABLE matrizes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            data_nascimento DATE NOT NULL,
            raca VARCHAR(100) NOT NULL,
            ninhadas INT DEFAULT 0,
            caracteristicas_filhotes TEXT,
            foto VARCHAR(255) NOT NULL,
            linhagem TEXT
        )
    ",
    'matrizes_premios' => "
        CREATE TABLE matrizes_premios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            matriz_id INT NOT NULL,
            premio VARCHAR(255) NOT NULL,
            FOREIGN KEY (matriz_id) REFERENCES matrizes(id) ON DELETE CASCADE
        )
    ",
    'padreadores' => "
        CREATE TABLE padreadores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            data_nascimento DATE NOT NULL,
            raca VARCHAR(100) NOT NULL,
            caracteristicas_filhotes TEXT,
            foto VARCHAR(255) NOT NULL,
            linhagem TEXT
        )
    ",
    'padreadores_premios' => "
        CREATE TABLE padreadores_premios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            padreador_id INT NOT NULL,
            premio VARCHAR(255) NOT NULL,
            FOREIGN KEY (padreador_id) REFERENCES padreadores(id) ON DELETE CASCADE
        )
    ",
    'gatos' => "
        CREATE TABLE gatos (
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
        )
    ",
    'gatos_tags_saude' => "
        CREATE TABLE gatos_tags_saude (
            id INT AUTO_INCREMENT PRIMARY KEY,
            gato_id INT NOT NULL,
            tag VARCHAR(50) NOT NULL,
            FOREIGN KEY (gato_id) REFERENCES gatos(id) ON DELETE CASCADE
        )
    ",
    'gatos_tags_personalidade' => "
        CREATE TABLE gatos_tags_personalidade (
            id INT AUTO_INCREMENT PRIMARY KEY,
            gato_id INT NOT NULL,
            tag VARCHAR(50) NOT NULL,
            FOREIGN KEY (gato_id) REFERENCES gatos(id) ON DELETE CASCADE
        )
    "
];

// Criar as tabelas se não existirem
echo "<h1>Criação de Tabelas</h1>";

foreach ($tabelas as $nome => $sql) {
    if (!tabelaExiste($conn, $nome)) {
        try {
            $conn->exec($sql);
            echo "Tabela <strong>{$nome}</strong> criada com sucesso!<br>";
        } catch (PDOException $e) {
            echo "Erro ao criar tabela {$nome}: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "Tabela <strong>{$nome}</strong> já existe.<br>";
    }
}

echo "<p>Processo concluído!</p>";
?> 