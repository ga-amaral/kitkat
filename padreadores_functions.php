<?php
require_once 'config.php';

// Função para criar novo padreador
function criarPadreador($dados) {
    global $conn;
    try {
        $sql = "INSERT INTO padreadores (
            name, born_date, raca, children_caracteristic, img_url, origem
        ) VALUES (
            :name, :born_date, :raca, :children_caracteristic, :img_url, :origem
        )";

        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':name' => $dados['name'],
            ':born_date' => $dados['born_date'],
            ':raca' => $dados['raca'],
            ':children_caracteristic' => $dados['children_caracteristic'] ?? null,
            ':img_url' => $dados['img_url'] ?? null,
            ':origem' => $dados['origem'] ?? null
        ]);
    } catch(PDOException $e) {
        error_log("Erro ao criar padreador: " . $e->getMessage());
        return false;
    }
}

// Função para listar todos os padreadores
function listarPadreadores() {
    global $conn;
    try {
        $sql = "SELECT p.*, 
                (SELECT COUNT(*) FROM filhotes f WHERE f.padreador_id = p.id) as total_filhotes
                FROM padreadores p 
                ORDER BY p.name ASC";
        
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Erro ao listar padreadores: " . $e->getMessage());
        return [];
    }
}

// Função para buscar um padreador específico
function buscarPadreador($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT p.*, 
            (SELECT COUNT(*) FROM filhotes f WHERE f.padreador_id = p.id) as total_filhotes
            FROM padreadores p 
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Erro ao buscar padreador: " . $e->getMessage());
        return null;
    }
}

// Função para buscar filhotes de um padreador
function buscarFilhotesPadreador($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT f.*, m.name as matriz_nome
            FROM filhotes f 
            LEFT JOIN matrizes m ON f.matriz_id = m.id
            WHERE f.padreador_id = ?
            ORDER BY f.born_date DESC
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Erro ao buscar filhotes do padreador: " . $e->getMessage());
        return [];
    }
}

// Função para atualizar padreador
function atualizarPadreador($id, $dados) {
    global $conn;
    try {
        $campos = [];
        $params = [];

        // Lista de campos possíveis para atualização
        $camposPossiveis = [
            'name', 'born_date', 'raca', 'children_caracteristic', 'img_url', 'origem'
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

        $sql = "UPDATE padreadores SET " . implode(', ', $campos) . " WHERE id = :id";
        $params[':id'] = $id;

        $stmt = $conn->prepare($sql);
        return $stmt->execute($params);
    } catch(PDOException $e) {
        error_log("Erro ao atualizar padreador: " . $e->getMessage());
        return false;
    }
}

// Função para deletar padreador
function deletarPadreador($id) {
    global $conn;
    try {
        // Primeiro, verifica se existem filhotes associados
        $stmt = $conn->prepare("SELECT COUNT(*) FROM filhotes WHERE padreador_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            // Se existem filhotes, apenas define padreador_id como NULL
            $stmt = $conn->prepare("UPDATE filhotes SET padreador_id = NULL WHERE padreador_id = ?");
            $stmt->execute([$id]);
        }

        // Agora pode deletar o padreador com segurança
        $stmt = $conn->prepare("DELETE FROM padreadores WHERE id = ?");
        return $stmt->execute([$id]);
    } catch(PDOException $e) {
        error_log("Erro ao deletar padreador: " . $e->getMessage());
        return false;
    }
}

// Função para buscar estatísticas do padreador
function buscarEstatisticasPadreador($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT 
                COUNT(*) as total_filhotes,
                COUNT(DISTINCT matriz_id) as total_matrizes,
                COUNT(CASE WHEN status = 'vendido' THEN 1 END) as filhotes_vendidos,
                COUNT(CASE WHEN status = 'disponivel' THEN 1 END) as filhotes_disponiveis
            FROM filhotes 
            WHERE padreador_id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Erro ao buscar estatísticas do padreador: " . $e->getMessage());
        return null;
    }
}
?> 