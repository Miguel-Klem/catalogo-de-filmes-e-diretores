<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$busca = $_GET['busca'] ?? '';
$diretor_filtro = $_GET['diretor'] ?? '';
$genero_filtro = $_GET['genero'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca de Filmes</title>
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
    <h2>Biblioteca de Filmes</h2>

    <!-- Barra de Filtros Combináveis -->
    <div class="card p-3 my-4 bg-light shadow-sm">
        <form method="GET" action="biblioteca.php" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Busca Geral</label>
                <input type="text" name="busca" class="form-control" placeholder="Título, estúdio..." value="<?= htmlspecialchars($busca) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Diretor</label>
                <select name="diretor" class="form-select">
                    <option value="">Todos os Diretores</option>
                    <option value="1" <?= $diretor_filtro === '1' ? 'selected' : '' ?>>Christopher Nolan</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Gênero</label>
                <select name="genero" class="form-select">
                    <option value="">Todos os Gêneros</option>
                    <option value="Ficção Científica" <?= $genero_filtro === 'Ficção Científica' ? 'selected' : '' ?>>Ficção Científica</option>
                    <option value="Ação" <?= $genero_filtro === 'Ação' ? 'selected' : '' ?>>Ação</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </form>
    </div>

    <!-- Tabela Geral -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Pôster</th>
                    <th>Título</th>
                    <th>Diretor</th>
                    <th>Ano / Estúdio</th>
                    <th>Gênero</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><img src="https://via.placeholder.com/50" alt="Capa" class="rounded" width="50" height="50"></td>
                    <td>Interstellar</td>
                    <td>Christopher Nolan</td>
                    <td>2014 / Warner Bros.</td>
                    <td>Ficção Científica</td>
                    <td>
                        <!-- Preserva os filtros ativos na URL -->
                        <a href="filmes.php?id=1&busca=<?= urlencode($busca) ?>&diretor=<?= urlencode($diretor_filtro) ?>&genero=<?= urlencode($genero_filtro) ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="excluir_filme.php?id=1&busca=<?= urlencode($busca) ?>&diretor=<?= urlencode($diretor_filtro) ?>&genero=<?= urlencode($genero_filtro) ?>" class="btn btn-sm btn-danger">Excluir</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>