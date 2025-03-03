-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS gatilzaidan;
USE gatilzaidan;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    user VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    type VARCHAR(20) NOT NULL
);

-- Inserir usuário administrador padrão (senha: amaral123)
INSERT INTO user (name, user, password, type) VALUES 
('Administrador', 'admin', 'Condr1cthy3S', 'admin');

-- Tabela de matrizes
CREATE TABLE IF NOT EXISTS matrizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    born_date DATE NOT NULL,
    raca VARCHAR(100) NOT NULL,
    ninhadas INT DEFAULT 0,
    children_caracteristic TEXT,
    img_url VARCHAR(255),
    origem VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de padreadores
CREATE TABLE IF NOT EXISTS padreadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    born_date DATE NOT NULL,
    raca VARCHAR(100) NOT NULL,
    children_caracteristic TEXT,
    img_url VARCHAR(255),
    origem VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de filhotes
CREATE TABLE IF NOT EXISTS filhotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    born_date DATE NOT NULL,
    color VARCHAR(50) NOT NULL,
    description TEXT,
    img_url VARCHAR(255),
    origem VARCHAR(100),
    status ENUM('disponivel', 'reservado', 'vendido') DEFAULT 'disponivel',
    matriz_id INT,
    padreador_id INT,
    vacinado BOOLEAN DEFAULT FALSE,
    vermifugado BOOLEAN DEFAULT FALSE,
    microchipado BOOLEAN DEFAULT FALSE,
    castrado BOOLEAN DEFAULT FALSE,
    docil BOOLEAN DEFAULT FALSE,
    brincalhao BOOLEAN DEFAULT FALSE,
    sociavel BOOLEAN DEFAULT FALSE,
    calmo BOOLEAN DEFAULT FALSE,
    independente BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (matriz_id) REFERENCES matrizes(id) ON DELETE SET NULL,
    FOREIGN KEY (padreador_id) REFERENCES padreadores(id) ON DELETE SET NULL
); 