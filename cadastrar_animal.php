<?php
require_once 'conexao.php';

$mensagem = '';
$tipo_mensagem = '';

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pega os dados do formulário
    $nome = trim($_POST['nome']);
    $especie = $_POST['especie'];
    $raca = trim($_POST['raca']);
    $idade = trim($_POST['idade']);
    $porte = $_POST['porte'];
    $cidade = trim($_POST['cidade']);
    $descricao = trim($_POST['descricao']);
    
    // Configurações do Upload de Múltiplas Fotos
    $fotos_nomes = [];
    if (isset($_FILES['foto']) && is_array($_FILES['foto']['name'])) {
        $total_fotos = count($_FILES['foto']['name']);
        $extensoes_permitidas = ['jpg', 'jpeg', 'png'];
        
        // Cria a pasta uploads se ela não existir
        if (!is_dir('uploads')) {
            mkdir('uploads', 0755, true);
        }

        for ($i = 0; $i < $total_fotos; $i++) {
            if ($_FILES['foto']['error'][$i] === UPLOAD_ERR_OK) {
                $extensao = strtolower(pathinfo($_FILES['foto']['name'][$i], PATHINFO_EXTENSION));
                
                if (in_array($extensao, $extensoes_permitidas)) {
                    // Gera um nome único para cada foto
                    $foto_nome = uniqid() . '_' . $i . '.' . $extensao;
                    $destino = 'uploads/' . $foto_nome;
                    
                    if (move_uploaded_file($_FILES['foto']['tmp_name'][$i], $destino)) {
                        $fotos_nomes[] = $foto_nome; // Guarda o nome da foto na lista
                    } else {
                        $mensagem = "Erro ao fazer o upload de uma das fotos.";
                        $tipo_mensagem = "danger";
                    }
                } else {
                    $mensagem = "Formato de imagem inválido. Use JPG ou PNG.";
                    $tipo_mensagem = "danger";
                }
            }
        }
    }
    // Junta todos os nomes das fotos em uma única string separada por vírgula
    $foto_string = implode(',', $fotos_nomes);
    // Se não houver erro, insere no banco
    // Se não houver erro, insere no banco
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Pet - Adote um Amigo</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Ícones -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-heart-fill text-danger"></i> Adote um Amigo</a>
            <div class="d-flex">
                <a href="index.php" class="btn btn-outline-light">Voltar para a Home</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                
                <!-- Mensagem de Sucesso ou Erro -->
                <?php if (!empty($mensagem)): ?>
                    <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show" role="alert">
                        <?= $mensagem ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Cadastrar Novo Pet para Adoção</h4>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="cadastrar_animal.php" enctype="multipart/form-data">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Nome do Pet *</label>
                                    <input type="text" name="nome" class="form-control" required placeholder="Ex: Rex">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Espécie *</label>
                                    <select name="especie" class="form-select" required>
                                        <option value="">Selecione...</option>
                                        <option value="Cão">Cão</option>
                                        <option value="Gato">Gato</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Raça</label>
                                    <input type="text" name="raca" class="form-control" placeholder="Ex: Poodle, SRD">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Idade</label>
                                    <input type="text" name="idade" class="form-control" placeholder="Ex: 2 anos, 3 meses">
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
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Cidade *</label>
                                    <input type="text" name="cidade" class="form-control" required placeholder="Ex: São Paulo - SP">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Foto do Pet</label>
                                    <input type="file" name="foto[]" class="form-control" accept="image/png, image/jpeg, image/jpg" multiple>>
                                    <div class="form-text">Formatos aceitos: JPG e PNG - Segure Ctrk ou  Shift para escolher várias imagens.</div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Descrição / História do Pet</label>
                                <textarea name="descricao" class="form-control" rows="3" placeholder="Conte um pouco sobre o temperamento, vacinas, etc..."></textarea>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="index.php" class="btn btn-light me-md-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Salvar Pet</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>