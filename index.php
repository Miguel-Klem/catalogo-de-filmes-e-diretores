<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal - Possivel nome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Barra Superior -->
<nav class="navbar navbar-dark bg-dark px-4">
    <span class="navbar-brand">Vai ter um nome ai</span>
    <div class="d-flex align-items-center gap-3">
        <span class="text-white">Olá, <strong><?= htmlspecialchars($_SESSION['usuario']) ?></strong>!</span>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Sair</a>
    </div>
</nav>

<!-- Grade de Navegação -->
<div class="container my-5">
    <h2 class="mb-4">Painel Principal</h2>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 text-center shadow-sm">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <h4 class="card-title">Gerenciar Diretores</h4>
                    <p class="card-text text-muted">Cadastre e edite os Diretores cadastrados no sistema.</p>
                    <a href="diretores.php" class="btn btn-primary mt-auto">Acessar</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 text-center shadow-sm">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <h4 class="card-title">Cadastrar Filmes</h4>
                    <p class="card-text text-muted">Adicione novos filmes, links do YouTube para trailers.</p>
                    <a href="filmes.php" class="btn btn-primary mt-auto">Acessar</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 text-center shadow-sm">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <h4 class="card-title">Biblioteca</h4>
                    <p class="card-text text-muted">Consulte e filtre todas os filmes do acervo.</p>
                    <a href="bibilioteca.php" class="btn btn-primary mt-auto">Acessar</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>