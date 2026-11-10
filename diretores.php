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
    <title>Gerenciar Diretores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-dark bg-dark px-4">
    <a class="navbar-brand" href="index.php">← Voltar ao Início</a>
    <div class="d-flex align-items-center gap-3">
        <span class="text-white">Olá, <strong><?= htmlspecialchars($_SESSION['usuario']) ?></strong>!</span>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Sair</a>
    </div>
</nav>

<div class="container my-5">
    <h2>Gerenciar Diretores</h2>
    
    <!-- Formulário em Grade -->
    <div class="card my-4 p-4 shadow-sm">
        <h5 class="card-title mb-3">Cadastrar / Editar Diretor</h5>
        <form class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nome do Diretor</label>
                <input type="text" class="form-control" placeholder="Ex: Christopher Nolan" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Estilo / Gênero Predominante</label>
                <input type="text" class="form-control" placeholder="Ex: Ficção Científica / Drama" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">País de Origem</label>
                <input type="text" class="form-control" placeholder="Ex: Reino Unido" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">URL da Foto</label>
                <input type="url" class="form-control" placeholder="https://link-da-imagem.jpg">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-success">Salvar Diretor</button>
            </div>
        </form>
    </div>

    <!-- Tabela de Diretores -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Foto</th>
                    <th>Nome</th>
                    <th>Estilo / Gênero</th>
                    <th>País</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><img src="https://via.placeholder.com/50" alt="Foto" class="rounded" width="50" height="50"></td>
                    <td>Christopher Nolan</td>
                    <td>Ficção Científica</td>
                    <td>Reino Unido</td>
                    <td>
                        <a href="#" class="btn btn-sm btn-warning">Editar</a>
                        <a href="#" class="btn btn-sm btn-danger">Excluir</a>
                        <a href="filmes.php?diretor_id=1" class="btn btn-sm btn-info text-white">Ver Filmes</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>