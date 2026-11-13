<?php
require_once 'conexao.php';

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

// Filtros flags
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #fcfcfc; color: #333; }
        .navbar { background-color: #ffffff !important; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .navbar-brand { color: #ff6b6b !important; font-weight: 600; font-size: 1.5rem; }
        .hero-section { background: linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%); color: #333; padding: 4rem 0; border-bottom: 1px solid #eee; margin-bottom: 2rem; }
        .btn-primary { background-color: #ff6b6b; border-color: #ff6b6b; border-radius: 8px; font-weight: 500; }
        .btn-primary:hover { background-color: #fa5252; border-color: #fa5252; }
        .btn-outline-primary { color: #ff6b6b; border-color: #ff6b6b; }
        .btn-outline-primary:hover { background-color: #ff6b6b; color: white; }
        .card-animal { border: 1px solid #eee; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); transition: 0.3s; background: #fff; }
        .card-animal:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.08); transform: translateY(-3px); }
        .foto-animal { height: 250px; object-fit: cover; border-radius: 12px 12px 0 0; }
        .filter-box { background: #f8f9fa; border-radius: 8px; padding: 12px; border: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-heart-fill text-danger"></i> Adote um Amigo</a>
            <div class="d-flex align-items-center">
                <a href="login.php" class="btn btn-outline-secondary btn-sm me-2">Área do Administrador</a>
                <a href="cadastrar_animal.php" class="btn btn-primary btn-sm">Cadastrar Pet</a>
            </div>
        </div>
    </nav>

    <div class="hero-section text-center">
        <div class="container">
            <h1 class="display-5 fw-bold mb-3">Encontre seu novo melhor amigo</h1>
            <p class="lead text-muted">Adote, não compre. Dê um lar cheio de amor para quem precisa.</p>
        </div>
    </div>

    <div class="container mb-5">
        <div class="card mb-5 shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-body p-4">
                <form method="GET" action="index.php">
                    <!-- linha 1 -->
                    <div class="row g-3 mb-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label text-muted fw-bold small">Buscar por nome ou raça</label>
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
                        <div class="col-md-3">
                            <label class="form-label text-muted fw-bold small">Porte</label>
                            <select name="porte" class="form-select">
                                <option value="">Todos</option>
                                <option value="Pequeno" <?= (isset($_GET['porte']) && $_GET['porte'] == 'Pequeno') ? 'selected' : '' ?>>Pequeno</option>
                                <option value="Médio" <?= (isset($_GET['porte']) && $_GET['porte'] == 'Médio') ? 'selected' : '' ?>>Médio</option>
                                <option value="Grande" <?= (isset($_GET['porte']) && $_GET['porte'] == 'Grande') ? 'selected' : '' ?>>Grande</option>
                            </select>
                        </div>
                    </div>

                    <!-- linha 3 -->
                    <div class="row g-3 mb-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label text-muted fw-bold small">UF (Estado)</label>
                            <select id="uf" name="uf" class="form-select">
                                <option value="">Todos</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted fw-bold small">Cidade</label>
                            <select id="cidade" name="cidade" class="form-select">
                                <option value="">Todas</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted fw-bold small">Idade (Qtd)</label>
                            <input type="number" name="idade_valor" class="form-control" placeholder="Ex: 2" min="0" value="<?= htmlspecialchars($_GET['idade_valor'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted fw-bold small">Tipo Idade</label>
                            <select name="idade_tipo" class="form-select">
                                <option value="">Qualquer</option>
                                <option value="Anos" <?= (isset($_GET['idade_tipo']) && $_GET['idade_tipo'] == 'Anos') ? 'selected' : '' ?>>Anos</option>
                                <option value="Meses" <?= (isset($_GET['idade_tipo']) && $_GET['idade_tipo'] == 'Meses') ? 'selected' : '' ?>>Meses</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mt-4 mb-2">
                        <div class="col-12">
                            <label class="form-label text-muted fw-bold small mb-2">Características Extras</label>
                            <div class="filter-box d-flex flex-wrap gap-4">
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

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <?php if (count($_GET) > 0): ?>
                                <a href="index.php" class="text-muted small text-decoration-none"><i class="bi bi-x-circle"></i> Limpar filtros</a>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-primary px-5 py-2"><i class="bi bi-funnel"></i> Aplicar Filtros</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
            <?php if (count($animais) > 0): ?>
                <?php foreach ($animais as $animal): ?>
                    <div class="col">
                        <div class="card card-animal h-100">
                            <?php 
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
                                    <i class="bi bi-tag-fill"></i> <?= htmlspecialchars($animal['raca']) ?> • <?= htmlspecialchars($animal['idade_valor'] . ' ' . $animal['idade_tipo']) ?> • <?= htmlspecialchars($animal['porte']) ?>
                                </p>
                                <p class="card-text small text-truncate"><?= htmlspecialchars($animal['descricao']) ?></p>

                                <div class="d-flex flex-wrap gap-1 mt-2">
                                    <?php if ($animal['castrado'] == 1): ?>
                                        <span class="badge bg-light text-success border border-success" style="font-size: 0.7rem;">Castrado</span>
                                    <?php endif; ?>
                                    <?php if ($animal['vacinado'] == 1): ?>
                                        <span class="badge bg-light text-success border border-success" style="font-size: 0.7rem;">Vacinado</span>
                                    <?php endif; ?>
                                    <?php if ($animal['vive_casa'] == 1 || $animal['vive_apartamento'] == 1): ?>
                                        <span class="badge bg-light text-info border border-info" style="font-size: 0.7rem;">Sociável</span>
                                    <?php endif; ?>
                                </div>

                            </div> <!-- linha 10 da imagem,fecha ocard-body -->
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

    <footer class="text-center py-4 text-muted small border-top bg-white">
        &copy; <?= date('Y') ?> Adote um Amigo. Projeto Integrador.
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
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>