<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['adotante_id'])) {
    header('Location: login_adotante.php');
    exit;
}

$id = $_SESSION['adotante_id'];
$msg = '';

$stmt = $pdo->prepare("SELECT * FROM adotantes WHERE id = :id");
$stmt->execute([':id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telefone = $_POST['telefone'];
    $cidade = $_POST['cidade'];
    $uf = $_POST['uf'];
    $moradia = $_POST['moradia'];
    
    $quintal = $_POST['tem_quintal_cercado'] ?? 'Nao';
    $rede = $_POST['tem_rede_janela'] ?? 'Nao';
    $teve_animais = $_POST['ja_teve_animais'] ?? 'Nao';
    
    $sql_senha = "";
    $params = [
        ':telefone' => $telefone, ':cidade' => $cidade, ':uf' => $uf,
        ':moradia' => $moradia, ':quintal' => $quintal, ':rede' => $rede, ':teve_animais' => $teve_animais, ':id' => $id
    ];

    if (!empty($_POST['nova_senha'])) {
        $sql_senha = ", senha = :senha";
        $params[':senha'] = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);
    }

    $sql = "UPDATE adotantes SET telefone = :telefone, cidade = :cidade, uf = :uf, moradia = :moradia, tem_quintal_cercado = :quintal, tem_rede_janela = :rede, ja_teve_animais = :teve_animais $sql_senha WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $stmt = $pdo->prepare("SELECT * FROM adotantes WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $msg = "<div class='alert alert-success border-0 shadow-sm'><i class='bi bi-check-circle-fill me-2'></i> Dados atualizados com sucesso!</div>";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Adote um Amigo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="py-5">
    <div class="container">
        <div class="card shadow-sm border-0 mx-auto" style="max-width: 650px;">
            <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                <h4 class="fw-bold mb-0">Meu Perfil</h4>
                <a href="index.php" class="btn btn-outline-secondary btn-sm">Voltar para Home</a>
            </div>
            <div class="card-body p-4">
                <h6 class="text-muted mb-4">Olá, <span class="fw-bold text-dark"><?= htmlspecialchars($user['nome']) ?></span>. Mantenha seus dados atualizados.</h6>
                
                <?= $msg ?>
                
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">WhatsApp *</label>
                            <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($user['telefone']) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">UF *</label>
                            <input type="text" name="uf" class="form-control" maxlength="2" value="<?= htmlspecialchars($user['uf']) ?>" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Cidade *</label>
                            <input type="text" name="cidade" class="form-control" value="<?= htmlspecialchars($user['cidade']) ?>" required>
                        </div>
                        
                        <div class="col-12 mt-4">
                            <label class="fw-bold text-secondary mb-2">Informações de Moradia</label>
                            <div class="bg-light p-3 rounded border">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Onde você mora? *</label>
                                    <select name="moradia" id="moradia" class="form-select" required>
                                        <option value="Casa" <?= $user['moradia'] == 'Casa' ? 'selected' : '' ?>>Casa</option>
                                        <option value="Apartamento" <?= $user['moradia'] == 'Apartamento' ? 'selected' : '' ?>>Apartamento</option>
                                        <option value="Chácara/Sítio" <?= $user['moradia'] == 'Chácara/Sítio' ? 'selected' : '' ?>>Chácara/Sítio</option>
                                    </select>
                                </div>
                                
                                <div id="opcoes-moradia"></div>
                                
                                <div class="form-check mt-3 border-top pt-3">
                                    <input class="form-check-input" type="checkbox" name="ja_teve_animais" value="Sim" id="ja_teve" <?= $user['ja_teve_animais'] == 'Sim' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="ja_teve">Já tive ou tenho outros animais</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <label class="fw-bold text-secondary mb-2">Alterar Senha (Deixe em branco para não mudar)</label>
                            <input type="password" name="nova_senha" class="form-control" minlength="6" placeholder="Digite a nova senha">
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Salvar Alterações</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const selectMoradia = document.getElementById('moradia');
        const containerOpcoes = document.getElementById('opcoes-moradia');
        
        const quintalAtual = "<?= $user['tem_quintal_cercado'] ?>";
        const redeAtual = "<?= $user['tem_rede_janela'] ?>";
        const moradiaAtual = "<?= $user['moradia'] ?>";

        const templates = {
            'Casa': `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="tem_quintal_cercado" value="Sim" id="quintal" ${quintalAtual === 'Sim' ? 'checked' : ''}>
                    <label class="form-check-label" for="quintal">Minha casa possui quintal cercado</label>
                </div>
            `,
            'Apartamento': `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="tem_rede_janela" value="Sim" id="rede" ${redeAtual === 'Sim' ? 'checked' : ''}>
                    <label class="form-check-label" for="rede">Meu apartamento tem rede nas janelas</label>
                </div>
            `,
            'Chácara/Sítio': `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="tem_quintal_cercado" value="Sim" id="quintal" ${quintalAtual === 'Sim' ? 'checked' : ''}>
                    <label class="form-check-label" for="quintal">Minha Chácara/Sítio possui quintal cercado</label>
                </div>
            `
        };

        function renderizarOpcoes(valor) {
            containerOpcoes.innerHTML = '';
            if (templates[valor]) {
                containerOpcoes.innerHTML = templates[valor];
            }
        }

        renderizarOpcoes(moradiaAtual);

        selectMoradia.addEventListener('change', function() {
            renderizarOpcoes(this.value);
        });
    </script>
</body>
</html>