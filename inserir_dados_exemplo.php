<?php
// Incluir arquivo de configuração
require_once 'config.php';

// Conexão com o banco de dados
try {
    // Conectar ao banco de dados
    $pdo = conectarBD();
    
    echo "<h2>Inserindo dados de exemplo...</h2>";
    
    // Inserir usuários de exemplo
    $usuarios = [
        [
            'name' => 'Administrador',
            'user_name' => 'admin',
            'password' => criptografarSenha('admin123'),
            'type' => 'admin'
        ],
        [
            'name' => 'Bernardo Zaidan',
            'user_name' => 'bernardo',
            'password' => criptografarSenha('bernardo123'),
            'type' => 'admin'
        ],
        [
            'name' => 'Usuário Teste',
            'user_name' => 'usuario',
            'password' => criptografarSenha('usuario123'),
            'type' => 'user'
        ]
    ];
    
    // Inserir usuários
    $stmt = $pdo->prepare("INSERT IGNORE INTO `usuarios` (`name`, `user_name`, `password`, `type`) VALUES (?, ?, ?, ?)");
    
    foreach ($usuarios as $usuario) {
        // Verificar se o usuário já existe
        $check = $pdo->prepare("SELECT COUNT(*) FROM `usuarios` WHERE `user_name` = ?");
        $check->execute([$usuario['user_name']]);
        $exists = $check->fetchColumn();
        
        if (!$exists) {
            $stmt->execute([
                $usuario['name'],
                $usuario['user_name'],
                $usuario['password'],
                $usuario['type']
            ]);
            echo "<p>Usuário '{$usuario['user_name']}' inserido com sucesso!</p>";
        } else {
            echo "<p>Usuário '{$usuario['user_name']}' já existe.</p>";
        }
    }
    
    // Inserir matrizes de exemplo
    $matrizes = [
        [
            'nome' => 'Luna',
            'data_nascimento' => '2020-05-15',
            'raca' => 'Persa',
            'ninhadas' => 3,
            'caracteristicas' => 'Filhotes com pelagem longa e densa, geralmente de cores claras.',
            'foto' => 'https://example.com/images/luna.jpg',
            'linhagem' => 'Descendente de campeões de exposição.'
        ],
        [
            'nome' => 'Mia',
            'data_nascimento' => '2019-08-22',
            'raca' => 'Maine Coon',
            'ninhadas' => 2,
            'caracteristicas' => 'Filhotes grandes e robustos, com pelagem semi-longa.',
            'foto' => 'https://example.com/images/mia.jpg',
            'linhagem' => 'Linhagem americana pura.'
        ],
        [
            'nome' => 'Bella',
            'data_nascimento' => '2021-03-10',
            'raca' => 'Siamês',
            'ninhadas' => 1,
            'caracteristicas' => 'Filhotes com pontas escuras e olhos azuis intensos.',
            'foto' => 'https://example.com/images/bella.jpg',
            'linhagem' => 'Linhagem tailandesa tradicional.'
        ]
    ];
    
    // Inserir matrizes
    $stmt = $pdo->prepare("INSERT IGNORE INTO `matrizes` (`nome`, `data_nascimento`, `raca`, `ninhadas`, `caracteristicas`, `foto`, `linhagem`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($matrizes as $matriz) {
        // Verificar se a matriz já existe
        $check = $pdo->prepare("SELECT COUNT(*) FROM `matrizes` WHERE `nome` = ? AND `data_nascimento` = ?");
        $check->execute([$matriz['nome'], $matriz['data_nascimento']]);
        $exists = $check->fetchColumn();
        
        if (!$exists) {
            $stmt->execute([
                $matriz['nome'],
                $matriz['data_nascimento'],
                $matriz['raca'],
                $matriz['ninhadas'],
                $matriz['caracteristicas'],
                $matriz['foto'],
                $matriz['linhagem']
            ]);
            echo "<p>Matriz '{$matriz['nome']}' inserida com sucesso!</p>";
        } else {
            echo "<p>Matriz '{$matriz['nome']}' já existe.</p>";
        }
    }
    
    // Inserir padreadores de exemplo
    $padreadores = [
        [
            'nome' => 'Thor',
            'data_nascimento' => '2019-11-20',
            'raca' => 'Persa',
            'caracteristicas' => 'Transmite genes para pelagem longa e densa, geralmente em cores sólidas.',
            'foto' => 'https://example.com/images/thor.jpg',
            'linhagem' => 'Campeão de exposições internacionais.'
        ],
        [
            'nome' => 'Max',
            'data_nascimento' => '2018-07-14',
            'raca' => 'Maine Coon',
            'caracteristicas' => 'Transmite genes para tamanho grande e pelagem semi-longa.',
            'foto' => 'https://example.com/images/max.jpg',
            'linhagem' => 'Descendente de linhagem premiada.'
        ],
        [
            'nome' => 'Leo',
            'data_nascimento' => '2020-01-05',
            'raca' => 'Siamês',
            'caracteristicas' => 'Transmite genes para padrão de coloração point e olhos azuis.',
            'foto' => 'https://example.com/images/leo.jpg',
            'linhagem' => 'Linhagem pura siamesa.'
        ]
    ];
    
    // Inserir padreadores
    $stmt = $pdo->prepare("INSERT IGNORE INTO `padreadores` (`nome`, `data_nascimento`, `raca`, `caracteristicas`, `foto`, `linhagem`) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($padreadores as $padreador) {
        // Verificar se o padreador já existe
        $check = $pdo->prepare("SELECT COUNT(*) FROM `padreadores` WHERE `nome` = ? AND `data_nascimento` = ?");
        $check->execute([$padreador['nome'], $padreador['data_nascimento']]);
        $exists = $check->fetchColumn();
        
        if (!$exists) {
            $stmt->execute([
                $padreador['nome'],
                $padreador['data_nascimento'],
                $padreador['raca'],
                $padreador['caracteristicas'],
                $padreador['foto'],
                $padreador['linhagem']
            ]);
            echo "<p>Padreador '{$padreador['nome']}' inserido com sucesso!</p>";
        } else {
            echo "<p>Padreador '{$padreador['nome']}' já existe.</p>";
        }
    }
    
    // Obter IDs das matrizes e padreadores inseridos
    $matrizesIds = [];
    $stmt = $pdo->query("SELECT `id`, `nome` FROM `matrizes`");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $matrizesIds[$row['nome']] = $row['id'];
    }
    
    $padreadoresIds = [];
    $stmt = $pdo->query("SELECT `id`, `nome` FROM `padreadores`");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $padreadoresIds[$row['nome']] = $row['id'];
    }
    
    // Inserir gatos de exemplo
    if (!empty($matrizesIds) && !empty($padreadoresIds)) {
        $gatos = [
            [
                'nome' => 'Simba',
                'data_nascimento' => '2022-06-10',
                'cor' => 'Laranja',
                'foto' => 'https://example.com/images/simba.jpg',
                'descricao' => 'Gato persa de pelagem longa e densa, muito brincalhão e sociável.',
                'status' => 'disponivel',
                'matriz_nome' => 'Luna',
                'padreador_nome' => 'Thor',
                'tags_saude' => ['Vacinado', 'Vermifugado', 'Testado FIV/FeLV'],
                'tags_personalidade' => ['Dócil', 'Brincalhão', 'Sociável']
            ],
            [
                'nome' => 'Nina',
                'data_nascimento' => '2022-08-15',
                'cor' => 'Cinza',
                'foto' => 'https://example.com/images/nina.jpg',
                'descricao' => 'Gata Maine Coon de porte grande, muito carinhosa e calma.',
                'status' => 'disponivel',
                'matriz_nome' => 'Mia',
                'padreador_nome' => 'Max',
                'tags_saude' => ['Vacinado', 'Vermifugado', 'Testado FIV/FeLV'],
                'tags_personalidade' => ['Dócil', 'Calmo']
            ],
            [
                'nome' => 'Felix',
                'data_nascimento' => '2022-09-20',
                'cor' => 'Preto e Branco',
                'foto' => 'https://example.com/images/felix.jpg',
                'descricao' => 'Gato siamês com olhos azuis intensos, muito ativo e brincalhão.',
                'status' => 'disponivel',
                'matriz_nome' => 'Bella',
                'padreador_nome' => 'Leo',
                'tags_saude' => ['Vacinado', 'Vermifugado', 'Testado FIV/FeLV', 'Castrado'],
                'tags_personalidade' => ['Brincalhão', 'Independente']
            ]
        ];
        
        // Inserir gatos
        $stmt = $pdo->prepare("INSERT IGNORE INTO `gatos` (`nome`, `data_nascimento`, `cor`, `foto`, `descricao`, `status`, `matriz_id`, `padreador_id`, `tags_saude`, `tags_personalidade`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($gatos as $gato) {
            // Verificar se o gato já existe
            $check = $pdo->prepare("SELECT COUNT(*) FROM `gatos` WHERE `nome` = ? AND `data_nascimento` = ?");
            $check->execute([$gato['nome'], $gato['data_nascimento']]);
            $exists = $check->fetchColumn();
            
            if (!$exists) {
                $matriz_id = isset($matrizesIds[$gato['matriz_nome']]) ? $matrizesIds[$gato['matriz_nome']] : null;
                $padreador_id = isset($padreadoresIds[$gato['padreador_nome']]) ? $padreadoresIds[$gato['padreador_nome']] : null;
                
                $tags_saude_json = json_encode($gato['tags_saude']);
                $tags_personalidade_json = json_encode($gato['tags_personalidade']);
                
                $stmt->execute([
                    $gato['nome'],
                    $gato['data_nascimento'],
                    $gato['cor'],
                    $gato['foto'],
                    $gato['descricao'],
                    $gato['status'],
                    $matriz_id,
                    $padreador_id,
                    $tags_saude_json,
                    $tags_personalidade_json
                ]);
                echo "<p>Gato '{$gato['nome']}' inserido com sucesso!</p>";
            } else {
                echo "<p>Gato '{$gato['nome']}' já existe.</p>";
            }
        }
    } else {
        echo "<p>Não foi possível inserir gatos de exemplo porque não há matrizes ou padreadores cadastrados.</p>";
    }
    
    echo "<h2>Dados de exemplo inseridos com sucesso!</h2>";
    
} catch (PDOException $e) {
    die("<h2>Erro ao inserir dados de exemplo:</h2><p>" . $e->getMessage() . "</p>");
}
?> 