<?php
// Chama o arquivo de conexão que criamos
require_once 'conexao.php';

// 1. Prepara a busca e os filtros
$where = [];
$params = [];

// Verifica se o usuário digitou algo na busca
if (!empty($_GET['busca'])) {
    $where[] = "(nome LIKE :busca OR raca LIKE :busca)";
    $params[':busca'] = '%' . $_GET['busca'] . '%';
}

// Verifica o filtro de espécie
if (!empty($_GET['especie'])) {
    $where[] = "especie = :especie";
    $params[':especie'] = $_GET['especie'];
}

// Verifica o filtro de porte
if (!empty($_GET['porte'])) {
    $where[] = "porte = :porte";
    $params[':porte'] = $_GET['porte'];
}

// 2. Monta a query SQL
$sql = "SELECT * FROM animais WHERE status = 'Disponível'";
if (count($where) > 0) {
    $sql .= " AND " . implode(' AND ', $where);
}
$sql .= " ORDER BY id DESC";

// Executa a query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$animais = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adote um Amigo - Encontre seu novo pet</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Ícones do Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <!-- Fonte Google (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        /* Estilização Moderna e Suave */
        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: #fcfcfc; 
            color: #333; 
        }
        
        /* Navbar branca com sombra suave */
        .navbar { 
            background-color: #ffffff !important; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
        }
        .navbar-brand { 
            color: #ff6b6b !important; 
            font-weight: 600; 
            font-size: 1.5rem;
        }
        
        /* Banner principal em tons pastéis */
        .hero-section { 
            background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); 
            color: #333; 
            padding: 4rem 0; 
            border-bottom: 1px solid #eee; 
            margin-bottom: 2rem;
        }
        
        /* Botões personalizados na cor do tema */
        .btn-primary { 
            background-color: #ff6b6b; 
            border-color: #ff6b6b; 
            border-radius: 8px; 
            font-weight: 500;
        }
        .btn-primary:hover { 
            background-color: #fa5252; 
            border-color: #fa5252; 
        }
        .btn-outline-primary {
            color: #ff6b6b;
            border-color: #ff6b6b;
        }
        .btn-outline-primary:hover {
            background-color: #ff6b6b;
            color: white;
        }
        
        /* Cards dos Animais */
        .card-animal { 
            border: 1px solid #eee; 
            border-radius: 12px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.03); 
            transition: 0.3s; 
            background: #fff;
        }
        .card-animal:hover { 
            box-shadow: 0 8px 24px rgba(0,0,0,0.08); 
            transform: translateY(-3px); 
        }
        .foto-animal { 
            height: 250px; 
            object-fit: cover; 
            border-radius: 12px 12px 0 0; 
        }
    </style>
</head>
<body>

    <!-- Barra de Navegação -->
    <nav class="navbar navbar-expand-lg navbar-light mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-heart-fill text-danger"></i> Adote um Amigo
            </a>
            <div class="d-flex align-items-center">
                <a href="login.php" class="btn btn-outline-secondary btn-sm me-2">Área do Administrador</a>
                <a href="cadastrar_animal.php" class="btn btn-primary btn-sm">Cadastrar Pet</a>
            </div>
        </div>
    </nav>

    <!-- Banner Principal -->
    <div class="hero-section text-center">
        <div class="container">
            <h1 class="display-5 fw-bold mb-3">Encontre seu novo melhor amigo</h1>
            <p class="lead text-muted">Adote, não compre. Dê um lar cheio de amor para quem precisa.</p>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Formulário de Busca e Filtros -->
        <div class="card mb-5 shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-body p-4">
                <form method="GET" action="index.php" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label text-muted fw-bold small">Buscar por nome ou raça</label>
                        <input type="text" name="busca" class="form-control" placeholder="Ex: Rex, Poodle..." value="<?= isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted fw-bold small">Espécie</label>
                        <select name="especie" class="form-select">
                            <option value="">Todas</option>
                            <option value="Cão" <?= (isset($_GET['especie']) && $_GET['especie'] == 'Cão') ? 'selected' : '' ?>>Cão</option>
                            <option value="Gato" <?= (isset($_GET['especie']) && $_GET['especie'] == 'Gato') ? 'selected' : '' ?>>Gato</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted fw-bold small">Porte</label>
                        <select name="porte" class="form-select">
                            <option value="">Todos</option>
                            <option value="Pequeno" <?= (isset($_GET['porte']) && $_GET['porte'] == 'Pequeno') ? 'selected' : '' ?>>Pequeno</option>
                            <option value="Médio" <?= (isset($_GET['porte']) && $_GET['porte'] == 'Médio') ? 'selected' : '' ?>>Médio</option>
                            <option value="Grande" <?= (isset($_GET['porte']) && $_GET['porte'] == 'Grande') ? 'selected' : '' ?>>Grande</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 py-2"><i class="bi bi-search"></i> Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de Animais -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
            <?php if (count($animais) > 0): ?>
                <?php foreach ($animais as $animal): ?>
                    <div class="col">
                        <div class="card card-animal h-100">
                            
                            <?php 
                                // Pega apenas a primeira foto para o card da home
                                $fotos_array = explode(',', $animal['foto']);
                                $primeira_foto = !empty($fotos_array[0]) && file_exists('uploads/' . trim($fotos_array[0])) 
                                        ? 'uploads/' . trim($fotos_array[0]) 
                                        : 'https://via.placeholder.com/400x250?text=Sem+Foto'; 
                            ?>
                            <img src="<?= $primeira_foto ?>" class="card-img-top foto-animal" alt="Foto de <?= htmlspecialchars($animal['nome']) ?>">
                            
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="card-title fw-bold mb-0"><?= htmlspecialchars($animal['nome']) ?></h5>
                                    <span class="badge bg-info text-dark"><?= htmlspecialchars($animal['especie']) ?></span>
                                </div>
                                <p class="card-text text-muted small mb-2">
                                    <i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($animal['cidade']) ?> - <?= htmlspecialchars($animal['uf']) ?> <br>
                                    <!-- Junta o valor e o tipo da idade -->
                                    <i class="bi bi-tag-fill"></i> <?= htmlspecialchars($animal['raca']) ?> • <?= htmlspecialchars($animal['idade_valor'] . ' ' . $animal['idade_tipo']) ?> • <?= htmlspecialchars($animal['porte']) ?>
                                </p>
                                <p class="card-text small text-truncate"><?= htmlspecialchars($animal['descricao']) ?></p>
                            </div>
                            
                            <div class="card-footer bg-white border-top-0 pb-3">
                                <a href="detalhes.php?id=<?= $animal['id'] ?>" class="btn btn-outline-primary w-100 fw-bold">
                                    <i class="bi bi-heart"></i> Quero Adotar
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <h4 class="text-muted">Nenhum pet encontrado com esses filtros. 😢</h4>
                    <a href="index.php" class="btn btn-primary mt-3">Limpar Filtros</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Rodapé simples -->
    <footer class="text-center py-4 text-muted small border-top bg-white">
        &copy; <?= date('Y') ?> Adote um Amigo. Projeto Integrador.
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>