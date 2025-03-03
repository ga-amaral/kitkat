<?php
require_once 'config.php';

try {
    // Testa a conexão primeiro
    echo "<h2>Teste de Conexão</h2>";
    if ($conn) {
        echo "✅ Conexão com o banco estabelecida com sucesso!<br><br>";
    }

    // Busca todos os usuários (sem mostrar as senhas)
    echo "<h2>Usuários Cadastrados:</h2>";
    $stmt = $conn->query("SELECT id, name, user, type FROM user");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($usuarios) > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Usuário</th><th>Tipo</th></tr>";
        foreach ($usuarios as $usuario) {
            echo "<tr>";
            echo "<td style='padding: 5px;'>" . $usuario['id'] . "</td>";
            echo "<td style='padding: 5px;'>" . $usuario['name'] . "</td>";
            echo "<td style='padding: 5px;'>" . $usuario['user'] . "</td>";
            echo "<td style='padding: 5px;'>" . $usuario['type'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ Nenhum usuário encontrado no banco de dados.<br>";
    }

    // Verifica a estrutura da tabela
    echo "<h2>Estrutura da Tabela:</h2>";
    $stmt = $conn->query("DESCRIBE user");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td style='padding: 5px;'>" . $coluna['Field'] . "</td>";
        echo "<td style='padding: 5px;'>" . $coluna['Type'] . "</td>";
        echo "<td style='padding: 5px;'>" . $coluna['Null'] . "</td>";
        echo "<td style='padding: 5px;'>" . $coluna['Key'] . "</td>";
        echo "<td style='padding: 5px;'>" . ($coluna['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Verifica se a coluna last_login existe
    $temLastLogin = false;
    foreach ($colunas as $coluna) {
        if ($coluna['Field'] === 'last_login') {
            $temLastLogin = true;
            break;
        }
    }

    if (!$temLastLogin) {
        echo "<br>⚠️ A coluna 'last_login' não existe. Adicionando...<br>";
        $conn->exec("ALTER TABLE user ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL");
        echo "✅ Coluna 'last_login' adicionada com sucesso!<br>";
    }

    // Informações de debug
    echo "<h2>Informações de Debug:</h2>";
    echo "📌 Servidor MySQL: " . DB_HOST . "<br>";
    echo "📌 Banco de dados: " . DB_NAME . "<br>";
    echo "📌 Usuário MySQL: " . DB_USER . "<br>";
    echo "📌 Charset: " . $conn->query('SELECT @@character_set_database')->fetchColumn() . "<br>";
    echo "📌 Collation: " . $conn->query('SELECT @@collation_database')->fetchColumn() . "<br>";

} catch(PDOException $e) {
    echo "<h2>❌ Erro Encontrado:</h2>";
    echo "Mensagem: " . $e->getMessage() . "<br>";
    echo "Código: " . $e->getCode() . "<br>";
    echo "Arquivo: " . $e->getFile() . "<br>";
    echo "Linha: " . $e->getLine() . "<br>";
}
?> 