<?php
require_once 'config.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = $_POST['nome'] ?? '';
        $usuario = $_POST['usuario'] ?? '';
        $senha = $_POST['senha'] ?? '';
        
        // Gera o hash MD5 da senha
        $hash = md5($senha);
        
        // Insere o novo usuário
        $stmt = $conn->prepare("INSERT INTO user (name, user, password, type) VALUES (?, ?, ?, 'admin')");
        if ($stmt->execute([$nome, $usuario, $hash])) {
            $mensagem = "<div style='color: green; margin: 10px 0;'>✅ Admin cadastrado com sucesso!</div>";
            $mensagem .= "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0;'>";
            $mensagem .= "Nome: " . htmlspecialchars($nome) . "<br>";
            $mensagem .= "Usuário: " . htmlspecialchars($usuario) . "<br>";
            $mensagem .= "Hash MD5 gerado: " . $hash . "<br>";
            $mensagem .= "</div>";
        }
    } catch(PDOException $e) {
        $mensagem = "<div style='color: red; margin: 10px 0;'>❌ Erro ao cadastrar: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Cadastro de Administrador</title>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #45a049;
        }
        .info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Cadastro de Administrador</h1>
    
    <?php echo $mensagem; ?>

    <form method="post">
        <div class="form-group">
            <label>Nome:</label>
            <input type="text" name="nome" required>
        </div>
        
        <div class="form-group">
            <label>Usuário:</label>
            <input type="text" name="usuario" required>
        </div>
        
        <div class="form-group">
            <label>Senha:</label>
            <input type="password" name="senha" required>
        </div>
        
        <button type="submit">Cadastrar Admin</button>
    </form>

    <div class="info">
        <h3>Informações do Banco:</h3>
        <p>
            Host: <?php echo DB_HOST; ?><br>
            Banco: <?php echo DB_NAME; ?><br>
            Usuário: <?php echo DB_USER; ?>
        </p>
    </div>
</body>
</html> 