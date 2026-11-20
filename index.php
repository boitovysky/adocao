<?php
session_start();
require_once 'conexao.php';

$adocoes_dia = $pdo->query("SELECT COUNT(*) FROM historico_adocoes WHERE DATE(data_confirmacao) = CURDATE()")->fetchColumn();
$adocoes_semana = $pdo->query("SELECT COUNT(*) FROM historico_adocoes WHERE YEARWEEK(data_confirmacao, 1) = YEARWEEK(CURDATE(), 1)")->fetchColumn();
$adocoes_mes = $pdo->query("SELECT COUNT(*) FROM historico_adocoes WHERE MONTH(data_confirmacao) = MONTH(CURDATE()) AND YEAR(data_confirmacao) = YEAR(CURDATE())")->fetchColumn();
$adocoes_ano = $pdo->query("SELECT COUNT(*) FROM historico_adocoes WHERE YEAR(data_confirmacao) = YEAR(CURDATE())")->fetchColumn();

$where = [];
$params = [];

if (!empty($_GET['busca'])) {
    $where[] = "(nome LIKE :busca OR raca LIKE :busca)";
    $params[':busca'] = '%' . $_GET['busca'] . '%';
}
if (!empty($_GET['especie'])) {
    $where[] = "especie = :especie";
    $params[':especie'] = $_GET['especie'];
}
if (!empty($_GET['sexo'])) {
    $where[] = "sexo = :sexo";
    $params[':sexo'] = $_GET['sexo'];
}
if (!empty($_GET['porte'])) {
    $where[] = "porte = :porte";
    $params[':porte'] = $_GET['porte'];
}
if (!empty($_GET['uf'])) {
    $where[] = "uf = :uf";
    $params[':uf'] = $_GET['uf'];
}
if (!empty($_GET['cidade'])) {
    $where[] = "cidade = :cidade";
    $params[':cidade'] = $_GET['cidade'];
}
if (!empty($_GET['idade_valor'])) {
    $where[] = "idade_valor = :idade_valor";
    $params[':idade_valor'] = $_GET['idade_valor'];
}
if (!empty($_GET['idade_tipo'])) {
    $where[] = "idade_tipo = :idade_tipo";
    $params[':idade_tipo'] = $_GET['idade_tipo'];
}
if (isset($_GET['castrado']) && $_GET['castrado'] == '1') { $where[] = "castrado = 1"; }
if (isset($_GET['vacinado']) && $_GET['vacinado'] == '1') { $where[] = "vacinado = 1"; }
if (isset($_GET['doenca']) && $_GET['doenca'] == '1') { $where[] = "doenca = 1"; }
if (isset($_GET['vive_casa']) && $_GET['vive_casa'] == '1') { $where[] = "vive_casa = 1"; }
if (isset($_GET['vive_apartamento']) && $_GET['vive_apartamento'] == '1') { $where[] = "vive_apartamento = 1"; }
if (isset($_GET['resgatado']) && $_GET['resgatado'] == '1') { $where[] = "resgatado = 1"; }

$sql = "SELECT * FROM animais WHERE status = 'Disponível'";
if (count($where) > 0) {
    $sql .= " AND " . implode(' AND ', $where);
}
$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$animais = $stmt->fetchAll(PDO::FETCH_ASSOC);

