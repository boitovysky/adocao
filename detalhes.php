<?php
session_start();
require_once 'conexao.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM animais WHERE id = :id");
$stmt->execute([':id' => $id]);
$animal = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$animal) {
    die("Animal não encontrado.");
}

$mensagem_sucesso = '';
$mensagem_erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $mensagem = trim($_POST['mensagem']);
    $adotante_id = $_SESSION['adotante_id'] ?? null;

    if (!empty($nome) && !empty($telefone) && $adotante_id) {
        try {
            $sql = "INSERT INTO interessados (nome, email, telefone, mensagem, animal_id) VALUES (:nome, :email, :telefone, :mensagem, :animal_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':telefone' => $telefone,
                ':mensagem' => $mensagem,
                ':animal_id' => $id
            ]);

            $stmt_hist = $pdo->prepare("INSERT INTO historico_adocoes (adotante_id, animal_id) VALUES (:adotante_id, :animal_id)");
            $stmt_hist->execute([':adotante_id' => $adotante_id, ':animal_id' => $id]);

            $mensagem_sucesso = "Interesse registrado com sucesso!";
            
        } catch (PDOException $e) {
            $mensagem_erro = "Erro ao registrar interesse no banco de dados. Tente novamente.";
        }
    }
}

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
    <link rel="stylesheet" href="style.css">
</head>
<body class="py-4">

    <nav class="navbar navbar-expand-lg navbar-light mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-heart-fill"></i> Adote um Amigo</a>
            <div class="d-flex align-items-center">
                <?php if(isset($_SESSION['adotante_id'])): ?>
                    <a href="perfil_adotante.php" class="btn btn-outline-primary btn-sm me-2 fw-bold"><i class="bi bi-person-circle"></i> Meu Perfil</a>
                    <a href="logout_adotante.php" class="btn btn-outline-danger btn-sm">Sair</a>
                <?php else: ?>
                    <a href="login_adotante.php" class="btn btn-outline-secondary btn-sm me-2">Login</a>
                    <a href="cadastro_adotante.php" class="btn btn-primary btn-sm">Criar Conta</a>
                <?php endif; ?>
                <a href="index.php" class="btn btn-outline-dark btn-sm ms-2">Voltar para a Home</a>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <div class="row g-4">
            
            <div class="col-md-6">
                <div id="carrosselFotos" class="carousel slide shadow-sm ambient-light-container" data-bs-ride="carousel" style="height: 450px;">
                    <div id="ambient-bg" class="ambient-light-bg"></div>
                    
                    <div class="carousel-inner h-100 ambient-light-content">
                        <?php if (count($fotos) > 0): ?>
                            <?php foreach ($fotos as $index => $foto): ?>
                                <div class="carousel-item h-100 <?= $index === 0 ? 'active' : '' ?>" data-img-src="uploads/<?= htmlspecialchars(trim($foto)) ?>">
                                    <img src="uploads/<?= htmlspecialchars(trim($foto)) ?>" class="d-block w-100 h-100" style="object-fit: contain; padding: 20px;" alt="Foto do pet">
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="carousel-item active h-100">
                                <img src="https://via.placeholder.com/600x400?text=Sem+Foto" class="d-block w-100 h-100" style="object-fit: cover;" alt="Sem foto">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (count($fotos) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#carrosselFotos" data-bs-slide="prev" style="z-index: 2;">
                            <span class="carousel-control-prev-icon bg-dark rounded-circle" aria-hidden="true"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carrosselFotos" data-bs-slide="next" style="z-index: 2;">
                            <span class="carousel-control-next-icon bg-dark rounded-circle" aria-hidden="true"></span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h2 class="fw-bold mb-0"><?= htmlspecialchars($animal['nome']) ?></h2>
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fs-6"><?= htmlspecialchars($animal['especie']) ?></span>
                        </div>
                        <h5 class="text-muted mb-4"><i class="bi bi-geo-alt-fill text-warning"></i> <?= htmlspecialchars($animal['cidade']) ?> - <?= htmlspecialchars($animal['uf']) ?></h5>
                        
                        <ul class="list-group list-group-flush mb-4">
                            <li class="list-group-item px-0"><strong>Raça:</strong> <?= htmlspecialchars($animal['raca']) ?></li>
                            <li class="list-group-item px-0"><strong>Sexo:</strong> <?= htmlspecialchars($animal['sexo']) ?></li>	
                            <li class="list-group-item px-0"><strong>Idade:</strong> <?= htmlspecialchars($animal['idade_valor'] . ' ' . $animal['idade_tipo']) ?></li>
                            <li class="list-group-item px-0"><strong>Porte:</strong> <?= htmlspecialchars($animal['porte']) ?></li>
                        </ul>
                        
                        <div class="mb-4">
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

                        <p class="card-text text-secondary border-top pt-3"><?= nl2br(htmlspecialchars($animal['descricao'])) ?></p>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white border-0 py-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-chat-dots"></i> Tenho Interesse em Adotar</h5>
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
                            
                            <?php if (!empty($mensagem_erro)): ?>
                                <div class="alert alert-danger border-0 shadow-sm mb-3">
                                    <i class="bi bi-x-circle-fill me-2"></i> <?= $mensagem_erro ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!isset($_SESSION['adotante_id'])): ?>
                                <div class="alert alert-warning text-center border-0 shadow-sm">
                                    <i class="bi bi-exclamation-triangle-fill fs-4 d-block mb-2 text-warning"></i>
                                    <h6 class="fw-bold">Você precisa ter uma conta para demonstrar interesse.</h6>
                                    <p class="small text-muted mb-3">Isso garante a segurança do processo de adoção e o bem-estar do animal.</p>
                                    <div class="mt-2">
                                        <a href="login_adotante.php" class="btn btn-sm btn-primary fw-bold px-4">Fazer Login</a> 
                                        <span class="mx-2 text-muted">ou</span>
                                        <a href="cadastro_adotante.php" class="btn btn-sm btn-outline-primary fw-bold px-4">Criar Conta</a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Seu Nome *</label>
                                        <input type="text" name="nome" class="form-control" required value="<?= htmlspecialchars($_SESSION['adotante_nome']) ?>">
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
                            
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const carousel = document.getElementById('carrosselFotos');
        const ambientBg = document.getElementById('ambient-bg');

        function atualizarCorFundo() {
            const activeItem = carousel.querySelector('.carousel-item.active');
            if (activeItem && activeItem.hasAttribute('data-img-src')) {
                const imgSrc = activeItem.getAttribute('data-img-src');
                ambientBg.style.backgroundImage = `url('${imgSrc}')`;
                ambientBg.style.backgroundSize = 'cover';
                ambientBg.style.backgroundPosition = 'center';
                ambientBg.style.opacity = '0.15';
            }
        }

        carousel.addEventListener('slid.bs.carousel', atualizarCorFundo);
        window.addEventListener('load', atualizarCorFundo);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>