<?php
require_once 'conexao.php';
$msg = '';
$etapa = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao']) && $_POST['acao'] == 'enviar_email') {
        $email = $_POST['email'];
        $stmt = $pdo->prepare("SELECT id FROM adotantes WHERE email = :email");
        $stmt->execute([':email' => $email]);
        
        if ($stmt->rowCount() > 0) {
            $codigo = rand(100000, 999999);
            $update = $pdo->prepare("UPDATE adotantes SET token_senha = :codigo WHERE email = :email");
            $update->execute([':codigo' => $codigo, ':email' => $email]);
            
            $msg = "<div class='alert alert-info border-0 shadow-sm'>Código gerado! Em um sistema real, ele seria enviado para seu e-mail.<br><br>Para teste, use o código: <strong>$codigo</strong></div>";
            $etapa = 2;
        } else {
            $msg = "<div class='alert alert-danger border-0 shadow-sm'>E-mail não encontrado em nossa base.</div>";
        }
    } elseif (isset($_POST['acao']) && $_POST['acao'] == 'redefinir') {
        $email = $_POST['email'];
        $codigo = $_POST['codigo'];
        $nova_senha = password_hash($_POST['nova_senha'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("SELECT id FROM adotantes WHERE email = :email AND token_senha = :codigo");
        $stmt->execute([':email' => $email, ':codigo' => $codigo]);
        
        if ($stmt->rowCount() > 0) {
            $update = $pdo->prepare("UPDATE adotantes SET senha = :senha, token_senha = NULL WHERE email = :email");
            $update->execute([':senha' => $nova_senha, ':email' => $email]);
            $msg = "<div class='alert alert-success border-0 shadow-sm'>Senha redefinida com sucesso! <a href='login_adotante.php' class='fw-bold text-success'>Faça login aqui</a>.</div>";
            $etapa = 3;
        } else {
            $msg = "<div class='alert alert-danger border-0 shadow-sm'>Código inválido.</div>";
            $etapa = 2;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Adote um Amigo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh; background-color: var(--cinza-fundo);">
    <div class="container">
        <div class="card shadow-sm border-0 mx-auto" style="max-width: 450px;">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-key-fill text-warning" style="font-size: 3rem;"></i>
                    <h4 class="text-center fw-bold mt-2">Recuperar Senha</h4>
                </div>
                
                <?= $msg ?>
                
                <?php if ($etapa == 1): ?>
                    <p class="text-muted small text-center mb-4">Digite seu e-mail cadastrado para receber um código de verificação.</p>
                    <form method="POST">
                        <input type="hidden" name="acao" value="enviar_email">
                        <div class="mb-4">
                            <label class="form-label fw-bold">E-mail</label>
                            <input type="email" name="email" class="form-control" required placeholder="seu@email.com">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Gerar Código</button>
                    </form>
                
                <?php elseif ($etapa == 2): ?>
                    <p class="text-muted small text-center mb-4">Insira o código de 6 dígitos e crie sua nova senha.</p>
                    <form method="POST">
                        <input type="hidden" name="acao" value="redefinir">
                        <input type="hidden" name="email" value="<?= $_POST['email'] ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Código de 6 dígitos</label>
                            <input type="text" name="codigo" class="form-control" required placeholder="000000">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Nova Senha</label>
                            <input type="password" name="nova_senha" class="form-control" required minlength="6" placeholder="••••••••">
                        </div>
                        <button type="submit" class="btn btn-success w-100 py-2 fw-bold">Redefinir Senha</button>
                    </form>
                <?php endif; ?>
                
                <div class="text-center mt-4 border-top pt-3">
                    <a href="login_adotante.php" class="text-muted small text-decoration-none"><i class="bi bi-arrow-left"></i> Voltar para o Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>