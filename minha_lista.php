<?php
// minha_lista.php - filmes que o usuário colocou na lista dele
include('valida_sessao.php');
include('conexao.php');
include('funcoes.php');

$usuario_id = intval($_SESSION['usuario_id']);

// tirar um filme da lista
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $filme_id = intval($_POST['filme_id'] ?? 0);
    $comando = $conexao->prepare("DELETE FROM favoritos WHERE filme_id = ? AND usuario_id = ?");
    $comando->bind_param("ii", $filme_id, $usuario_id);
    $comando->execute();
    $comando->close();
}

$comando = $conexao->prepare("SELECT f.*, d.nome AS diretor_nome
                              FROM favoritos x
                              JOIN filmes f ON x.filme_id = f.id
                              JOIN diretores d ON f.diretor_id = d.id
                              WHERE x.usuario_id = ?
                              ORDER BY x.id DESC");
$comando->bind_param("i", $usuario_id);
$comando->execute();
$filmes = $comando->get_result();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha lista — Cineflix</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27><rect width=%27100%27 height=%27100%27 rx=%2718%27 fill=%27%23e50914%27/><text x=%2750%27 y=%2772%27 font-size=%2770%27 text-anchor=%27middle%27 fill=%27white%27 font-family=%27Arial%27 font-weight=%27bold%27>C</text></svg>">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php $pagina_atual = 'minhalista'; include('menu.php'); ?>

<div class="conteudo">

    <div class="barra-pagina">
        <h2>Minha lista</h2>
    </div>

    <?php if ($filmes->num_rows > 0): ?>
        <div class="grade-filmes">
            <?php while ($filme = $filmes->fetch_assoc()): ?>
                <?php
                // botão de tirar da lista (um formulário pequeno dentro do cartão)
                $botao = '<form method="post" action="" class="form-simples">'
                       . '<input type="hidden" name="filme_id" value="' . $filme['id'] . '">'
                       . '<button type="submit" class="botao-excluir">Tirar da lista</button></form>';
                mostrarCartaoFilme($conexao, $filme, $botao);
                ?>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="vazio">
            <p>Sua lista está vazia. Abra um filme e clique em “Minha lista”.</p>
            <a href="biblioteca.php" class="botao-principal">Ir para a biblioteca</a>
        </div>
    <?php endif; ?>

</div>
<?php include('rodape.php'); ?>
</body>
</html>
