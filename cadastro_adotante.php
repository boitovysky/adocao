<?php
require_once 'conexao.php';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $telefone = trim($_POST['telefone']);
    $cidade = trim($_POST['cidade']);
    $uf = trim($_POST['uf']);
    $moradia = $_POST['moradia'];
    
    $tem_quintal = $_POST['tem_quintal_cercado'] ?? 'Nao';
    $tem_rede = $_POST['tem_rede_janela'] ?? 'Nao';
    $teve_animais = $_POST['ja_teve_animais'] ?? 'Nao';

    try {
        $sql = "INSERT INTO adotantes (nome, email, senha, telefone, cidade, uf, moradia, tem_quintal_cercado, tem_rede_janela, ja_teve_animais) 
                VALUES (:nome, :email, :senha, :telefone, :cidade, :uf, :moradia, :quintal, :rede, :teve_animais)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $nome, ':email' => $email, ':senha' => $senha,
            ':telefone' => $telefone, ':cidade' => $cidade, ':uf' => $uf,
            ':moradia' => $moradia, ':quintal' => $tem_quintal, ':rede' => $tem_rede, ':teve_animais' => $teve_animais
        ]);
        $msg = "<div class='alert alert-success border-0 shadow-sm'>Cadastro realizado! <a href='login_adotante.php' class='fw-bold text-success'>Faça login aqui</a>.</div>";
    } catch (PDOException $e) {
        $msg = "<div class='alert alert-danger border-0 shadow-sm'>Erro ao cadastrar: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Adotante - Adote um Amigo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="py-5">
    <div class="container">
        <div class="card shadow-sm border-0 mx-auto" style="max-width: 650px;">
            <div class="card-body p-4">
                <h3 class="text-center fw-bold mb-4">Criar Conta para Adotar</h3>
                <?= $msg ?>
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label fw-bold">Nome Completo *</label><input type="text" name="nome" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">E-mail *</label><input type="email" name="email" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label fw-bold">Senha *</label><input type="password" name="senha" class="form-control" required minlength="6"></div>
                        <div class="col-md-12"><label class="form-label fw-bold">WhatsApp *</label><input type="text" name="telefone" class="form-control" required></div>
                        
                        <div class="col-md-3">
                            <label class="form-label fw-bold">UF *</label>
                            <select id="uf" name="uf" class="form-select" required>
                                <option value="">...</option>
                            </select>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label fw-bold">Cidade *</label>
                            <select id="cidade" name="cidade" class="form-select" required>
                                <option value="">Selecione a UF...</option>
                            </select>
                        </div>
                        
                        <div class="col-12 mt-4">
                            <label class="fw-bold text-secondary mb-2">Informações para Adoção Responsável</label>
                            <div class="bg-light p-3 rounded border">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Onde você mora? *</label>
                                    <select name="moradia" id="moradia" class="form-select" required>
                                        <option value="">Selecione...</option>
                                        <option value="Casa">Casa</option>
                                        <option value="Apartamento">Apartamento</option>
                                        <option value="Chácara/Sítio">Chácara/Sítio</option>
                                    </select>
                                </div>
                                
                                <div id="opcoes-moradia"></div>
                                
                                <div class="form-check mt-3 border-top pt-3">
                                    <input class="form-check-input" type="checkbox" name="ja_teve_animais" value="Sim" id="ja_teve">
                                    <label class="form-check-label" for="ja_teve">Já tive ou tenho outros animais</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="lgpd" id="lgpd" required>
                                <label class="form-check-label small text-muted" for="lgpd">
                                    Li e concordo com a <a href="#" data-bs-toggle="modal" data-bs-target="#modalLGPD">Política de Privacidade</a> e autorizo o uso dos meus dados para o processo de adoção.
                                </label>
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary w-100 py-2">Cadastrar</button>
                        </div>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <a href="login_adotante.php" class="text-decoration-none text-muted small">Já tenho conta. Fazer Login</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalLGPD" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-0">
                    <h5 class="modal-title fw-bold">Política de Privacidade</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">Seus dados (Nome, E-mail, Telefone) são coletados exclusivamente para viabilizar o processo de adoção responsável. Sua senha é criptografada (Bcrypt) e não pode ser vista nem por administradores. Suas informações de moradia são usadas para avaliar a compatibilidade com o animal. Você pode solicitar a exclusão dos seus dados a qualquer momento pelo painel.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        fetch('https://servicodados.ibge.gov.br/api/v1/localidades/estados')
            .then(res => res.json())
            .then(data => {
                const ufSelect = document.getElementById('uf');
                ufSelect.innerHTML = '<option value="">Selecione...</option>';
                data.sort((a, b) => a.sigla.localeCompare(b.sigla));
                data.forEach(estado => {
                    ufSelect.innerHTML += `<option value="${estado.sigla}">${estado.sigla}</option>`;
                });
            });

        document.getElementById('uf').addEventListener('change', function() {
            const uf = this.value;
            const cidadeSelect = document.getElementById('cidade');
            if (!uf) {
                cidadeSelect.innerHTML = '<option value="">Selecione a UF...</option>';
                return;
            }
            cidadeSelect.innerHTML = '<option value="">Carregando...</option>';
            fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${uf}/municipios`)
                .then(res => res.json())
                .then(cidades => {
                    cidadeSelect.innerHTML = '<option value="">Selecione...</option>';
                    cidades.forEach(cidade => {
                        cidadeSelect.innerHTML += `<option value="${cidade.nome}">${cidade.nome}</option>`;
                    });
                });
        });

        const selectMoradia = document.getElementById('moradia');
        const containerOpcoes = document.getElementById('opcoes-moradia');

        const templates = {
            'Casa': `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="tem_quintal_cercado" value="Sim" id="quintal">
                    <label class="form-check-label" for="quintal">Minha casa possui quintal cercado</label>
                </div>
            `,
            'Apartamento': `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="tem_rede_janela" value="Sim" id="rede">
                    <label class="form-check-label" for="rede">Meu apartamento tem rede nas janelas</label>
                </div>
            `,
            'Chácara/Sítio': `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="tem_quintal_cercado" value="Sim" id="quintal">
                    <label class="form-check-label" for="quintal">Minha Chácara/Sítio possui quintal cercado</label>
                </div>
            `
        };

        selectMoradia.addEventListener('change', function() {
            const valor = this.value;
            containerOpcoes.innerHTML = '';
            if (templates[valor]) {
                containerOpcoes.innerHTML = templates[valor];
            }
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>