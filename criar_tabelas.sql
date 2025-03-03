-- Tabela de Matrizes
CREATE TABLE matrizes (
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
CREATE TABLE matrizes_premios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    matriz_id INT NOT NULL,
    premio VARCHAR(255) NOT NULL,
    FOREIGN KEY (matriz_id) REFERENCES matrizes(id) ON DELETE CASCADE
);

-- Tabela de Padreadores
CREATE TABLE padreadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    data_nascimento DATE NOT NULL,
    raca VARCHAR(100) NOT NULL,
    caracteristicas_filhotes TEXT,
    foto VARCHAR(255) NOT NULL,
    linhagem TEXT
);

-- Tabela de Prêmios dos Padreadores
CREATE TABLE padreadores_premios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    padreador_id INT NOT NULL,
    premio VARCHAR(255) NOT NULL,
    FOREIGN KEY (padreador_id) REFERENCES padreadores(id) ON DELETE CASCADE
);

-- Tabela de Gatos
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
);

-- Tabela de Tags de Saúde dos Gatos
CREATE TABLE gatos_tags_saude (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gato_id INT NOT NULL,
    tag VARCHAR(50) NOT NULL,
    FOREIGN KEY (gato_id) REFERENCES gatos(id) ON DELETE CASCADE
);

-- Tabela de Tags de Personalidade dos Gatos
CREATE TABLE gatos_tags_personalidade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gato_id INT NOT NULL,
    tag VARCHAR(50) NOT NULL,
    FOREIGN KEY (gato_id) REFERENCES gatos(id) ON DELETE CASCADE
); 