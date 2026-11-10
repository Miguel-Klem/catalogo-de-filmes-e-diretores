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
    <title>Cadastrar / Gerenciar Filmes</title>
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
    <h2>Cadastrar e Gerenciar Filmes</h2>

    <!-- Formulário em Grade -->
    <div class="card my-4 p-4 shadow-sm">
        <h5 class="card-title mb-3">Cadastrar / Editar Filme</h5>
        <form class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Diretor</label>
                <select class="form-select" required>
                    <option value="">Selecione o Diretor...</option>
                    <option value="1">Christopher Nolan</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Título do Filme</label>
                <input type="text" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Ano de Lançamento / Estúdio</label>
                <input type="text" class="form-control" placeholder="Ex: 2014 / Warner Bros.">
            </div>
            <div class="col-md-4">
                <label class="form-label">Gênero</label>
                <input type="text" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Link do Trailer (YouTube)</label>
                <input type="url" class="form-control" placeholder="https://youtube.com/watch?v=...">
            </div>
            <div class="col-md-4">
                <label class="form-label">URL do Pôster / Capa</label>
                <input type="url" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label">Sinopse</label>
                <textarea class="form-control" rows="4" placeholder="Insira o resumo/sinopse do filme aqui..."></textarea>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-success">Salvar Filme</button>
            </div>
        </form>
    </div>

    <!-- Tabela de Filmes -->
    <h4 class="mt-5 mb-3">Filmes do Diretor Selecionado</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Pôster</th>
                    <th>Título</th>
                    <th>Ano / Estúdio</th>
                    <th>Gênero</th>
                    <th>Trailer</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><img src="https://via.placeholder.com/50" alt="Capa" class="rounded" width="50" height="50"></td>
                    <td>Interstellar</td>
                    <td>2014 / Warner Bros.</td>
                    <td>Ficção Científica</td>
                    <td>
                        <a href="https://www.youtube.com/watch?v=zSWdZVtXT7E" target="_blank" class="btn btn-sm btn-danger">Assistir Trailer 🎬</a>
                    </td>
                    <td>
                        <a href="#" class="btn btn-sm btn-warning">Editar</a>
                        <a href="#" class="btn btn-sm btn-outline-danger">Excluir</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>