<?php
require_once 'config.php';

try {
    // Primeiro remove o usuário admin se existir
    $stmt = $conn->prepare("DELETE FROM user WHERE user = ?");
    $stmt->execute(['admin']);

    // Cria o hash da senha
    $senha = 'amaral123';
    $hash = password_hash($senha, PASSWORD_DEFAULT);

    // Insere o novo usuário admin
    $stmt = $conn->prepare("INSERT INTO user (name, user, password, type) VALUES (?, ?, ?, ?)");
    if ($stmt->execute(['Administrador', 'admin', $hash, 'admin'])) {
        echo "✅ Usuário admin recriado com sucesso!<br>";
        echo "Usuário: admin<br>";
        echo "Senha: amaral123<br>";
        
        // Verifica se a senha está correta
        $stmt = $conn->prepare("SELECT password FROM user WHERE user = ?");
        $stmt->execute(['admin']);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify('amaral123', $admin['password'])) {
            echo "✅ Verificação de senha: OK<br>";
        } else {
            echo "❌ Verificação de senha: Falhou<br>";
        }
    } else {
        echo "❌ Erro ao recriar usuário admin<br>";
    }

} catch(PDOException $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?> 