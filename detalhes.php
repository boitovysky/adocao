<?php
require_once 'conexao.php';

// Pega o ID do animal pela URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Busca o animal no banco
$stmt = $pdo->prepare("SELECT * FROM animais WHERE id = :id");
$stmt->execute([':id' => $id]);
$animal = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$animal) {
    die("Animal não encontrado.");
}

// Processa o formulário de contato (Interessado)
$mensagem_sucesso = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $mensagem = trim($_POST['mensagem']);

    if (!empty($nome) && !empty($telefone)) {
        $sql = "INSERT INTO interessados (nome, email, telefone, mensagem, animal_id) VALUES (:nome, :email, :telefone, :mensagem, :animal_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':telefone' => $telefone,
            ':mensagem' => $mensagem,
            ':animal_id' => $id
        ]);
        $mensagem_sucesso = "Interesse registrado! O responsável entrará em contato.";
    }
}

// Separa as fotos (se houver)
$fotos = [];
if (!empty($animal['foto'])) {
    $fotos = explode(',', $animal['foto']);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($animal['nome']) ?> - Adote um Amigo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-heart-fill text-danger"></i> Adote um Amigo</a>
            <a href="index.php" class="btn btn-outline-light">Voltar</a>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row">
            <!-- Coluna das Fotos (Carrossel) -->
            <div class="col-md-6 mb-4">
                <div id="carrosselFotos" class="carousel slide shadow-sm rounded" data-bs-ride="carousel">
                    <div class="carousel-inner rounded">
                        <?php if (count($fotos) > 0): ?>
                            <?php foreach ($fotos as $index => $foto): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                    <img src="uploads/<?= htmlspecialchars(trim($foto)) ?>" class="d-block w-100" style="height: 400px; object-fit: cover;" alt="Foto do pet">
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="carousel-item active">
                                <img src="https://via.placeholder.com/600x400?text=Sem+Foto" class="d-block w-100" style="height: 400px; object-fit: cover;" alt="Sem foto">
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (count($fotos) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carrosselFotos" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon bg-dark rounded" aria-hidden="true"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carrosselFotos" data-bs-slide="next">
                            <span class="carousel-control-next-icon bg-dark rounded" aria-hidden="true"></span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Coluna de Informações e Contato -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h2 class="fw-bold mb-1"><?= htmlspecialchars($animal['nome']) ?></h2>
                        <h5 class="text-muted mb-3"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($animal['cidade']) ?></h5>
                        
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item"><strong>Espécie:</strong> <?= htmlspecialchars($animal['especie']) ?></li>
                            <li class="list-group-item"><strong>Raça:</strong> <?= htmlspecialchars($animal['raca']) ?></li>
                            <li class="list-group-item"><strong>Idade:</strong> <?= htmlspecialchars($animal['idade']) ?></li>
                            <li class="list-group-item"><strong>Porte:</strong> <?= htmlspecialchars($animal['porte']) ?></li>
                        </ul>
                        
                        <p class="card-text"><?= nl2br(htmlspecialchars($animal['descricao'])) ?></p>
                    </div>
                </div>

                <!-- Formulário de Interesse -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-chat-dots"></i> Tenho Interesse</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($mensagem_sucesso): ?>
                            <div class="alert alert-success"><?= $mensagem_sucesso ?></div>
                            
                            <!-- BOTÃO INTELIGENTE DE WHATSAPP -->
                            <div class="text-center mt-3">
                                <p class="text-muted small">Para agilizar, chame o responsável agora:</p>
                                <?php 
                                    // Pega o telefone que a pessoa digitou e limpa (remove parênteses e traços)
                                    $telefone_limpo = preg_replace('/[^0-9]/', '', $telefone);
                                    $mensagem_wpp = urlencode("Olá! Acabei de preencher o formulário no site e tenho interesse em adotar o(a) {$animal['nome']}.");
                                ?>
                                <a href="https://wa.me/55<?= $telefone_limpo ?>?text=<?= $mensagem_wpp ?>" target="_blank" class="btn btn-success btn-lg w-100">
                                    <i class="bi bi-whatsapp"></i> Chamar no WhatsApp
                                </a>
                            </div>
                        <?php else: ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Seu Nome *</label>
                                    <input type="text" name="nome" class="form-control" required>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Telefone (WhatsApp) *</label>
                                        <input type="text" name="telefone" class="form-control" required placeholder="(11) 99999-9999">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">E-mail</label>
                                        <input type="email" name="email" class="form-control">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Mensagem</label>
                                    <textarea name="mensagem" class="form-control" rows="2" placeholder="Por que você quer adotar?"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 fw-bold">Enviar Interesse</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>