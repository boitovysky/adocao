<?php
session_start();
require_once 'conexao.php';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM adotantes WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['adotante_id'] = $usuario['id'];
        $_SESSION['adotante_nome'] = $usuario['nome'];
        header('Location: index.php');
        exit;
    } else {
        $erro = "E-mail ou senha incorretos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login do Adotante - Adote um Amigo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh; background-color: var(--cinza-fundo);">
    <div class="container">
        <div class="card shadow-sm border-0 mx-auto" style="max-width: 450px;">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-person-heart text-warning" style="font-size: 3rem;"></i>
                    <h3 class="text-center fw-bold mt-2">Login do Adotante</h3>
                    <p class="text-muted small">Acesse sua conta para continuar</p>
                </div>

                <?php if($erro): ?>
                    <div class="alert alert-danger border-0 shadow-sm">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $erro ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">E-mail</label>
                        <input type="email" name="email" class="form-control" required placeholder="seu@email.com">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Senha</label>
                        <input type="password" name="senha" class="form-control" required placeholder="••••••••">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Entrar</button>
                    
                    <div class="text-center mt-3">
                        <a href="recuperar_senha.php" class="text-muted small text-decoration-none">Esqueci minha senha</a>
                    </div>
                    
                    <div class="text-center mt-4 border-top pt-3">
                        <span class="text-muted small">Não tem uma conta?</span><br>
                        <a href="cadastro_adotante.php" class="text-decoration-none fw-bold" style="color: var(--laranja-hover);">Criar conta agora</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>