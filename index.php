<?php
// index.php - página inicial
// boas-vindas, números do sistema (clicáveis) e os filmes mais aclamados pela crítica
include('valida_sessao.php');
include('conexao.php');
include('funcoes.php');

$id_usuario = intval($_SESSION['usuario_id']);

// números do sistema
$total_diretores = $conexao->query("SELECT COUNT(*) AS total FROM diretores")->fetch_assoc()['total'];
$total_filmes = $conexao->query("SELECT COUNT(*) AS total FROM filmes")->fetch_assoc()['total'];

$comando = $conexao->prepare("SELECT COUNT(*) AS total FROM favoritos WHERE usuario_id = ?");
$comando->bind_param("i", $id_usuario);
$comando->execute();
$total_lista = $comando->get_result()->fetch_assoc()['total'];
$comando->close();

// os 15 filmes aclamados pela crítica (já vêm cadastrados no banco, na ordem do ranking)
$aclamados = $conexao->query("SELECT f.*, d.nome AS diretor_nome
                              FROM filmes f
                              JOIN diretores d ON f.diretor_id = d.id
                              ORDER BY f.id ASC LIMIT 15");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Início — Cineflix</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27><rect width=%27100%27 height=%27100%27 rx=%2718%27 fill=%27%23e50914%27/><text x=%2750%27 y=%2772%27 font-size=%2770%27 text-anchor=%27middle%27 fill=%27white%27 font-family=%27Arial%27 font-weight=%27bold%27>C</text></svg>">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php $pagina_atual = 'inicio'; include('menu.php'); ?>

<div class="conteudo">

    <!-- faixa de boas-vindas -->
    <section class="destaque">
        <p class="destaque-pequeno">Bem-vindo de volta</p>
        <h1>Olá, <?php echo htmlspecialchars($_SESSION['usuario']); ?>!</h1>
        <p>Veja os números do sistema e os filmes mais aclamados pela crítica.</p>
    </section>

    <!-- números do sistema (cada um leva para a página da função) -->
    <section class="numeros">
        <a href="biblioteca.php" class="numero-cartao">
            <div class="numero-valor"><?php echo $total_filmes; ?></div>
            <div class="numero-texto">Filmes cadastrados</div>
        </a>
        <a href="cadastro_diretor.php" class="numero-cartao">
            <div class="numero-valor"><?php echo $total_diretores; ?></div>
            <div class="numero-texto">Diretores cadastrados</div>
        </a>
        <a href="minha_lista.php" class="numero-cartao">
            <div class="numero-valor"><?php echo $total_lista; ?></div>
            <div class="numero-texto">Filmes na sua lista</div>
        </a>
    </section>

    <!-- top 15: aparece em 3 linhas de 5 filmes -->
    <h2 class="titulo-secao">Aclamados pela crítica</h2>
    <?php if ($aclamados->num_rows > 0): ?>
        <div class="grade-top">
            <?php
            $posicao = 0;
            while ($filme = $aclamados->fetch_assoc()) {
                $posicao = $posicao + 1;
                mostrarCartaoFilme($conexao, $filme, '', $posicao);
            }
            ?>
        </div>
    <?php else: ?>
        <div class="vazio">
            <p>Ainda não há filmes cadastrados.</p>
            <a href="cadastro_filme.php" class="botao-principal">Cadastrar um filme</a>
        </div>
    <?php endif; ?>

</div>
<?php include('rodape.php'); ?>
</body>
</html>
