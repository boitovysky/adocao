<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM adotantes WHERE id = :id");
$stmt->execute([':id' => $id]);
$adotante = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$adotante) { die("Adotante não encontrado."); }

$sql_hist = "SELECT a.nome, a.especie, a.raca, h.data_adocao, h.status_confirmado 
             FROM historico_adocoes h 
             JOIN animais a ON h.animal_id = a.id 
             WHERE h.adotante_id = :id 
             ORDER BY h.data_adocao DESC";
$stmt_hist = $pdo->prepare($sql_hist);
$stmt_hist->execute([':id' => $id]);
$historico = $stmt_hist->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Adotante - Adote um Amigo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="py-4">
    <nav class="navbar navbar-expand-lg navbar-light mb-4">
        <div class="container">
            <a class="navbar-brand" href="admin_adotantes.php"><i class="bi bi-arrow-left"></i> Voltar para Lista</a>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row g-4">
            
            <div class="col-md-5">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white py-3 border-0">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-person-vcard me-2"></i> Perfil do Adotante</h5>
                    </div>
                    <div class="card-body p-4">
                        <h3 class="fw-bold mb-1"><?= htmlspecialchars($adotante['nome']) ?></h3>
                        <p class="text-muted mb-4"><i class="bi bi-geo-alt-fill text-warning"></i> <?= htmlspecialchars($adotante['cidade']) ?> - <?= htmlspecialchars($adotante['uf']) ?></p>
                        
                        <ul class="list-group list-group-flush mb-0">
                            <li class="list-group-item px-0"><strong><i class="bi bi-envelope me-2 text-muted"></i>E-mail:</strong> <?= htmlspecialchars($adotante['email']) ?></li>
                            <li class="list-group-item px-0"><strong><i class="bi bi-whatsapp me-2 text-success"></i>WhatsApp:</strong> <?= htmlspecialchars($adotante['telefone']) ?></li>
                            <li class="list-group-item px-0"><strong><i class="bi bi-calendar-event me-2 text-muted"></i>Cadastrado em:</strong> <?= date('d/m/Y', strtotime($adotante['data_cadastro'])) ?></li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-secondary text-white py-3 border-0">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-house-door me-2"></i> Informações de Moradia</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="mb-2"><strong>Onde mora:</strong> <?= htmlspecialchars($adotante['moradia']) ?></p>
                        
                        <?php if($adotante['moradia'] == 'Apartamento'): ?>
                            <p class="mb-2"><strong>Rede nas janelas:</strong> 
                                <?= $adotante['tem_rede_janela'] == 'Sim' ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-danger">Não</span>' ?>
                            </p>
                        <?php else: ?>
                            <p class="mb-2"><strong>Quintal cercado:</strong> 
                                <?= $adotante['tem_quintal_cercado'] == 'Sim' ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-danger">Não</span>' ?>
                            </p>
                        <?php endif; ?>
                        
                        <p class="mb-0"><strong>Já teve animais:</strong> 
                            <?= $adotante['ja_teve_animais'] == 'Sim' ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-secondary">Não</span>' ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-clock-history text-warning"></i> Histórico de Adoções</h5>
                        <span class="badge bg-dark rounded-pill px-3 py-2"><?= count($historico) ?> Registros</span>
                    </div>
                    <div class="card-body p-4 bg-light rounded-bottom">
                        <?php if (count($historico) > 0): ?>
                            <?php foreach ($historico as $h): ?>
                                <div class="card mb-3 border-0 shadow-sm border-start border-4 border-<?= $h['status_confirmado'] == 'Confirmado' ? 'success' : 'warning' ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="fw-bold mb-0 fs-5"><?= htmlspecialchars($h['nome']) ?> <span class="text-muted fs-6">(<?= htmlspecialchars($h['especie']) ?>)</span></h6>
                                            
                                            <?php if ($h['status_confirmado'] == 'Confirmado'): ?>
                                                <span class="badge bg-success rounded-pill px-3"><i class="bi bi-check-circle-fill me-1"></i> Adotado dia <?= date('d/m/Y', strtotime($h['data_adocao'])) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark rounded-pill px-3"><i class="bi bi-hourglass-split me-1"></i> Pendente</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-muted small mb-0"><strong>Raça:</strong> <?= htmlspecialchars($h['raca']) ?> &bull; <strong>Data do Registro:</strong> <?= date('d/m/Y', strtotime($h['data_adocao'])) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-folder-x fs-1 text-muted d-block mb-3" style="opacity: 0.5;"></i>
                                <h6 class="text-muted fw-bold">Nenhum histórico registrado.</h6>
                                <p class="small text-muted">Este usuário ainda não demonstrou interesse em nenhum pet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>