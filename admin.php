<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    
    $stmt = $pdo->prepare("SELECT foto FROM animais WHERE id = ?");
    $stmt->execute([$id]);
    $pet = $stmt->fetch();
    
    if ($pet && $pet['foto']) {
        foreach (explode(',', $pet['foto']) as $foto) {
            $caminho = 'uploads/' . trim($foto);
            if (file_exists($caminho)) {
                unlink($caminho);
            }
        }
    }
    
    $stmt = $pdo->prepare("DELETE FROM animais WHERE id = ?");
    $stmt->execute([$id]);
    
    header('Location: admin.php');
    exit;
}

$animais = $pdo->query("SELECT * FROM animais ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Adote um Amigo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="py-4">

    <nav class="navbar navbar-expand-lg navbar-light mb-4">
        <div class="container">
            <a class="navbar-brand" href="admin.php"><i class="bi bi-speedometer2"></i> Painel de Controle</a>
            <div class="d-flex align-items-center">
                <span class="text-secondary me-3 d-none d-md-inline small">Olá, <strong><?= $_SESSION['admin_nome'] ?></strong></span>
                <a href="index.php" class="btn btn-outline-secondary btn-sm me-2" target="_blank"><i class="bi bi-box-arrow-up-right"></i> Ver Site</a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Sair</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <h3 class="fw-bold mb-0 text-dark">Meus Pets Cadastrados</h3>
            
            <div class="d-flex flex-wrap gap-2">
                <a href="admin_adotantes.php" class="btn btn-outline-secondary">
                    <i class="bi bi-people-fill"></i> Ver Adotantes
                </a>
                <a href="admin_interessados.php" class="btn btn-outline-warning text-dark border-warning">
                    <i class="bi bi-bell-fill"></i> Propostas de Adoção
                </a>
                <a href="cadastrar_animal.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Cadastrar Novo Pet
                </a>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3 border-0">ID</th>
                                <th class="py-3 border-0">Pet</th>
                                <th class="py-3 border-0">Localização</th>
                                <th class="py-3 border-0">WhatsApp</th>
                                <th class="py-3 border-0">Status</th>
                                <th class="text-end pe-4 py-3 border-0">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($animais) > 0): ?>
                                <?php foreach ($animais as $animal): ?>
                                    <tr>
                                        <td class="ps-4 text-muted">#<?= $animal['id'] ?></td>
                                        <td>
                                            <strong class="text-dark"><?= htmlspecialchars($animal['nome']) ?></strong> <br>
                                            <small class="text-muted"><?= htmlspecialchars($animal['especie']) ?> • <?= htmlspecialchars($animal['raca']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($animal['cidade']) ?> - <?= htmlspecialchars($animal['uf']) ?></td>
                                        <td><?= htmlspecialchars($animal['whatsapp']) ?></td>
                                        <td><span class="badge bg-success bg-opacity-75 rounded-pill px-3 py-2"><?= htmlspecialchars($animal['status']) ?></span></td>
                                        <td class="text-end pe-4">
                                            <a href="detalhes.php?id=<?= $animal['id'] ?>" class="btn btn-sm btn-outline-info btn-action" title="Ver no site" target="_blank">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="editar_animal.php?id=<?= $animal['id'] ?>" class="btn btn-sm btn-outline-primary btn-action" title="Editar Pet">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="admin.php?excluir=<?= $animal['id'] ?>" class="btn btn-sm btn-outline-danger btn-action" onclick="return confirm('Tem certeza que deseja excluir o pet <?= htmlspecialchars($animal['nome']) ?>? As fotos e contatos serão apagados permanentemente.')" title="Excluir Pet">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-folder-x fs-1 d-block mb-3 text-secondary"></i>
                                        Nenhum pet cadastrado ainda.<br>
                                        Clique em "Cadastrar Novo Pet" para começar.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>