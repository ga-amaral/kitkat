<?php
require_once 'config.php';

try {
    echo "<h2>🔍 Debug de Login</h2>";

    // 1. Verifica a conexão
    echo "<h3>1. Teste de Conexão</h3>";
    if ($conn) {
        echo "✅ Conexão com o banco estabelecida<br><br>";
    }

    // 2. Verifica se a tabela existe
    echo "<h3>2. Verificando Tabela</h3>";
    $stmt = $conn->query("SHOW TABLES LIKE 'user'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela 'user' encontrada<br><br>";
    } else {
        echo "❌ Tabela 'user' não encontrada<br><br>";
        die("Erro: Tabela não existe!");
    }

    // 3. Mostra todos os usuários (sem senha)
    echo "<h3>3. Usuários no Banco</h3>";
    $stmt = $conn->query("SELECT id, name, user, type FROM user");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($usuarios) > 0) {
        echo "✅ " . count($usuarios) . " usuário(s) encontrado(s):<br>";
        foreach ($usuarios as $u) {
            echo "ID: {$u['id']}, Nome: {$u['name']}, Usuário: {$u['user']}, Tipo: {$u['type']}<br>";
        }
    } else {
        echo "❌ Nenhum usuário encontrado!<br>";
    }
    echo "<br>";

    // 4. Testa login com usuário admin
    echo "<h3>4. Teste de Login (admin)</h3>";
    $stmt = $conn->prepare("SELECT * FROM user WHERE user = ?");
    $stmt->execute(['admin']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo "✅ Usuário 'admin' encontrado<br>";
        echo "- Hash da senha atual: " . $admin['password'] . "<br>";
        
        // Testa a senha 'amaral123'
        $senha_teste = 'amaral123';
        $hash_teste = md5($senha_teste);
        
        echo "- Senha testada: " . $senha_teste . "<br>";
        echo "- Hash MD5 gerado: " . $hash_teste . "<br>";
        
        if ($hash_teste === trim($admin['password'])) {
            echo "✅ Senha está correta!<br>";
            echo "- Hash do banco e hash gerado são iguais<br>";
        } else {
            echo "❌ Senha não confere<br>";
            echo "- Comprimento do hash no banco: " . strlen($admin['password']) . "<br>";
            echo "- Comprimento do hash gerado: " . strlen($hash_teste) . "<br>";
            echo "- Comparação exata: " . ($hash_teste === $admin['password'] ? 'true' : 'false') . "<br>";
            
            // Mostra os hashes em hexadecimal para comparação
            echo "- Hash do banco (hex): " . bin2hex($admin['password']) . "<br>";
            echo "- Hash gerado (hex): " . bin2hex($hash_teste) . "<br>";
        }
    } else {
        echo "❌ Usuário 'admin' não encontrado<br>";
    }
    echo "<br>";

    // 5. Formulário para testar outras senhas
    echo "<h3>5. Teste Manual de Senha</h3>";
    echo "<form method='post'>";
    echo "<input type='text' name='senha_teste' placeholder='Digite uma senha para testar'>";
    echo "<input type='submit' value='Testar Senha'>";
    echo "</form><br>";

    if (isset($_POST['senha_teste'])) {
        $senha_manual = $_POST['senha_teste'];
        $hash_manual = md5($senha_manual);
        echo "Resultado do teste manual:<br>";
        echo "- Senha digitada: " . $senha_manual . "<br>";
        echo "- Hash MD5 gerado: " . $hash_manual . "<br>";
        echo "- Hash é igual ao do banco: " . ($hash_manual === trim($admin['password']) ? 'SIM' : 'NÃO') . "<br>";
    }

    // 6. Criar novo usuário admin se não existir
    echo "<h3>6. Criando Usuário Admin</h3>";
    if (!$admin) {
        $senha = 'amaral123';
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO user (name, user, password, type) VALUES (?, ?, ?, ?)");
        if ($stmt->execute(['Administrador', 'admin', $hash, 'admin'])) {
            echo "✅ Novo usuário admin criado com sucesso!<br>";
            echo "Usuário: admin<br>";
            echo "Senha: amaral123<br>";
        }
    } else {
        echo "ℹ️ Usuário admin já existe<br>";
    }

    // 7. Verifica a estrutura da tabela
    echo "<h3>7. Estrutura da Tabela</h3>";
    $stmt = $conn->query("DESCRIBE user");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($colunas as $col) {
        echo "{$col['Field']} - {$col['Type']} - {$col['Null']} - {$col['Key']}<br>";
    }

} catch (Exception $e) {
    echo "<h3>❌ Erro:</h3>";
    echo $e->getMessage();
}

// Formulário de teste
echo "<h3>8. Teste de Login Manual</h3>";
echo "<form method='post' action='login_process.php' style='margin: 20px; padding: 20px; border: 1px solid #ccc;'>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>Usuário: <input type='text' name='user' value='admin'></label><br>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label>Senha: <input type='password' name='password' value='amaral123'></label><br>";
echo "</div>";
echo "<input type='submit' value='Testar Login'>";
echo "</form>";
?> 