<?php
session_start();
require_once 'conexao.php';

// Protege a página: se não estiver logado, chuta para o login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Lógica para EXCLUIR um pet
if (isset($_GET['excluir'])) {
    $id = (int)$_GET['excluir'];
    
    // Busca as fotos para apagar os arquivos físicos da pasta
    $stmt = $pdo->prepare("SELECT foto FROM animais WHERE id = ?");
    $stmt->execute([$id]);
    $pet = $stmt->fetch();
    
    if ($pet && $pet['foto']) {
        foreach (explode(',', $pet['foto']) as $foto) {
            $caminho = 'uploads/' . trim($foto);
            if (file_exists($caminho)) {
                unlink($caminho); // Apaga a imagem do servidor
            }
        }
    }
    
    // Deleta do banco (O ON DELETE CASCADE apaga os interessados dele também)
    $stmt = $pdo->prepare("DELETE FROM animais WHERE id = ?");
    $stmt->execute([$id]);
    
    header('Location: admin.php');
    exit;
}

// Busca todos os animais cadastrados
$animais = $pdo->query("SELECT * FROM animais ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; } </style>
</head>
<body>

    <!-- Navbar Admin -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="admin.php"><i class="bi bi-speedometer2"></i> Painel de Controle</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Olá, <?= $_SESSION['admin_nome'] ?></span>
                <a href="index.php" class="btn btn-outline-light btn-sm me-2">Ver Site</a>
                <a href="logout.php" class="btn btn-danger btn-sm">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Meus Pets Cadastrados</h4>
            <a href="cadastrar_animal.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Cadastrar Novo Pet</a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Pet</th>
                            <th>Cidade</th>
                            <th>WhatsApp</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($animais) > 0): ?>
                            <?php foreach ($animais as $animal): ?>
                                <tr>
                                    <td class="ps-4"><?= $animal['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($animal['nome']) ?></strong> <br>
                                        <small class="text-muted"><?= $animal['especie'] ?> • <?= $animal['raca'] ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($animal['cidade']) ?> - <?= $animal['uf'] ?></td>
                                    <td><?= htmlspecialchars($animal['whatsapp']) ?></td>
                                    <td><span class="badge bg-success"><?= $animal['status'] ?></span></td>
                                    <td class="text-end pe-4">
                                        <a href="detalhes.php?id=<?= $animal['id'] ?>" class="btn btn-sm btn-outline-info" title="Ver no site"><i class="bi bi-eye"></i></a>
                                        <a href="admin.php?excluir=<?= $animal['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza? As fotos e contatos serão apagados.')" title="Excluir"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Nenhum pet cadastrado ainda.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>