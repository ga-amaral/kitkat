<?php
require_once 'config.php';

try {
    // Lê o arquivo SQL
    $sql = file_get_contents('db.sql');

    // Divide o arquivo em comandos individuais
    $comandos = array_filter(array_map('trim', explode(';', $sql)));

    // Executa cada comando
    foreach ($comandos as $comando) {
        if (!empty($comando)) {
            $conn->exec($comando);
        }
    }

    echo "Banco de dados importado com sucesso!<br>";
    echo "Tabelas criadas:<br>";
    
    // Lista as tabelas criadas
    $stmt = $conn->query("SHOW TABLES");
    while ($tabela = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "- " . $tabela[0] . "<br>";
    }

} catch(PDOException $e) {
    die("Erro ao importar banco de dados: " . $e->getMessage());
}
?> 