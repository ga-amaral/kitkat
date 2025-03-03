<?php
require_once 'config.php';

// Função para criar novo usuário
function criarUsuario($name, $user, $password, $type) {
    global $conn;
    try {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO user (name, user, password, type) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$name, $user, $hashed_password, $type]);
    } catch(PDOException $e) {
        error_log("Erro ao criar usuário: " . $e->getMessage());
        return false;
    }
}

// Função para listar todos os usuários
function listarUsuarios() {
    global $conn;
    try {
        $stmt = $conn->query("SELECT id, name, user, type FROM user");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Erro ao listar usuários: " . $e->getMessage());
        return [];
    }
}

// Função para buscar um usuário específico
function buscarUsuario($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT id, name, user, type FROM user WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Erro ao buscar usuário: " . $e->getMessage());
        return null;
    }
}

// Função para atualizar usuário
function atualizarUsuario($id, $name, $type, $password = null) {
    global $conn;
    try {
        if ($password) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE user SET name = ?, type = ?, password = ? WHERE id = ?");
            return $stmt->execute([$name, $type, $hashed_password, $id]);
        } else {
            $stmt = $conn->prepare("UPDATE user SET name = ?, type = ? WHERE id = ?");
            return $stmt->execute([$name, $type, $id]);
        }
    } catch(PDOException $e) {
        error_log("Erro ao atualizar usuário: " . $e->getMessage());
        return false;
    }
}

// Função para deletar usuário
function deletarUsuario($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
        return $stmt->execute([$id]);
    } catch(PDOException $e) {
        error_log("Erro ao deletar usuário: " . $e->getMessage());
        return false;
    }
}

// Função para verificar login
function verificarLogin($user, $password) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT id, user, password, name, type FROM user WHERE user = ?");
        $stmt->execute([$user]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['password'])) {
            return [
                'id' => $usuario['id'],
                'user' => $usuario['user'],
                'name' => $usuario['name'],
                'type' => $usuario['type']
            ];
        }
        return false;
    } catch(PDOException $e) {
        error_log("Erro ao verificar login: " . $e->getMessage());
        return false;
    }
}

// Função para alterar status do administrador (ativar/desativar)
function alterarStatusAdministrador($id, $ativo) {
    global $conn;
    try {
        $stmt = $conn->prepare("UPDATE administradores SET ativo = ? WHERE id = ?");
        return $stmt->execute([$ativo, $id]);
    } catch(PDOException $e) {
        error_log("Erro ao alterar status do administrador: " . $e->getMessage());
        return false;
    }
}
?> 