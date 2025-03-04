<?php
// Incluir arquivo de configuração
require_once 'config.php';

// Função para verificar se uma tabela existe
function tabelaExiste($pdo, $tabela) {
    try {
        $result = $pdo->query("SELECT 1 FROM `$tabela` LIMIT 1");
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Função para contar registros em uma tabela
function contarRegistros($pdo, $tabela) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM `$tabela`");
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

// Conexão com o banco de dados
try {
    global $config;
    
    $host = $config['db']['host'];
    $dbname = $config['db']['dbname'];
    $username = $config['db']['username'];
    $password = $config['db']['password'];
    
    echo "<h2>Verificando conexão com o banco de dados...</h2>";
    
    // Tentar conectar ao servidor MySQL
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✅ Conexão com o servidor MySQL estabelecida com sucesso.</p>";
    
    // Verificar se o banco de dados existe
    $stmt = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    $dbExists = (bool) $stmt->fetchColumn();
    
    if ($dbExists) {
        echo "<p>✅ Banco de dados '$dbname' existe.</p>";
        
        // Conectar ao banco de dados específico
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Verificar tabelas
        echo "<h3>Verificando tabelas:</h3>";
        
        // Tabela de usuários
        if (tabelaExiste($pdo, 'usuarios')) {
            $count = contarRegistros($pdo, 'usuarios');
            echo "<p>✅ Tabela 'usuarios' existe. Registros: $count</p>";
        } else {
            echo "<p>❌ Tabela 'usuarios' não existe.</p>";
        }
        
        // Tabela de matrizes
        if (tabelaExiste($pdo, 'matrizes')) {
            $count = contarRegistros($pdo, 'matrizes');
            echo "<p>✅ Tabela 'matrizes' existe. Registros: $count</p>";
        } else {
            echo "<p>❌ Tabela 'matrizes' não existe.</p>";
        }
        
        // Tabela de padreadores
        if (tabelaExiste($pdo, 'padreadores')) {
            $count = contarRegistros($pdo, 'padreadores');
            echo "<p>✅ Tabela 'padreadores' existe. Registros: $count</p>";
        } else {
            echo "<p>❌ Tabela 'padreadores' não existe.</p>";
        }
        
        // Tabela de gatos
        if (tabelaExiste($pdo, 'gatos')) {
            $count = contarRegistros($pdo, 'gatos');
            echo "<p>✅ Tabela 'gatos' existe. Registros: $count</p>";
        } else {
            echo "<p>❌ Tabela 'gatos' não existe.</p>";
        }
        
    } else {
        echo "<p>❌ Banco de dados '$dbname' não existe.</p>";
        echo "<p>Execute o script 'executar_sql.php' para criar o banco de dados e as tabelas.</p>";
    }
    
} catch (PDOException $e) {
    echo "<h2>❌ Erro ao conectar ao banco de dados:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    
    if (strpos($e->getMessage(), "Access denied") !== false) {
        echo "<p>Verifique se as credenciais de acesso ao banco de dados estão corretas.</p>";
    }
}
?> 