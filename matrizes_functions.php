<?php
require_once 'config.php';

// Função para criar nova matriz
function criarMatriz($dados) {
    global $conn;
    try {
        $sql = "INSERT INTO matrizes (
            name, born_date, raca, ninhadas, children_caracteristic, img_url, origem
        ) VALUES (
            :name, :born_date, :raca, :ninhadas, :children_caracteristic, :img_url, :origem
        )";

        $stmt = $conn->prepare($sql);
        return $stmt->execute([
            ':name' => $dados['name'],
            ':born_date' => $dados['born_date'],
            ':raca' => $dados['raca'],
            ':ninhadas' => $dados['ninhadas'] ?? 0,
            ':children_caracteristic' => $dados['children_caracteristic'] ?? null,
            ':img_url' => $dados['img_url'] ?? null,
            ':origem' => $dados['origem'] ?? null
        ]);
    } catch(PDOException $e) {
        error_log("Erro ao criar matriz: " . $e->getMessage());
        return false;
    }
}

// Função para listar todas as matrizes
function listarMatrizes() {
    global $conn;
    try {
        $sql = "SELECT m.*, 
                (SELECT COUNT(*) FROM filhotes f WHERE f.matriz_id = m.id) as total_filhotes,
                (SELECT COUNT(DISTINCT padreador_id) FROM filhotes f WHERE f.matriz_id = m.id) as total_padreadores
                FROM matrizes m 
                ORDER BY m.name ASC";
        
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Erro ao listar matrizes: " . $e->getMessage());
        return [];
    }
}

// Função para buscar uma matriz específica
function buscarMatriz($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT m.*, 
            (SELECT COUNT(*) FROM filhotes f WHERE f.matriz_id = m.id) as total_filhotes,
            (SELECT COUNT(DISTINCT padreador_id) FROM filhotes f WHERE f.matriz_id = m.id) as total_padreadores
            FROM matrizes m 
            WHERE m.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Erro ao buscar matriz: " . $e->getMessage());
        return null;
    }
}

// Função para buscar filhotes de uma matriz
function buscarFilhotesMatriz($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT f.*, p.name as padreador_nome
            FROM filhotes f 
            LEFT JOIN padreadores p ON f.padreador_id = p.id
            WHERE f.matriz_id = ?
            ORDER BY f.born_date DESC
        ");
        $stmt->execute([$id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Erro ao buscar filhotes da matriz: " . $e->getMessage());
        return [];
    }
}

// Função para atualizar matriz
function atualizarMatriz($id, $dados) {
    global $conn;
    try {
        $campos = [];
        $params = [];

        // Lista de campos possíveis para atualização
        $camposPossiveis = [
            'name', 'born_date', 'raca', 'ninhadas', 'children_caracteristic', 'img_url', 'origem'
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

        $sql = "UPDATE matrizes SET " . implode(', ', $campos) . " WHERE id = :id";
        $params[':id'] = $id;

        $stmt = $conn->prepare($sql);
        return $stmt->execute($params);
    } catch(PDOException $e) {
        error_log("Erro ao atualizar matriz: " . $e->getMessage());
        return false;
    }
}

// Função para deletar matriz
function deletarMatriz($id) {
    global $conn;
    try {
        // Primeiro, verifica se existem filhotes associados
        $stmt = $conn->prepare("SELECT COUNT(*) FROM filhotes WHERE matriz_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            // Se existem filhotes, apenas define matriz_id como NULL
            $stmt = $conn->prepare("UPDATE filhotes SET matriz_id = NULL WHERE matriz_id = ?");
            $stmt->execute([$id]);
        }

        // Agora pode deletar a matriz com segurança
        $stmt = $conn->prepare("DELETE FROM matrizes WHERE id = ?");
        return $stmt->execute([$id]);
    } catch(PDOException $e) {
        error_log("Erro ao deletar matriz: " . $e->getMessage());
        return false;
    }
}

// Função para incrementar ninhada
function incrementarNinhada($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("UPDATE matrizes SET ninhadas = ninhadas + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    } catch(PDOException $e) {
        error_log("Erro ao incrementar ninhada: " . $e->getMessage());
        return false;
    }
}

// Função para buscar estatísticas da matriz
function buscarEstatisticasMatriz($id) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT 
                COUNT(*) as total_filhotes,
                COUNT(DISTINCT padreador_id) as total_padreadores,
                COUNT(CASE WHEN status = 'vendido' THEN 1 END) as filhotes_vendidos,
                COUNT(CASE WHEN status = 'disponivel' THEN 1 END) as filhotes_disponiveis,
                m.ninhadas
            FROM filhotes f
            RIGHT JOIN matrizes m ON m.id = f.matriz_id
            WHERE m.id = ?
            GROUP BY m.id, m.ninhadas
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Erro ao buscar estatísticas da matriz: " . $e->getMessage());
        return null;
    }
}
?> 