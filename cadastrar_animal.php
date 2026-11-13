<?php
require_once 'conexao.php';

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $fotos_nomes = [];
    if (isset($_FILES['foto']) && is_array($_FILES['foto']['name'])) {
        $total_fotos = count($_FILES['foto']['name']);
        $extensoes_permitidas = ['jpg', 'jpeg', 'png'];
        
        if (!is_dir('uploads')) {
            mkdir('uploads', 0755, true);
        }

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
    }
    
    $foto_string = implode(',', $fotos_nomes);

    if (empty($mensagem)) {
        try {
            $sql = "INSERT INTO animais (nome, especie, raca, idade_valor, idade_tipo, porte, uf, cidade, whatsapp, foto, descricao, castrado, vacinado, doenca, vive_casa, vive_apartamento, resgatado) 
                    VALUES (:nome, :especie, :raca, :idade_valor, :idade_tipo, :porte, :uf, :cidade, :whatsapp, :foto, :descricao, :castrado, :vacinado, :doenca, :vive_casa, :vive_apartamento, :resgatado)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $_POST['nome'],
                ':especie' => $_POST['especie'],
                ':raca' => $_POST['raca'],
                ':idade_valor' => $_POST['idade_valor'],
                ':idade_tipo' => $_POST['idade_tipo'],
                ':porte' => $_POST['porte'],
                ':uf' => $_POST['uf'],
                ':cidade' => $_POST['cidade'],
                ':whatsapp' => $_POST['whatsapp'],
                ':foto' => $foto_string,
                ':descricao' => $_POST['descricao'],
                ':castrado' => isset($_POST['castrado']) ? 1 : 0,
                ':vacinado' => isset($_POST['vacinado']) ? 1 : 0,
                ':doenca' => isset($_POST['doenca']) ? 1 : 0,
                ':vive_casa' => isset($_POST['vive_casa']) ? 1 : 0,
                ':vive_apartamento' => isset($_POST['vive_apartamento']) ? 1 : 0,
                ':resgatado' => isset($_POST['resgatado']) ? 1 : 0
            ]);
            
            $mensagem = "Pet cadastrado com sucesso!";
            $tipo_mensagem = "success";
            
        } catch (PDOException $e) {
            $mensagem = "Erro ao cadastrar no banco: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Pet - Adote um Amigo</title>
    <!-- bootstrap ccs -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style> 
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; } 
    </style>
</head>
<body class="py-4">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-heart-fill text-danger"></i> Adote um Amigo</a>
            <div class="d-flex">
                <a href="admin.php" class="btn btn-outline-light btn-sm">Voltar ao Painel</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-9">
                
                <!-- sucesso ou erro -->
                <?php if (!empty($mensagem)): ?>
                    <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show" role="alert">
                        <?= $mensagem ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h3 class="fw-bold text-center">Cadastrar Novo Pet para Adoção</h3>
                    </div>
                    <div class="card-body p-4">
                        
                        <form method="POST" action="cadastrar_animal.php" enctype="multipart/form-data">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nome do Pet *</label>
                                    <input type="text" name="nome" class="form-control" required placeholder="Ex: Rex, Mima etc.">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Espécie *</label>
                                    <select name="especie" class="form-select" required>
                                        <option value="">Selecione...</option>
					<option value="Ave">Ave</option>
                                        <option value="Cachorro">Cachorro</option>
                                        <option value="Gato">Gato</option>
                                        <option value="Peixe">Peixe</option>
                                        <option value="Outro">Outro</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Idade *</label>
                                    <div class="input-group">
                                        <input type="number" name="idade_valor" class="form-control" required min="0" placeholder="Ex: 2, 8 etc...">
                                        <select name="idade_tipo" class="form-select">
                                            <option value="Anos">Anos</option>
                                            <option value="Meses">Meses</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Porte *</label>
                                    <select name="porte" class="form-select" required>
                                        <option value="">Selecione...</option>
                                        <option value="Pequeno">Pequeno</option>
                                        <option value="Médio">Médio</option>
                                        <option value="Grande">Grande</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Raça</label>
                                    <input type="text" name="raca" class="form-control" placeholder="Ex: Poodle, Siamês, SRD etc...">
                                </div>

                                <!-- cidade e uf -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">UF *</label>
                                    <select id="uf" name="uf" class="form-select" required>
                                        <option value="">Carregando</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">Cidade *</label>
                                    <select id="cidade" name="cidade" class="form-select" required>
                                        <option value="">Selecione a UF primeiro</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">WhatsApp para Contato *</label>
                                    <input type="text" name="whatsapp" class="form-control" placeholder="Ex: 49999998888" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Fotos do Pet *</label>
                                    <input type="file" name="foto[]" class="form-control" multiple required accept="image/*">
                                    <div class="form-text">Formatos aceitos: JPG e PNG - Segure Ctrl ou Shift para escolher várias imagens.</div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold">Descrição / História do Pet</label>
                                    <textarea name="descricao" class="form-control" rows="3" placeholder="Conte um pouco sobre o temperamento, vacinas, etc..."></textarea>
                                </div>

                                <!-- flag) -->
                                <div class="col-12 mt-4">
                                    <label class="form-label fw-bold">Características Extras</label>
                                    <div class="d-flex flex-wrap gap-3 p-3 bg-light rounded">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="castrado" value="1" id="castrado">
                                            <label class="form-check-label" for="castrado">Castrado</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="vacinado" value="1" id="vacinado">
                                            <label class="form-check-label" for="vacinado">Vacinado</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="doenca" value="1" id="doenca">
                                            <label class="form-check-label" for="doenca">Possui Doença</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="vive_casa" value="1" id="vive_casa">
                                            <label class="form-check-label" for="vive_casa">Acostumado com Casa</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="vive_apartamento" value="1" id="vive_apartamento">
                                            <label class="form-check-label" for="vive_apartamento">Acostumado com Apt</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="resgatado" value="1" id="resgatado">
                                            <label class="form-check-label" for="resgatado">É Resgatado</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 text-end mt-4">
                                    <a href="admin.php" class="btn btn-light me-2">Cancelar</a>
                                    <button type="submit" class="btn btn-primary px-5 py-2"><i class="bi bi-save"></i> Salvar Pet</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- api ibge -->
    <script>
        fetch('https://servicodados.ibge.gov.br/api/v1/localidades/estados')
            .then(res => res.json())
            .then(data => {
                const ufSelect = document.getElementById('uf');
                ufSelect.innerHTML = '<option value="">Selecione o Estado...</option>';
                data.sort((a, b) => a.sigla.localeCompare(b.sigla));
                data.forEach(estado => {
                    ufSelect.innerHTML += `<option value="${estado.sigla}">${estado.sigla}</option>`;
                });
            });

        document.getElementById('uf').addEventListener('change', function() {
            const uf = this.value;
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
                        cidadeSelect.innerHTML += `<option value="${cidade.nome}">${cidade.nome}</option>`;
                    });
                });
        });
    </script>
    
    <!-- bootstrapjs -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>