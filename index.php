<?php
/*
 * ARQUIVO: index.php
 * FUNÇÃO: menu principal (primeira página depois do login)
 */
include('valida_sessao.php');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Principal — Cineflix</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27><text y=%27.9em%27 font-size=%2790%27>🎬</text></svg>">
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<div class="conteudo">

    <div class="barra-topo">
        <a href="logout.php" class="botao-voltar">🚪 Sair</a>
    </div>

    <div class="cabecalho-pagina">
        <h1>🎬 CINEFLIX</h1>
        <!-- htmlspecialchars protege contra XSS ao mostrar o nome -->
        <p>Olá, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong>! O que deseja fazer?</p>
    </div>

    <div class="grade-menu">

        <div class="cartao-menu">
            <a href="cadastro_diretor.php">
                <div class="icone-menu">🎥</div>
                <div class="titulo-menu">Gerenciar Diretores</div>
                <div class="descricao-menu">Cadastre e edite diretores</div>
            </a>
        </div>

        <div class="cartao-menu">
            <a href="cadastro_filme.php">
                <div class="icone-menu">🎞️</div>
                <div class="titulo-menu">Cadastrar Filmes</div>
                <div class="descricao-menu">Adicione filmes ao sistema</div>
            </a>
        </div>

        <div class="cartao-menu">
            <a href="biblioteca.php">
                <div class="icone-menu">🍿</div>
                <div class="titulo-menu">Biblioteca de Filmes</div>
                <div class="descricao-menu">Veja e filtre todos os filmes</div>
            </a>
        </div>

    </div>

</div>
</body>
</html>
