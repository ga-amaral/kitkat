<?php
// Incluir arquivo de configuração
require_once 'config.php';

// Conexão com o banco de dados
try {
    global $config;
    
    $host = $config['db']['host'];
    $dbname = $config['db']['dbname'];
    $username = $config['db']['username'];
    $password = $config['db']['password'];
    
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Criar o banco de dados se não existir
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");
    $pdo->exec("USE `$dbname`");
    
    echo "<h2>Criando tabelas no banco de dados...</h2>";
    
    // Criar tabela de usuários
    $sql = "CREATE TABLE IF NOT EXISTS `usuarios` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `user_name` VARCHAR(50) NOT NULL UNIQUE,
        `password` VARCHAR(32) NOT NULL,
        `type` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    
    $pdo->exec($sql);
    echo "<p>Tabela 'usuarios' criada com sucesso!</p>";
    
    // Criar tabela de matrizes
    $sql = "CREATE TABLE IF NOT EXISTS `matrizes` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `nome` VARCHAR(100) NOT NULL,
        `data_nascimento` DATE NOT NULL,
        `raca` VARCHAR(100) NOT NULL,
        `ninhadas` INT(11) NOT NULL DEFAULT 0,
        `caracteristicas` TEXT NOT NULL,
        `foto` VARCHAR(255) NOT NULL,
        `linhagem` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    
    $pdo->exec($sql);
    echo "<p>Tabela 'matrizes' criada com sucesso!</p>";
    
    // Criar tabela de padreadores
    $sql = "CREATE TABLE IF NOT EXISTS `padreadores` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `nome` VARCHAR(100) NOT NULL,
        `data_nascimento` DATE NOT NULL,
        `raca` VARCHAR(100) NOT NULL,
        `caracteristicas` TEXT NOT NULL,
        `foto` VARCHAR(255) NOT NULL,
        `linhagem` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    
    $pdo->exec($sql);
    echo "<p>Tabela 'padreadores' criada com sucesso!</p>";
    
    // Criar tabela de gatos
    $sql = "CREATE TABLE IF NOT EXISTS `gatos` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    
    $pdo->exec($sql);
    echo "<p>Tabela 'gatos' criada com sucesso!</p>";
    
    // Inserir usuário admin padrão (senha: admin123)
    $admin_name = "Administrador";
    $admin_user = "admin";
    $admin_password = criptografarSenha("admin123"); // Usando função de criptografia
    
    // Verificar se já existe um usuário admin
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `usuarios` WHERE `user_name` = ?");
    $stmt->execute([$admin_user]);
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO `usuarios` (`name`, `user_name`, `password`, `type`) VALUES (?, ?, ?, 'admin')");
        $stmt->execute([$admin_name, $admin_user, $admin_password]);
        echo "<p>Usuário administrador padrão criado com sucesso!</p>";
    } else {
        echo "<p>Usuário administrador já existe.</p>";
    }
    
    echo "<h2>Configuração do banco de dados concluída com sucesso!</h2>";
    
} catch (PDOException $e) {
    die("<h2>Erro ao configurar o banco de dados:</h2><p>" . $e->getMessage() . "</p>");
}
?> 