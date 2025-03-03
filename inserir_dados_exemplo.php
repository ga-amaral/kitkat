<?php
require_once 'config.php';

// Habilitar exibição de erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Função para verificar se uma tabela existe
function tabelaExiste($conn, $tabela) {
    try {
        $stmt = $conn->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$tabela]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        echo "Erro ao verificar tabela {$tabela}: " . $e->getMessage() . "<br>";
        return false;
    }
}

// Função para contar registros em uma tabela
function contarRegistros($conn, $tabela) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM {$tabela}");
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        echo "Erro ao contar registros da tabela {$tabela}: " . $e->getMessage() . "<br>";
        return 0;
    }
}

// Verificar se as tabelas existem
$tabelas = [
    'matrizes',
    'padreadores',
    'gatos',
    'gatos_tags_saude',
    'gatos_tags_personalidade'
];

foreach ($tabelas as $tabela) {
    if (!tabelaExiste($conn, $tabela)) {
        echo "<p style='color: red;'>A tabela {$tabela} não existe. Execute o script criar_tabelas_agora.php primeiro.</p>";
        exit;
    }
}

echo "<h1>Inserção de Dados de Exemplo</h1>";

// Inserir matrizes de exemplo
$matrizes = [
    [
        'nome' => 'Luna',
        'data_nascimento' => '2020-05-15',
        'raca' => 'Persa',
        'ninhadas' => 2,
        'caracteristicas_filhotes' => 'Pelagem longa e densa, temperamento calmo',
        'foto' => 'img/matrizes/luna.jpg',
        'linhagem' => 'Linhagem europeia'
    ],
    [
        'nome' => 'Bella',
        'data_nascimento' => '2019-08-10',
        'raca' => 'Maine Coon',
        'ninhadas' => 3,
        'caracteristicas_filhotes' => 'Porte grande, pelagem densa, sociáveis',
        'foto' => 'img/matrizes/bella.jpg',
        'linhagem' => 'Linhagem americana'
    ]
];

// Verificar se já existem matrizes
if (contarRegistros($conn, 'matrizes') == 0) {
    echo "<h2>Inserindo Matrizes</h2>";
    
    foreach ($matrizes as $matriz) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO matrizes (nome, data_nascimento, raca, ninhadas, caracteristicas_filhotes, foto, linhagem)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $matriz['nome'],
                $matriz['data_nascimento'],
                $matriz['raca'],
                $matriz['ninhadas'],
                $matriz['caracteristicas_filhotes'],
                $matriz['foto'],
                $matriz['linhagem']
            ]);
            
            echo "Matriz <strong>{$matriz['nome']}</strong> inserida com sucesso!<br>";
        } catch (PDOException $e) {
            echo "Erro ao inserir matriz {$matriz['nome']}: " . $e->getMessage() . "<br>";
        }
    }
} else {
    echo "<p>Já existem matrizes cadastradas. Pulando inserção.</p>";
}

// Inserir padreadores de exemplo
$padreadores = [
    [
        'nome' => 'Thor',
        'data_nascimento' => '2019-03-20',
        'raca' => 'Persa',
        'caracteristicas_filhotes' => 'Pelagem longa e sedosa, olhos expressivos',
        'foto' => 'img/padreadores/thor.jpg',
        'linhagem' => 'Linhagem europeia'
    ],
    [
        'nome' => 'Max',
        'data_nascimento' => '2018-11-05',
        'raca' => 'Maine Coon',
        'caracteristicas_filhotes' => 'Porte grande, orelhas com tufos, pelagem densa',
        'foto' => 'img/padreadores/max.jpg',
        'linhagem' => 'Linhagem americana'
    ]
];

