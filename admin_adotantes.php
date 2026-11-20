<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$sql = "SELECT a.*, COUNT(h.id) as total_adocoes 
        FROM adotantes a 
        LEFT JOIN historico_adocoes h ON a.id = h.adotante_id 
        GROUP BY a.id 
        ORDER BY total_adocoes DESC, a.nome ASC";
$adotantes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Adotantes - Adote um Amigo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="py-4">
    <nav class="navbar navbar-expand-lg navbar-light mb-4">
        <div class="container">
            <a class="navbar-brand" href="admin.php"><i class="bi bi-arrow-left"></i> Voltar ao Painel</a>
            <span class="navbar-text text-secondary fw-bold d-none d-md-inline">Gestão de Adotantes</span>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-people text-warning me-2"></i> Usuários Cadastrados para Adoção</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3 border-0">Nome</th>
                                <th class="py-3 border-0">E-mail / Contato</th>
                                <th class="py-3 border-0">Localização</th>
                                <th class="py-3 border-0">Histórico</th>
                                <th class="text-end pe-4 py-3 border-0">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($adotantes as $ad): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-dark"><?= htmlspecialchars($ad['nome']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($ad['email']) ?><br>
                                        <small class="text-muted"><i class="bi bi-whatsapp text-success"></i> <?= htmlspecialchars($ad['telefone']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($ad['cidade']) ?> - <?= htmlspecialchars($ad['uf']) ?></td>
                                    <td>
                                        <?php if ($ad['total_adocoes'] > 0): ?>
                                            <span class="badge bg-success rounded-pill px-3 py-2"><?= $ad['total_adocoes'] ?> adoção(ões)</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary rounded-pill px-3 py-2">Nenhuma ainda</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="ver_adotante.php?id=<?= $ad['id'] ?>" class="btn btn-sm btn-outline-primary">Ver Perfil Completo</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>