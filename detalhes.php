<?php
require_once 'conexao.php';

// id do anumla pela url
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// bacno do animal
$stmt = $pdo->prepare("SELECT * FROM animais WHERE id = :id");
$stmt->execute([':id' => $id]);
$animal = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$animal) {
    die("Animal não encontrado.");
}

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
        $mensagem_sucesso = "Interesse registrado com sucesso!";
    }
}

// separador de fotos)
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
    <!-- botstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #fcfcfc; }
        .navbar { background-color: #ffffff !important; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .navbar-brand { color: #ff6b6b !important; font-weight: 600; }
        .btn-primary { background-color: #ff6b6b; border-color: #ff6b6b; border-radius: 8px; }
        .btn-primary:hover { background-color: #fa5252; border-color: #fa5252; }
    </style>
</head>
<body>

    <!-- navbart -->
    <nav class="navbar navbar-expand-lg navbar-light mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-heart-fill text-danger"></i> Adote um Amigo</a>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">Voltar para a Home</a>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row">
            <!-- carrosel de imagens -->
            <div class="col-md-6 mb-4">
                <div id="carrosselFotos" class="carousel slide shadow-sm rounded" data-bs-ride="carousel" style="border-radius: 12px; overflow: hidden;">
                    <div class="carousel-inner">
                        <?php if (count($fotos) > 0): ?>
                            <?php foreach ($fotos as $index => $foto): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                    <img src="uploads/<?= htmlspecialchars(trim($foto)) ?>" class="d-block w-100" style="height: 450px; object-fit: cover;" alt="Foto do pet">
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="carousel-item active">
                                <img src="https://via.placeholder.com/600x400?text=Sem+Foto" class="d-block w-100" style="height: 450px; object-fit: cover;" alt="Sem foto">
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

            <!-- contatoo -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-body p-4">
                        <h2 class="fw-bold mb-1"><?= htmlspecialchars($animal['nome']) ?></h2>
                        <h5 class="text-muted mb-4"><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($animal['cidade']) ?> - <?= htmlspecialchars($animal['uf']) ?></h5>
                        
                        <ul class="list-group list-group-flush mb-3">
                            <li class="list-group-item"><strong>Espécie:</strong> <?= htmlspecialchars($animal['especie']) ?></li>
                            <li class="list-group-item"><strong>Raça:</strong> <?= htmlspecialchars($animal['raca']) ?></li>
                            <li class="list-group-item"><strong>Idade:</strong> <?= htmlspecialchars($animal['idade_valor'] . ' ' . $animal['idade_tipo']) ?></li>
                            <li class="list-group-item"><strong>Porte:</strong> <?= htmlspecialchars($animal['porte']) ?></li>
                        </ul>
                        
                        <div class="mb-3">
                            <h6 class="fw-bold text-muted small mb-2">Características do Pet:</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <?php if ($animal['castrado'] == 1): ?>
                                    <span class="badge bg-success bg-opacity-75 rounded-pill px-3 py-2"><i class="bi bi-check-circle-fill me-1"></i> Castrado</span>
                                <?php endif; ?>
                                
                                <?php if ($animal['vacinado'] == 1): ?>
                                    <span class="badge bg-success bg-opacity-75 rounded-pill px-3 py-2"><i class="bi bi-check-circle-fill me-1"></i> Vacinado</span>
                                <?php endif; ?>
                                
                                <?php if ($animal['doenca'] == 1): ?>
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> Possui Doença</span>
                                <?php endif; ?>
                                
                                <?php if ($animal['vive_casa'] == 1): ?>
                                    <span class="badge bg-info text-dark bg-opacity-75 rounded-pill px-3 py-2"><i class="bi bi-house-door-fill me-1"></i> Vive em Casa</span>
                                <?php endif; ?>
                                
                                <?php if ($animal['vive_apartamento'] == 1): ?>
                                    <span class="badge bg-info text-dark bg-opacity-75 rounded-pill px-3 py-2"><i class="bi bi-building me-1"></i> Apto p/ Apt</span>
                                <?php endif; ?>
                                
                                <?php if ($animal['resgatado'] == 1): ?>
                                    <span class="badge bg-secondary bg-opacity-75 rounded-pill px-3 py-2"><i class="bi bi-heart-fill me-1"></i> É Resgatado</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <p class="card-text mt-3 text-secondary"><?= nl2br(htmlspecialchars($animal['descricao'])) ?></p>
                    </div>
                </div>

                <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-primary text-white border-0 py-3">
                        <h5 class="mb-0"><i class="bi bi-chat-dots"></i> Tenho Interesse em Adotar</h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($mensagem_sucesso): ?>
                            
                            <div class="alert alert-success border-0 shadow-sm mb-4">
                                <i class="bi bi-check-circle-fill me-2"></i> <?= $mensagem_sucesso ?>
                            </div>
                            
                            <div class="text-center mt-3">
                                <p class="text-muted small mb-3">Para agilizar a adoção, chame o responsável agora mesmo:</p>
                                <?php 
                                    $numero_doador = preg_replace('/[^0-9]/', '', $animal['whatsapp']);
                                    
                                    if (strlen($numero_doador) <= 11 && substr($numero_doador, 0, 2) !== '55') {
                                        $numero_doador = '55' . $numero_doador;
                                    }
                                    
                                    $mensagem_wpp = urlencode("Olá! Vi o {$animal['nome']} no site Adote um Amigo e tenho interesse em adotá-lo.");
                                ?>
                                <a href="https://wa.me/<?= $numero_doador ?>?text=<?= $mensagem_wpp ?>" target="_blank" class="btn btn-success btn-lg w-100 fw-bold shadow-sm">
                                    <i class="bi bi-whatsapp"></i> Chamar no WhatsApp
                                </a>
                            </div>
                            
                        <?php else: ?>
                        
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Seu Nome *</label>
                                    <input type="text" name="nome" class="form-control" required placeholder="Como podemos te chamar?">
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Telefone (WhatsApp) *</label>
                                        <input type="text" name="telefone" class="form-control" required placeholder="(11) 99999-9999">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">E-mail</label>
                                        <input type="email" name="email" class="form-control" placeholder="seu@email.com">
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Mensagem</label>
                                    <textarea name="mensagem" class="form-control" rows="2" placeholder="Por que você quer adotar este pet?"></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 fw-bold py-2">Enviar Interesse</button>
                            </form>
                            
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- botstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>