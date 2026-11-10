<?php
session_start();
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if ($usuario === 'admin' && $senha === '123') {
        $_SESSION['usuario'] = 'Administrador';
        header('Location: index.php');
        exit;
    } else if ($usuario !== 'admin') {
        $erro = 'Usuário não encontrado.';
    } else {
        $erro = 'Senha incorreta.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Música SENAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="card shadow p-4" style="max-width: 400px; width: 100%;">
    <h3 class="card-title text-center mb-4">Acesso ao Sistema</h3>
    
    <?php if ($erro): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="mb-3">
            <label for="usuario" class="form-label">Usuário</label>
            <input type="text" name="usuario" id="usuario" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="senha" class="form-label">Senha</label>
            <input type="password" name="senha" id="senha" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>

    <div class="alert alert-info mt-4 mb-0 text-center" role="alert">
        <small><strong>Dica de teste:</strong><br>Usuário: <code>admin</code> | Senha: <code>123</code></small>
    </div>
</div>

</body>
</html>