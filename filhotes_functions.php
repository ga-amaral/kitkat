<?php
require_once 'config.php';

// Função para criar novo filhote
function criarFilhote($dados) {
    global $conn;
    try {
        $sql = "INSERT INTO filhotes (
            name, born_date, color, description, img_url, origem, status,
            matriz_id, padreador_id, vacinado, vermifugado, microchipado,
            castrado, docil, brincalhao, sociavel, calmo, independente
        ) VALUES (
            :name, :born_date, :color, :description, :img_url, :origem, :status,
            :matriz_id, :padreador_id, :vacinado, :vermifugado, :microchipado,
            :castrado, :docil, :brincalhao, :sociavel, :calmo, :independente
        )";

        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':name' => $dados['name'],
            ':born_date' => $dados['born_date'],
            ':color' => $dados['color'],
            ':description' => $dados['description'],
            ':img_url' => $dados['img_url'],
            ':origem' => $dados['origem'],
            ':status' => $dados['status'] ?? 'disponivel',
            ':matriz_id' => $dados['matriz_id'] ?? null,
            ':padreador_id' => $dados['padreador_id'] ?? null,
            ':vacinado' => $dados['vacinado'] ?? false,
            ':vermifugado' => $dados['vermifugado'] ?? false,
            ':microchipado' => $dados['microchipado'] ?? false,
            ':castrado' => $dados['castrado'] ?? false,
            ':docil' => $dados['docil'] ?? false,
            ':brincalhao' => $dados['brincalhao'] ?? false,
            ':sociavel' => $dados['sociavel'] ?? false,
            ':calmo' => $dados['calmo'] ?? false,
            ':independente' => $dados['independente'] ?? false
        ]);
    } catch(PDOException $e) {
        error_log("Erro ao criar filhote: " . $e->getMessage());
        return false;
    }
}

// Função para listar todos os filhotes
function listarFilhotes($filtros = []) {
    global $conn;
    try {
        $sql = "SELECT f.*, 
                m.name as matriz_nome,
                p.name as padreador_nome
                FROM filhotes f 
                LEFT JOIN matrizes m ON f.matriz_id = m.id
                LEFT JOIN padreadores p ON f.padreador_id = p.id
                WHERE 1=1";
        
        $params = [];

        if (!empty($filtros['status'])) {
            $sql .= " AND f.status = :status";
            $params[':status'] = $filtros['status'];
        }

        if (!empty($filtros['matriz_id'])) {
            $sql .= " AND f.matriz_id = :matriz_id";
            $params[':matriz_id'] = $filtros['matriz_id'];
        }

        if (!empty($filtros['padreador_id'])) {
            $sql .= " AND f.padreador_id = :padreador_id";
            $params[':padreador_id'] = $filtros['padreador_id'];
        }

        $sql .= " ORDER BY f.created_at DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Erro ao listar filhotes: " . $e->getMessage());
        return [];
    }
}

// Função para buscar um filhote específico
function buscarFilhote($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT f.*, 
            m.name as matriz_nome,
            p.name as padreador_nome
            FROM filhotes f 
            LEFT JOIN matrizes m ON f.matriz_id = m.id
            LEFT JOIN padreadores p ON f.padreador_id = p.id
            WHERE f.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Erro ao buscar filhote: " . $e->getMessage());
        return null;
    }
}

// Função para atualizar filhote
function atualizarFilhote($id, $dados) {
    global $conn;
    try {
        $campos = [];
        $params = [];

        // Lista de campos possíveis para atualização
        $camposPossiveis = [
            'name', 'born_date', 'color', 'description', 'img_url', 'origem', 
            'status', 'matriz_id', 'padreador_id', 'vacinado', 'vermifugado', 
            'microchipado', 'castrado', 'docil', 'brincalhao', 'sociavel', 
            'calmo', 'independente'
        ];

        foreach ($camposPossiveis as $campo) {
            if (isset($dados[$campo])) {
                $campos[] = "$campo = :$campo";
                $params[":$campo"] = $dados[$campo];
            }
        }

        if (empty($campos)) {
            return false;
        }

        $sql = "UPDATE filhotes SET " . implode(', ', $campos) . " WHERE id = :id";
        $params[':id'] = $id;

        $stmt = $conn->prepare($sql);
        return $stmt->execute($params);
    } catch(PDOException $e) {
        error_log("Erro ao atualizar filhote: " . $e->getMessage());
        return false;
    }
}

// Função para deletar filhote
function deletarFilhote($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("DELETE FROM filhotes WHERE id = ?");
        return $stmt->execute([$id]);
    } catch(PDOException $e) {
        error_log("Erro ao deletar filhote: " . $e->getMessage());
        return false;
    }
}

// Função para atualizar status do filhote
function atualizarStatusFilhote($id, $status) {
    global $conn;
    try {
        $stmt = $conn->prepare("UPDATE filhotes SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    } catch(PDOException $e) {
        error_log("Erro ao atualizar status do filhote: " . $e->getMessage());
        return false;
    }
}
?> 