$uf_selecionada = $_GET['uf'] ?? '';
$cidade_selecionada = $_GET['cidade'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adote um Amigo - Encontre seu novo pet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    
    <style>
        .hero-layout { position: relative; padding: 7rem 0 5rem; overflow: hidden; }
        .hero-bg-shape { position: absolute; top: -10%; right: -5%; width: 50%; height: 120%; background: var(--amarelo-pastel); border-radius: 50% 0 0 50%; z-index: 0; }
        .hero-content { position: relative; z-index: 2; }
        
        .pet-faces-container { display: flex; justify-content: center; gap: 15px; margin-bottom: 20px; position: relative; z-index: 5; }
        .pet-face { width: 80px; height: 80px; background: #fff; border-radius: 50%; box-shadow: 0 10px 20px rgba(0,0,0,0.08); display: flex; align-items: center; justify-content: center; border: 3px solid var(--amarelo-pastel); transition: transform 0.3s ease; }
        .pet-face:hover { transform: translateY(-5px) scale(1.05); }
        
        .pupil-container { transition: transform 0.1s ease-out; }

        .search-dashboard-wrapper { position: relative; z-index: 10; margin-top: -2rem; }
        .search-card, .mini-dashboard { background: #fff; border-radius: 24px; border: 1px solid var(--borda-suave); box-shadow: 0 20px 40px rgba(0,0,0,0.05); padding: 30px; height: 100%; }
        
        .stat-box { text-align: center; padding: 12px 5px; border-radius: 16px; background: var(--cinza-fundo); }
        .stat-number { font-family: 'Nunito', sans-serif; font-size: 1.8rem; font-weight: 800; color: var(--laranja-hover); line-height: 1; }
        .stat-label { font-size: 0.75rem; color: var(--texto-secundario); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; margin-top: 5px; }

        .bento-grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 25px; margin-top: 3rem; }
        .bento-item { background: #fff; border-radius: 24px; border: 1px solid var(--borda-suave); box-shadow: var(--sombra-card); transition: var(--transicao-suave); }
        .bento-item:hover { transform: translateY(-5px); box-shadow: var(--sombra-hover); }
        .bento-large { grid-column: span 8; }
        .bento-small { grid-column: span 4; }
        
        .social-proof-card { background: var(--laranja-suave); color: #fff; border-radius: 24px; padding: 40px; text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%; }
        .section-spacer { margin-top: 6rem; margin-bottom: 4rem; }

        @media (max-width: 768px) {
            .bento-large, .bento-small { grid-column: span 12; }
            .hero-bg-shape { width: 100%; border-radius: 0 0 50% 50%; height: 100%; }
            .search-dashboard-wrapper { margin-top: 0; }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-heart-fill"></i> Adote um Amigo
            </a>
            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['adotante_id'])): ?>
                    <a href="perfil_adotante.php" class="btn btn-outline-primary btn-sm me-2 fw-bold"><i class="bi bi-person-circle"></i> Meu Perfil</a>
                    <a href="logout_adotante.php" class="btn btn-outline-danger btn-sm">Sair</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-secondary btn-sm me-2">Área do Administrador</a>
                    <a href="cadastro_adotante.php" class="btn btn-primary btn-sm">Criar Conta</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <header class="hero-layout text-center">
        <div class="hero-bg-shape"></div>
        
        <div class="container hero-content">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto">
                    
                    <div class="pet-faces-container" id="faces-container">
                        <div class="pet-face">
                            <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" width="60" height="60">
                                <path d="M25 35 Q10 10 30 20 Z" fill="#D3A374"/>
                                <path d="M75 35 Q90 10 70 20 Z" fill="#D3A374"/>
                                <circle cx="50" cy="55" r="30" fill="#F4D3AC"/>
                                <ellipse cx="50" cy="65" rx="15" ry="10" fill="#FFF"/>
                                <ellipse cx="50" cy="60" rx="4" ry="3" fill="#3A3A3A"/>
                                <circle cx="38" cy="48" r="7" fill="#FFF"/>
                                <circle cx="62" cy="48" r="7" fill="#FFF"/>
                                <g class="pupil-container">
                                    <circle cx="38" cy="48" r="3.5" fill="#3A3A3A"/>
                                    <circle cx="62" cy="48" r="3.5" fill="#3A3A3A"/>
                                </g>
                            </svg>
                        </div>
                        <div class="pet-face">
                            <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" width="60" height="60">
                                <path d="M25 35 L15 5 L45 25 Z" fill="#FFB067"/>
                                <path d="M75 35 L85 5 L55 25 Z" fill="#FFB067"/>
                                <circle cx="50" cy="55" r="30" fill="#FFD5B0"/>
                                <path d="M47 60 L53 60 L50 65 Z" fill="#FF8B8B"/>
                                <line x1="20" y1="60" x2="40" y2="62" stroke="#3A3A3A" stroke-width="1.5"/>
                                <line x1="80" y1="60" x2="60" y2="62" stroke="#3A3A3A" stroke-width="1.5"/>
                                <circle cx="38" cy="45" r="8" fill="#FFF"/>
                                <circle cx="62" cy="45" r="8" fill="#FFF"/>
                                <g class="pupil-container">
                                    <ellipse cx="38" cy="45" rx="2.5" ry="5" fill="#3A3A3A"/>
                                    <ellipse cx="62" cy="45" rx="2.5" ry="5" fill="#3A3A3A"/>
                                </g>
                            </svg>
                        </div>
                    </div>

                    <h1 class="display-3 fw-bold mb-4" style="color: var(--texto-principal); letter-spacing: -1px;">
                        Encontre seu novo <br>
                        <span style="color: var(--laranja-hover);">melhor amigo</span>
                    </h1>
                    <p class="lead mb-5 mx-auto" style="max-width: 600px; color: var(--texto-secundario);">
                        Adote, não compre. Dê um lar cheio de amor para quem precisa e transforme a vida de um animal.
                    </p>
                </div>
            </div>
        </div>
    </header>

    <div class="container search-dashboard-wrapper">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-4">
                <div class="mini-dashboard">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-heart-pulse-fill text-warning fs-3 me-2"></i>
                        <h5 class="fw-bold mb-0">Nossas Conquistas</h5>
                    </div>
                    <p class="text-muted small mb-4">Veja o impacto da nossa comunidade de adoção responsável.</p>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="stat-box">
                                <div class="stat-number"><?= $adocoes_dia ?></div>
                                <div class="stat-label">Hoje</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box">
                                <div class="stat-number"><?= $adocoes_semana ?></div>
                                <div class="stat-label">Esta Semana</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box">
                                <div class="stat-number"><?= $adocoes_mes ?></div>
                                <div class="stat-label">Este Mês</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box" style="background: var(--amarelo-pastel);">
                                <div class="stat-number"><?= $adocoes_ano ?></div>
                                <div class="stat-label">Este Ano</div>
                            </div>
                        </div>
                    </div>
                    <p class="text-center text-muted small mb-0"><i class="bi bi-check-circle-fill text-success"></i> Reencontros confirmados</p>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="search-card">
                    <h5 class="mb-4 fw-bold"><i class="bi bi-search text-warning"></i> Encontre o pet ideal</h5>
                    <form method="GET" action="index.php">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label text-muted fw-bold small">Nome ou Raça</label>
                                <input type="text" name="busca" class="form-control" placeholder="Ex: Rex, Poodle..." value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label text-muted fw-bold small">Espécie</label>
                                <select name="especie" class="form-select">
                                    <option value="">Todas</option>
                                    <option value="Ave" <?= (isset($_GET['especie']) && $_GET['especie'] == 'Ave') ? 'selected' : '' ?>>Ave</option>
                                    <option value="Cachorro" <?= (isset($_GET['especie']) && $_GET['especie'] == 'Cachorro') ? 'selected' : '' ?>>Cachorro</option>
                                    <option value="Gato" <?= (isset($_GET['especie']) && $_GET['especie'] == 'Gato') ? 'selected' : '' ?>>Gato</option>
                                    <option value="Peixe" <?= (isset($_GET['especie']) && $_GET['especie'] == 'Peixe') ? 'selected' : '' ?>>Peixe</option>
                                    <option value="Outro" <?= (isset($_GET['especie']) && $_GET['especie'] == 'Outro') ? 'selected' : '' ?>>Outro</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted fw-bold small">Sexo</label>
                                <select name="sexo" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="Macho" <?= (isset($_GET['sexo']) && $_GET['sexo'] == 'Macho') ? 'selected' : '' ?>>Macho</option>
                                    <option value="Fêmea" <?= (isset($_GET['sexo']) && $_GET['sexo'] == 'Fêmea') ? 'selected' : '' ?>>Fêmea</option>
                                </select>
                            </div>
                        </div>

                        <div class="collapse mt-3" id="filtrosAvancados">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label text-muted fw-bold small">Porte</label>
                                    <select name="porte" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="Pequeno" <?= (isset($_GET['porte']) && $_GET['porte'] == 'Pequeno') ? 'selected' : '' ?>>Pequeno</option>
                                        <option value="Médio" <?= (isset($_GET['porte']) && $_GET['porte'] == 'Médio') ? 'selected' : '' ?>>Médio</option>
                                        <option value="Grande" <?= (isset($_GET['porte']) && $_GET['porte'] == 'Grande') ? 'selected' : '' ?>>Grande</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-muted fw-bold small">UF</label>
                                    <select id="uf" name="uf" class="form-select">
                                        <option value="">Todos</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label text-muted fw-bold small">Cidade</label>
                                    <select id="cidade" name="cidade" class="form-select">
                                        <option value="">Todas</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-muted fw-bold small">Idade</label>
                                    <input type="number" name="idade_valor" class="form-control" placeholder="Ex: 2" min="0" value="<?= htmlspecialchars($_GET['idade_valor'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-muted fw-bold small">Tipo de Idade</label>
                                    <select name="idade_tipo" class="form-select">
                                        <option value="">Qualquer</option>
                                        <option value="Anos" <?= (isset($_GET['idade_tipo']) && $_GET['idade_tipo'] == 'Anos') ? 'selected' : '' ?>>Anos</option>
                                        <option value="Meses" <?= (isset($_GET['idade_tipo']) && $_GET['idade_tipo'] == 'Meses') ? 'selected' : '' ?>>Meses</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="filter-box d-flex flex-wrap gap-4 justify-content-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="castrado" value="1" id="f_castrado" <?= (isset($_GET['castrado']) && $_GET['castrado'] == '1') ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="f_castrado">Castrado</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="vacinado" value="1" id="f_vacinado" <?= (isset($_GET['vacinado']) && $_GET['vacinado'] == '1') ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="f_vacinado">Vacinado</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="doenca" value="1" id="f_doenca" <?= (isset($_GET['doenca']) && $_GET['doenca'] == '1') ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="f_doenca">Possui Doença</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="vive_casa" value="1" id="f_casa" <?= (isset($_GET['vive_casa']) && $_GET['vive_casa'] == '1') ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="f_casa">Vive em Casa</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="vive_apartamento" value="1" id="f_apt" <?= (isset($_GET['vive_apartamento']) && $_GET['vive_apartamento'] == '1') ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="f_apt">Apto p/ Apartamento</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="resgatado" value="1" id="f_resgatado" <?= (isset($_GET['resgatado']) && $_GET['resgatado'] == '1') ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="f_resgatado">É Resgatado</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#filtrosAvancados">
                                <i class="bi bi-sliders"></i> Filtros Avançados
                            </button>
                            <div>
                                <?php if (count($_GET) > 0): ?>
                                    <a href="index.php" class="btn btn-link text-muted text-decoration-none btn-sm me-2">Limpar</a>
                                <?php endif; ?>
                                <button type="submit" class="btn btn-primary px-4 py-2"><i class="bi bi-search"></i> Buscar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container section-spacer">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h2 class="fw-bold mb-1">Amigos esperando por um lar</h2>
                <p class="text-muted mb-0">Conheça os pets disponíveis para adoção agora.</p>
            </div>
            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><?= count($animais) ?> Encontrados</span>
        </div>

        <div class="bento-grid">
            <?php if (count($animais) > 0): ?>
                <?php foreach ($animais as $index => $animal): ?>
                    <?php 
                        $fotos_array = explode(',', $animal['foto']);
                        $primeira_foto = !empty($fotos_array[0]) && file_exists('uploads/' . trim($fotos_array[0])) 
                                ? 'uploads/' . trim($fotos_array[0]) 
                                : 'https://via.placeholder.com/400x250?text=Sem+Foto'; 
                        
                        $col_class = ($index % 3 == 0) ? 'bento-large' : 'bento-small';
                        $img_height = ($index % 3 == 0) ? '300px' : '250px';
                    ?>
                    
                    <div class="bento-item <?= $col_class ?> overflow-hidden d-flex flex-column">
                        <img src="<?= $primeira_foto ?>" style="height: <?= $img_height ?>; width: 100%; object-fit: cover;" alt="Foto de <?= htmlspecialchars($animal['nome']) ?>">
                        
                        <div class="p-4 flex-grow-1 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h4 class="fw-bold mb-0"><?= htmlspecialchars($animal['nome']) ?></h4>
                                <span class="badge bg-info text-dark"><?= htmlspecialchars($animal['especie']) ?></span>
                            </div>
                            
                            <p class="text-muted small mb-3">
                                <i class="bi bi-geo-alt-fill text-warning"></i> <?= htmlspecialchars($animal['cidade']) ?> - <?= htmlspecialchars($animal['uf']) ?> &bull; 
                                <?= htmlspecialchars($animal['raca']) ?> &bull; <?= htmlspecialchars($animal['sexo']) ?> &bull; <?= htmlspecialchars($animal['idade_valor'] . ' ' . $animal['idade_tipo']) ?>
                            </p>
                            
                            <p class="card-text small text-truncate mb-4 flex-grow-1" style="color: var(--texto-secundario);"><?= htmlspecialchars($animal['descricao']) ?></p>
                            
                            <div class="d-flex flex-wrap gap-2 mb-4">
                                <?php if ($animal['castrado'] == 1): ?>
                                    <span class="badge bg-light text-success border border-success">Castrado</span>
                                <?php endif; ?>
                                <?php if ($animal['vacinado'] == 1): ?>
                                    <span class="badge bg-light text-success border border-success">Vacinado</span>
                                <?php endif; ?>
                                <?php if ($animal['vive_casa'] == 1 || $animal['vive_apartamento'] == 1): ?>
                                    <span class="badge bg-light text-info border border-info">Sociável</span>
                                <?php endif; ?>
                            </div>
                            
                            <a href="detalhes.php?id=<?= $animal['id'] ?>" class="btn btn-outline-primary w-100 fw-bold mt-auto">
                                <i class="bi bi-heart"></i> Quero Adotar
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5 bento-item">
                    <h4 class="text-muted">Nenhum pet encontrado com esses filtros. 😢</h4>
                    <a href="index.php" class="btn btn-primary mt-3">Limpar Filtros e ver todos</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <section class="container my-5 py-5 bg-white rounded-5 shadow-sm border section-spacer">
        <div class="row align-items-center g-5 p-4">
            <div class="col-md-6">
                <div class="social-proof-card" style="background: var(--laranja-suave);">
                    <i class="bi bi-people-fill" style="font-size: 4rem; margin-bottom: 20px;"></i>
                    <h3 class="fw-bold">Faça parte dessa rede de amor</h3>
                    <p class="mb-0">Milhares de pessoas já encontraram seus melhores amigos. A sua vez pode ser agora.</p>
                </div>
            </div>
            <div class="col-md-6">
                <h2 class="fw-bold mb-3" style="color: var(--texto-principal);">Por que adotar com a gente?</h2>
                <div class="d-flex mb-4">
                    <div class="me-3"><i class="bi bi-shield-check text-warning fs-3"></i></div>
                    <div>
                        <h5 class="fw-bold mb-1">Processo Seguro</h5>
                        <p class="text-muted small mb-0">Todos os doadores são verificados e o contato é feito de forma direta via WhatsApp.</p>
                    </div>
                </div>
                <div class="d-flex mb-4">
                    <div class="me-3"><i class="bi bi-geo-alt text-warning fs-3"></i></div>
                    <div>
                        <h5 class="fw-bold mb-1">Perto de Você</h5>
                        <p class="text-muted small mb-0">Filtre por estado e cidade para encontrar um amigo que já mora na sua região.</p>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="me-3"><i class="bi bi-heart-pulse text-warning fs-3"></i></div>
                    <div>
                        <h5 class="fw-bold mb-1">Adoção Responsável</h5>
                        <p class="text-muted small mb-0">Incentivamos a castração e a vacinação. Adote com consciência.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="text-center py-5 mt-5 border-top bg-white">
        <div class="container">
            <a class="navbar-brand mb-4 d-inline-block" href="index.php">
                <i class="bi bi-heart-fill"></i> Adote um Amigo
            </a>
            <p class="text-muted small mb-1">&copy; <?= date('Y') ?> Adote um Amigo. Projeto Integrador.</p>
            <p class="text-muted small">Desenvolvido com amor para conectar pets e tutores.</p>
        </div>
    </footer>

    <script>
        const ufSelecionada = "<?= $uf_selecionada ?>";
        const cidadeSelecionada = "<?= $cidade_selecionada ?>";

        fetch('https://servicodados.ibge.gov.br/api/v1/localidades/estados')
            .then(res => res.json())
            .then(data => {
                const ufSelect = document.getElementById('uf');
                data.sort((a, b) => a.sigla.localeCompare(b.sigla));
                data.forEach(estado => {
                    const selected = (estado.sigla === ufSelecionada) ? 'selected' : '';
                    ufSelect.innerHTML += `<option value="${estado.sigla}" ${selected}>${estado.sigla}</option>`;
                });
                if(ufSelecionada) { carregarCidades(ufSelecionada, cidadeSelecionada); }
            });

        document.getElementById('uf').addEventListener('change', function() {
            carregarCidades(this.value);
        });

        function carregarCidades(uf, cidadeMantida = '') {
            const cidadeSelect = document.getElementById('cidade');
            if (!uf) {
                cidadeSelect.innerHTML = '<option value="">Todas</option>';
                return;
            }
            cidadeSelect.innerHTML = '<option value="">Carregando...</option>';
            fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${uf}/municipios`)
                .then(res => res.json())
                .then(cidades => {
                    cidadeSelect.innerHTML = '<option value="">Todas</option>';
                    cidades.forEach(cidade => {
                        const selected = (cidade.nome === cidadeMantida) ? 'selected' : '';
                        cidadeSelect.innerHTML += `<option value="${cidade.nome}" ${selected}>${cidade.nome}</option>`;
                    });
                });
        }

        const facesContainer = document.getElementById('faces-container');
        const pupils = document.querySelectorAll('.pupil-container');

        if (window.innerWidth > 768) {
            document.addEventListener('mousemove', (e) => {
                const rect = facesContainer.getBoundingClientRect();
                const centerX = rect.left + (rect.width / 2);
                const centerY = rect.top + (rect.height / 2);
                
                const angle = Math.atan2(e.clientY - centerY, e.clientX - centerX);
                const maxDistance = 3;
                
                const moveX = Math.cos(angle) * maxDistance;
                const moveY = Math.sin(angle) * maxDistance;

                pupils.forEach(pupil => {
                    pupil.style.transform = `translate(${moveX}px, ${moveY}px)`;
                });
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>