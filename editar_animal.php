<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mensagem = '';
$tipo_mensagem = '';

$stmt = $pdo->prepare("SELECT * FROM animais WHERE id = :id");
$stmt->execute([':id' => $id]);
$animal = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$animal) {
    die("Pet não encontrado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $especie = $_POST['especie'];
    $raca = $_POST['raca'];
    $idade_valor = $_POST['idade_valor'];
    $idade_tipo = $_POST['idade_tipo'];
    $porte = $_POST['porte'];
    $uf = $_POST['uf'];
    $cidade = $_POST['cidade'];
    $whatsapp = $_POST['whatsapp'];
    $descricao = $_POST['descricao'];
    
    $castrado = isset($_POST['castrado']) ? 1 : 0;
    $vacinado = isset($_POST['vacinado']) ? 1 : 0;
    $doenca = isset($_POST['doenca']) ? 1 : 0;
    $vive_casa = isset($_POST['vive_casa']) ? 1 : 0;
    $vive_apartamento = isset($_POST['vive_apartamento']) ? 1 : 0;
    $resgatado = isset($_POST['resgatado']) ? 1 : 0;

    $foto_string = $animal['foto']; // Mantém as antigas por padrão
    
    if (isset($_FILES['foto']) && is_array($_FILES['foto']['name']) && $_FILES['foto']['error'][0] !== UPLOAD_ERR_NO_FILE) {
        $fotos_nomes = [];
        $total_fotos = count($_FILES['foto']['name']);
        $extensoes_permitidas = ['jpg', 'jpeg', 'png'];
        
        if (!is_dir('uploads')) { mkdir('uploads', 0755, true); }

        for ($i = 0; $i < $total_fotos; $i++) {
            if ($_FILES['foto']['error'][$i] === UPLOAD_ERR_OK) {
                $extensao = strtolower(pathinfo($_FILES['foto']['name'][$i], PATHINFO_EXTENSION));
                
                if (in_array($extensao, $extensoes_permitidas)) {
                    $foto_nome = uniqid() . '_' . $i . '.' . $extensao;
                    $destino = 'uploads/' . $foto_nome;
                    
                    if (move_uploaded_file($_FILES['foto']['tmp_name'][$i], $destino)) {
                        $fotos_nomes[] = $foto_nome;
                    }
                }
            }
        }
        
        if (count($fotos_nomes) > 0) {
            if (!empty($animal['foto'])) {
                foreach (explode(',', $animal['foto']) as $foto_antiga) {
                    $caminho_antigo = 'uploads/' . trim($foto_antiga);
                    if (file_exists($caminho_antigo)) { unlink($caminho_antigo); }
                }
            }
            $foto_string = implode(',', $fotos_nomes);
        }
    }

    try {
        $sql = "UPDATE animais SET nome = :nome, especie = :especie, raca = :raca, idade_valor = :idade_valor, 
                idade_tipo = :idade_tipo, porte = :porte, uf = :uf, cidade = :cidade, whatsapp = :whatsapp, 
                foto = :foto, descricao = :descricao, castrado = :castrado, vacinado = :vacinado, 
                doenca = :doenca, vive_casa = :vive_casa, vive_apartamento = :vive_apartamento, resgatado = :resgatado 
                WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $nome, ':especie' => $especie, ':raca' => $raca, ':idade_valor' => $idade_valor,
            ':idade_tipo' => $idade_tipo, ':porte' => $porte, ':uf' => $uf, ':cidade' => $cidade,
            ':whatsapp' => $whatsapp, ':foto' => $foto_string, ':descricao' => $descricao,
            ':castrado' => $castrado, ':vacinado' => $vacinado, ':doenca' => $doenca,
            ':vive_casa' => $vive_casa, ':vive_apartamento' => $vive_apartamento, ':resgatado' => $resgatado,
            ':id' => $id
        ]);
        
        $mensagem = "Pet atualizado com sucesso!";
        $tipo_mensagem = "success";
        
        $stmt = $pdo->prepare("SELECT * FROM animais WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $animal = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $mensagem = "Erro ao atualizar: " . $e->getMessage();
        $tipo_mensagem = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Pet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; } </style>
</head>
<body class="py-4">

<div class="container">
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 900px; border-radius: 12px;">
        <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
            <h3 class="fw-bold mb-0">Editar Pet: <?= htmlspecialchars($animal['nome']) ?></h3>
            <a href="admin.php" class="btn btn-outline-secondary btn-sm">Voltar ao Painel</a>
        </div>
        <div class="card-body p-4">
            
            <?php if (!empty($mensagem)): ?>
                <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show">
                    <?= $mensagem ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row g-3">
                    <!-- Nome e Espécie -->
                    <div class="col-md-6">
                        <label class="form-label">Nome do Pet *</label>
                        <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($animal['nome']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Espécie *</label>
                        <select name="especie" class="form-select" required>
                            <option value="Cão" <?= $animal['especie'] == 'Ave' ? 'selected' : '' ?>>Ave</option>
                            <option value="Cão" <?= $animal['especie'] == 'Chachorro' ? 'selected' : '' ?>>Cachorro</option>
                            <option value="Gato" <?= $animal['especie'] == 'Gato' ? 'selected' : '' ?>>Gato</option>
                            <option value="Outro" <?= $animal['especie'] == 'Peixe' ? 'selected' : '' ?>>Peixe</option>
                            <option value="Outro" <?= $animal['especie'] == 'Outro' ? 'selected' : '' ?>>Outro</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Idade *</label>
                        <div class="input-group">
                            <input type="number" name="idade_valor" class="form-control" value="<?= $animal['idade_valor'] ?>" required min="0">
                            <select name="idade_tipo" class="form-select">
                                <option value="Anos" <?= $animal['idade_tipo'] == 'Anos' ? 'selected' : '' ?>>Anos</option>
                                <option value="Meses" <?= $animal['idade_tipo'] == 'Meses' ? 'selected' : '' ?>>Meses</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Porte *</label>
                        <select name="porte" class="form-select" required>
                            <option value="Pequeno" <?= $animal['porte'] == 'Pequeno' ? 'selected' : '' ?>>Pequeno</option>
                            <option value="Médio" <?= $animal['porte'] == 'Médio' ? 'selected' : '' ?>>Médio</option>
                            <option value="Grande" <?= $animal['porte'] == 'Grande' ? 'selected' : '' ?>>Grande</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Raça</label>
                        <input type="text" name="raca" class="form-control" value="<?= htmlspecialchars($animal['raca']) ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">UF *</label>
                        <select id="uf" name="uf" class="form-select" required>
                            <option value="<?= $animal['uf'] ?>"><?= $animal['uf'] ?> (Atual)</option>
                            <!-- O JS vai popular o resto -->
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Cidade *</label>
                        <select id="cidade" name="cidade" class="form-select" required>
                            <option value="<?= htmlspecialchars($animal['cidade']) ?>"><?= htmlspecialchars($animal['cidade']) ?> (Atual)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">WhatsApp para Contato *</label>
                        <input type="text" name="whatsapp" class="form-control" value="<?= htmlspecialchars($animal['whatsapp']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Fotos do Pet (Deixe vazio para manter as atuais)</label>
                        <input type="file" name="foto[]" class="form-control" multiple accept="image/*">
                        <div class="form-text text-danger">Atenção: subir novas fotos apagará as fotos antigas.</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" class="form-control" rows="3"><?= htmlspecialchars($animal['descricao']) ?></textarea>
                    </div>

                    <div class="col-12 mt-4">
                        <label class="form-label fw-bold">Características Extras</label>
                        <div class="d-flex flex-wrap gap-3 p-3 bg-light rounded">
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="castrado" value="1" <?= $animal['castrado'] ? 'checked' : '' ?>><label class="form-check-label">Castrado</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="vacinado" value="1" <?= $animal['vacinado'] ? 'checked' : '' ?>><label class="form-check-label">Vacinado</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="doenca" value="1" <?= $animal['doenca'] ? 'checked' : '' ?>><label class="form-check-label">Possui Doença</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="vive_casa" value="1" <?= $animal['vive_casa'] ? 'checked' : '' ?>><label class="form-check-label">Acostumado com Casa</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="vive_apartamento" value="1" <?= $animal['vive_apartamento'] ? 'checked' : '' ?>><label class="form-check-label">Acostumado com Apt</label></div>
                            <div class="form-check"><input class="form-check-input" type="checkbox" name="resgatado" value="1" <?= $animal['resgatado'] ? 'checked' : '' ?>><label class="form-check-label">É Resgatado</label></div>
                        </div>
                    </div>

                    <div class="col-12 text-end mt-4">
                        <button type="submit" class="btn btn-primary px-5 py-2">Salvar Alterações</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    fetch('https://servicodados.ibge.gov.br/api/v1/localidades/estados')
        .then(res => res.json())
        .then(data => {
            const ufSelect = document.getElementById('uf');
            const ufAtual = ufSelect.options[0].value; 
            ufSelect.innerHTML = '<option value="">Selecione o Estado...</option>';
            
            data.sort((a, b) => a.sigla.localeCompare(b.sigla));
            data.forEach(estado => {
                const selected = (estado.sigla === ufAtual) ? 'selected' : '';
                ufSelect.innerHTML += `<option value="${estado.sigla}" ${selected}>${estado.sigla}</option>`;
            });
            
            if(ufAtual) {
                carregarCidades(ufAtual, document.getElementById('cidade').options[0].value);
            }
        });

    // mudança de uf
    document.getElementById('uf').addEventListener('change', function() {
        carregarCidades(this.value);
    });

    function carregarCidades(uf, cidadeAtual = '') {
        const cidadeSelect = document.getElementById('cidade');
        if (!uf) {
            cidadeSelect.innerHTML = '<option value="">Selecione a UF primeiro...</option>';
            return;
        }
        cidadeSelect.innerHTML = '<option value="">Carregando cidades...</option>';
        
        fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${uf}/municipios`)
            .then(res => res.json())
            .then(cidades => {
                cidadeSelect.innerHTML = '<option value="">Selecione a Cidade...</option>';
                cidades.forEach(cidade => {
                    const selected = (cidade.nome === cidadeAtual) ? 'selected' : '';
                    cidadeSelect.innerHTML += `<option value="${cidade.nome}" ${selected}>${cidade.nome}</option>`;
                });
            });
    }
</script>
</body>
</html>