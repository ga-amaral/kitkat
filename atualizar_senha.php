<?php
require_once 'config.php';

try {
    // Senha que queremos definir
    $senha = 'amaral123';
    
    // Gera o hash da senha
    $hash = password_hash($senha, PASSWORD_DEFAULT);
    
    // Atualiza a senha do usuário admin
    $stmt = $conn->prepare("UPDATE user SET password = ? WHERE user = 'admin'");
    if ($stmt->execute([$hash])) {
        echo "✅ Senha atualizada com sucesso!<br>";
        echo "Usuário: admin<br>";
        echo "Senha: amaral123<br>";
    } else {
        echo "❌ Erro ao atualizar a senha.<br>";
    }

} catch(PDOException $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?> 