// Verificar se já existem padreadores
if (contarRegistros($conn, 'padreadores') == 0) {
    echo "<h2>Inserindo Padreadores</h2>";
    
    foreach ($padreadores as $padreador) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO padreadores (nome, data_nascimento, raca, caracteristicas_filhotes, foto, linhagem)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $padreador['nome'],
                $padreador['data_nascimento'],
                $padreador['raca'],
                $padreador['caracteristicas_filhotes'],
                $padreador['foto'],
                $padreador['linhagem']
            ]);
            
            echo "Padreador <strong>{$padreador['nome']}</strong> inserido com sucesso!<br>";
        } catch (PDOException $e) {
            echo "Erro ao inserir padreador {$padreador['nome']}: " . $e->getMessage() . "<br>";
        }
    }
} else {
    echo "<p>Já existem padreadores cadastrados. Pulando inserção.</p>";
}

// Buscar IDs das matrizes e padreadores
$stmt = $conn->query("SELECT id, nome FROM matrizes LIMIT 2");
$matrizesIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("SELECT id, nome FROM padreadores LIMIT 2");
$padreadoresIds = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Inserir gatos de exemplo
if (count($matrizesIds) > 0 && count($padreadoresIds) > 0 && contarRegistros($conn, 'gatos') == 0) {
    echo "<h2>Inserindo Gatos</h2>";
    
    $gatos = [
        [
            'nome' => 'Mia',
            'data_nascimento' => '2022-01-10',
            'cor' => 'Branco',
            'descricao' => 'Filhote dócil e brincalhão',
            'foto' => 'img/gatos/mia.jpg',
            'status' => 'disponivel',
            'matriz_id' => $matrizesIds[0]['id'],
            'padreador_id' => $padreadoresIds[0]['id'],
            'tags_saude' => ['Vacinado', 'Vermifugado'],
            'tags_personalidade' => ['Dócil', 'Brincalhão']
        ],
        [
            'nome' => 'Leo',
            'data_nascimento' => '2021-11-15',
            'cor' => 'Cinza',
            'descricao' => 'Filhote sociável e independente',
            'foto' => 'img/gatos/leo.jpg',
            'status' => 'disponivel',
            'matriz_id' => $matrizesIds[1]['id'],
            'padreador_id' => $padreadoresIds[1]['id'],
            'tags_saude' => ['Vacinado', 'Vermifugado', 'Microchipado'],
            'tags_personalidade' => ['Sociável', 'Independente']
        ]
    ];
    
    foreach ($gatos as $gato) {
        try {
            // Iniciar transação
            $conn->beginTransaction();
            
            // Inserir gato
            $stmt = $conn->prepare("
                INSERT INTO gatos (nome, data_nascimento, cor, descricao, foto, status, matriz_id, padreador_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $gato['nome'],
                $gato['data_nascimento'],
                $gato['cor'],
                $gato['descricao'],
                $gato['foto'],
                $gato['status'],
                $gato['matriz_id'],
                $gato['padreador_id']
            ]);
            
            $gatoId = $conn->lastInsertId();
            
            // Inserir tags de saúde
            foreach ($gato['tags_saude'] as $tag) {
                $stmt = $conn->prepare("
                    INSERT INTO gatos_tags_saude (gato_id, tag)
                    VALUES (?, ?)
                ");
                $stmt->execute([$gatoId, $tag]);
            }
            
            // Inserir tags de personalidade
            foreach ($gato['tags_personalidade'] as $tag) {
                $stmt = $conn->prepare("
                    INSERT INTO gatos_tags_personalidade (gato_id, tag)
                    VALUES (?, ?)
                ");
                $stmt->execute([$gatoId, $tag]);
            }
            
            // Confirmar transação
            $conn->commit();
            
            echo "Gato <strong>{$gato['nome']}</strong> inserido com sucesso!<br>";
        } catch (PDOException $e) {
            // Reverter transação em caso de erro
            $conn->rollBack();
            echo "Erro ao inserir gato {$gato['nome']}: " . $e->getMessage() . "<br>";
        }
    }
} else {
    if (contarRegistros($conn, 'gatos') > 0) {
        echo "<p>Já existem gatos cadastrados. Pulando inserção.</p>";
    } else {
        echo "<p>Não foi possível inserir gatos. Verifique se existem matrizes e padreadores cadastrados.</p>";
    }
}

echo "<p>Processo concluído!</p>";
?> 