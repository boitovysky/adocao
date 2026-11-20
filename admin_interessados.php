<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['confirmar'])) {
    $id_historico = (int)$_GET['confirmar'];
    
    $stmt = $pdo->prepare("SELECT animal_id FROM historico_adocoes WHERE id = ?");
    $stmt->execute([$id_historico]);
    $animal_id = $stmt->fetchColumn();
    
    if ($animal_id) {
        $pdo->prepare("UPDATE animais SET status = 'Adotado' WHERE id = ?")->execute([$animal_id]);
        $pdo->prepare("UPDATE historico_adocoes SET status_confirmado = 'Confirmado', data_confirmacao = NOW() WHERE id = ?")->execute([$id_historico]);
        
        header('Location: admin_interessados.php?msg=sucesso');
        exit;
    }
}

if (isset($_GET['reverter'])) {
    $id_historico = (int)$_GET['reverter'];
    
    $stmt = $pdo->prepare("SELECT animal_id FROM historico_adocoes WHERE id = ?");
    $stmt->execute([$id_historico]);
    $animal_id = $stmt->fetchColumn();
    
    if ($animal_id) {
        $pdo->prepare("UPDATE animais SET status = 'Disponível' WHERE id = ?")->execute([$animal_id]);
        $pdo->prepare("UPDATE historico_adocoes SET status_confirmado = 'Pendente' WHERE id = ?")->execute([$id_historico]);
        
        header('Location: admin_interessados.php?msg=revertido');
        exit;
    }
}

$sql = "SELECT i.id as id_interesse, i.nome, i.email, i.telefone, i.data_contato, i.mensagem, 
               a.id as animal_id, a.nome as animal_nome, a.status as animal_status,
               h.id as id_historico, h.status_confirmado
        FROM interessados i 
        JOIN animais a ON i.animal_id = a.id 
        LEFT JOIN historico_adocoes h ON h.animal_id = a.id AND h.adotante_id = (SELECT id FROM adotantes WHERE email = i.email LIMIT 1)
        ORDER BY i.data_contato DESC";
$interessados = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interessados na Adoção - Adote um Amigo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="py-4">
    <nav class="navbar navbar-expand-lg navbar-light mb-4">
        <div class="container">
            <a class="navbar-brand" href="admin.php"><i class="bi bi-arrow-left"></i> Voltar ao Painel</a>
        </div>
    </nav>

    <div class="container pb-5">
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-<?= $_GET['msg'] == 'sucesso' ? 'success' : 'warning' ?> alert-dismissible fade show border-0 shadow-sm">
                <i class="bi bi-info-circle-fill me-2"></i>
                <?= $_GET['msg'] == 'sucesso' ? 'Adoção confirmada! O animal foi marcado como Adotado.' : 'Adoção revertida! O animal voltou a ficar Disponível.' ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0 fw-bold text-dark"><i class="bi bi-bell text-warning me-2"></i> Propostas de Adoção</h3>
        </div>
        
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3 border-0">Animal</th>
                                <th class="py-3 border-0">Doador (Quem se interessou)</th>
                                <th class="py-3 border-0">Contato</th>
                                <th class="py-3 border-0">Data</th>
                                <th class="py-3 border-0">Mensagem</th>
                                <th class="text-end pe-4 py-3 border-0">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($interessados as $i): ?>
                            <tr>
                                <td class="ps-4">
                                    <strong class="text-dark"><?= htmlspecialchars($i['animal_nome']) ?></strong><br>
                                    <span class="badge bg-<?= $i['animal_status'] == 'Adotado' ? 'success' : 'primary' ?> rounded-pill mt-1"><?= $i['animal_status'] ?></span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($i['nome']) ?><br>
                                    <small class="text-muted"><?= htmlspecialchars($i['email']) ?></small>
                                </td>
                                <td><i class="bi bi-whatsapp text-success"></i> <?= htmlspecialchars($i['telefone']) ?></td>
                                <td><?= date('d/m/Y', strtotime($i['data_contato'])) ?></td>
                                <td style="max-width: 200px;">
                                    <?php if(!empty($i['mensagem'])): ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalMensagem<?= $i['id_interesse'] ?>">
                                            <i class="bi bi-eye"></i> Ver Motivo
                                        </button>
                                        
                                        <div class="modal fade" id="modalMensagem<?= $i['id_interesse'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header bg-light border-0">
                                                        <h5 class="modal-title fw-bold"><i class="bi bi-chat-quote text-warning"></i> Motivo da Adoção</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <p class="mb-0"><?= nl2br(htmlspecialchars($i['mensagem'])) ?></p>
                                                    </div>
                                                    <div class="modal-footer border-0 bg-light">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fechar</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">Não informado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <?php if($i['status_confirmado'] == 'Confirmado'): ?>
                                        <a href="admin_interessados.php?reverter=<?= $i['id_historico'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deseja reverter esta adoção? O animal voltará a ficar disponível.')">
                                            <i class="bi bi-arrow-counterclockwise"></i> Voltar Status
                                        </a>
                                    <?php else: ?>
                                        <?php if($i['id_historico']): ?>
                                            <a href="admin_interessados.php?confirmar=<?= $i['id_historico'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Confirmar que esta adoção foi realizada com sucesso?')">
                                                <i class="bi bi-check-circle"></i> Confirmar Adoção
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" disabled>Sem histórico</button>
                                        <?php endif; ?>
                                    <?php endif; ?>
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