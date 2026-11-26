<?php
// filme.php - página de um filme
// mostra os dados, o trailer, a média das notas e deixa o usuário avaliar e favoritar
include('valida_sessao.php');
include('conexao.php');
include('funcoes.php');

$filme_id = intval($_GET['id'] ?? 0);
$usuario_id = intval($_SESSION['usuario_id']);
$mensagem = '';
$tipo_mensagem = '';

// -----------------------------------------------
// ações do formulário (avaliar, apagar avaliação, favoritar)
// -----------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao == 'avaliar') {
        $nota = intval($_POST['nota'] ?? 0);
        $comentario = trim($_POST['comentario'] ?? '');

        if ($nota < 1 || $nota > 5) {
            $mensagem = "Escolha uma nota de 1 a 5.";
            $tipo_mensagem = "erro";
        } else {
            // se o usuário já avaliou, atualiza; senão, cria
            $comando = $conexao->prepare("SELECT id FROM avaliacoes WHERE filme_id = ? AND usuario_id = ?");
            $comando->bind_param("ii", $filme_id, $usuario_id);
            $comando->execute();
            $ja_avaliou = $comando->get_result()->num_rows;
            $comando->close();

            if ($ja_avaliou > 0) {
                $comando = $conexao->prepare("UPDATE avaliacoes SET nota = ?, comentario = ? WHERE filme_id = ? AND usuario_id = ?");
                $comando->bind_param("isii", $nota, $comentario, $filme_id, $usuario_id);
            } else {
                $comando = $conexao->prepare("INSERT INTO avaliacoes (filme_id, usuario_id, nota, comentario) VALUES (?, ?, ?, ?)");
                $comando->bind_param("iiis", $filme_id, $usuario_id, $nota, $comentario);
            }
            $comando->execute();
            $comando->close();
            $mensagem = "Avaliação salva!";
            $tipo_mensagem = "sucesso";
        }
    }

    if ($acao == 'apagar_avaliacao') {
        $comando = $conexao->prepare("DELETE FROM avaliacoes WHERE filme_id = ? AND usuario_id = ?");
        $comando->bind_param("ii", $filme_id, $usuario_id);
        $comando->execute();
        $comando->close();
        $mensagem = "Sua avaliação foi apagada.";
        $tipo_mensagem = "sucesso";
    }

    if ($acao == 'favoritar') {
        // se já está na lista tira, se não está coloca
        $comando = $conexao->prepare("SELECT id FROM favoritos WHERE filme_id = ? AND usuario_id = ?");
        $comando->bind_param("ii", $filme_id, $usuario_id);
        $comando->execute();
        $esta_na_lista = $comando->get_result()->num_rows;
        $comando->close();

        if ($esta_na_lista > 0) {
            $comando = $conexao->prepare("DELETE FROM favoritos WHERE filme_id = ? AND usuario_id = ?");
            $mensagem = "Filme removido da sua lista.";
        } else {
            $comando = $conexao->prepare("INSERT INTO favoritos (filme_id, usuario_id) VALUES (?, ?)");
            $mensagem = "Filme adicionado na sua lista!";
        }
        $comando->bind_param("ii", $filme_id, $usuario_id);
        $comando->execute();
        $comando->close();
        $tipo_mensagem = "sucesso";
    }
}

if (isset($_GET['importado'])) {
    $mensagem = "Filme importado com sucesso!";
    $tipo_mensagem = "sucesso";
}

// -----------------------------------------------
// busca os dados da página
// -----------------------------------------------
$comando = $conexao->prepare("SELECT f.*, d.nome AS diretor_nome, d.id AS diretor_codigo
                              FROM filmes f
                              JOIN diretores d ON f.diretor_id = d.id
                              WHERE f.id = ?");
$comando->bind_param("i", $filme_id);
$comando->execute();
$filme = $comando->get_result()->fetch_assoc();
$comando->close();

if (!$filme) {
    header('Location: biblioteca.php');
    exit();
}

$avaliacao = mediaDoFilme($conexao, $filme_id);

// a avaliação do usuário logado (se tiver)
$comando = $conexao->prepare("SELECT nota, comentario FROM avaliacoes WHERE filme_id = ? AND usuario_id = ?");
$comando->bind_param("ii", $filme_id, $usuario_id);
$comando->execute();
$minha = $comando->get_result()->fetch_assoc();
$comando->close();

// está na lista do usuário?
$comando = $conexao->prepare("SELECT id FROM favoritos WHERE filme_id = ? AND usuario_id = ?");
$comando->bind_param("ii", $filme_id, $usuario_id);
$comando->execute();
$na_lista = $comando->get_result()->num_rows > 0;
$comando->close();

// todas as avaliações do filme
$comando = $conexao->prepare("SELECT a.nota, a.comentario, u.usuario AS usuario_nome
                              FROM avaliacoes a
                              JOIN usuarios u ON a.usuario_id = u.id
                              WHERE a.filme_id = ?
                              ORDER BY a.id DESC");
$comando->bind_param("i", $filme_id);
$comando->execute();
$avaliacoes = $comando->get_result();

$id_video = pegarIdYoutube($filme['trailer_url'] ?? '');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($filme['titulo']); ?> — Cineflix</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27><rect width=%27100%27 height=%27100%27 rx=%2718%27 fill=%27%23e50914%27/><text x=%2750%27 y=%2772%27 font-size=%2770%27 text-anchor=%27middle%27 fill=%27white%27 font-family=%27Arial%27 font-weight=%27bold%27>C</text></svg>">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php $pagina_atual = 'biblioteca'; include('menu.php'); ?>

<div class="conteudo">

    <?php if ($mensagem): ?>
        <div class="mensagem <?php echo $tipo_mensagem; ?>">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <!-- parte de cima: pôster e informações -->
    <section class="filme-topo">
        <div class="filme-poster">
            <?php if (!empty($filme['poster_url']) && file_exists($filme['poster_url'])): ?>
                <img src="<?php echo htmlspecialchars($filme['poster_url']); ?>" alt="Pôster de <?php echo htmlspecialchars($filme['titulo']); ?>">
            <?php else: ?>
                <div class="cartao-sem-poster grande"></div>
            <?php endif; ?>
        </div>

        <div class="filme-dados">
            <h1><?php echo htmlspecialchars($filme['titulo']); ?></h1>
            <p class="filme-linha">
                <?php echo htmlspecialchars($filme['ano_estudio']) ?: 'Ano não informado'; ?>
                • <?php echo htmlspecialchars($filme['genero']); ?>
            </p>
            <p class="filme-linha">
                Dirigido por
                <a href="biblioteca.php?diretor=<?php echo $filme['diretor_codigo']; ?>" class="link-destaque"><?php echo htmlspecialchars($filme['diretor_nome']); ?></a>
            </p>

            <p class="filme-nota">
                <?php if ($avaliacao['total'] > 0): ?>
                    <span class="estrelas grande"><?php echo estrelas($avaliacao['media']); ?></span>
                    <strong><?php echo number_format($avaliacao['media'], 1, ',', ''); ?></strong>
                    <span class="texto-suave">(<?php echo $avaliacao['total']; ?> avaliações)</span>
                <?php else: ?>
                    <span class="texto-suave">Ainda sem avaliações</span>
                <?php endif; ?>
            </p>

            <h3>Sinopse</h3>
            <p class="filme-sinopse"><?php echo htmlspecialchars($filme['sinopse']) ?: 'Sem sinopse cadastrada.'; ?></p>

            <div class="botoes-acao">
                <?php if ($id_video): ?>
                    <a href="https://www.youtube.com/watch?v=<?php echo $id_video; ?>" target="_blank" rel="noopener noreferrer" class="botao-trailer">Assistir ao trailer</a>
                <?php endif; ?>

                <!-- botão que coloca/tira o filme da lista do usuário -->
                <form method="post" action="" class="form-simples">
                    <input type="hidden" name="acao" value="favoritar">
                    <button type="submit" class="botao-lista <?php echo $na_lista ? 'na-lista' : ''; ?>">
                        <?php echo $na_lista ? 'Na minha lista' : 'Adicionar à minha lista'; ?>
                    </button>
                </form>

                <a href="cadastro_filme.php?editar_id=<?php echo $filme['id']; ?>" class="botao-editar">Editar</a>
            </div>
        </div>
    </section>

    <!-- avaliação do usuário -->
    <h2 class="titulo-secao">Sua avaliação</h2>
    <form method="post" action="" class="form-avaliar">
        <input type="hidden" name="acao" value="avaliar">

        <div class="nota-opcoes">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <label class="nota-opcao">
                    <input type="radio" name="nota" value="<?php echo $i; ?>" <?php echo (isset($minha['nota']) && $minha['nota'] == $i) ? 'checked' : ''; ?>>
                    <?php echo $i; ?> ★
                </label>
            <?php endfor; ?>
        </div>

        <div class="grupo-campo">
            <label for="comentario">Comentário (opcional)</label>
            <textarea id="comentario" name="comentario" rows="3" placeholder="O que você achou do filme?"><?php echo htmlspecialchars($minha['comentario'] ?? ''); ?></textarea>
        </div>

        <div class="acoes-formulario">
            <button type="submit" class="botao-principal"><?php echo $minha ? 'Atualizar avaliação' : 'Avaliar'; ?></button>
        </div>
    </form>

    <?php if ($minha): ?>
        <form method="post" action="" class="form-simples" onsubmit="return confirm('Apagar sua avaliação?')">
            <input type="hidden" name="acao" value="apagar_avaliacao">
            <button type="submit" class="botao-excluir">Apagar minha avaliação</button>
        </form>
    <?php endif; ?>

    <!-- avaliações de todos -->
    <h2 class="titulo-secao">Avaliações (<?php echo $avaliacao['total']; ?>)</h2>
    <?php if ($avaliacoes->num_rows > 0): ?>
        <div class="lista-avaliacoes">
            <?php while ($av = $avaliacoes->fetch_assoc()): ?>
                <div class="avaliacao">
                    <div class="avaliacao-topo">
                        <strong><?php echo htmlspecialchars($av['usuario_nome']); ?></strong>
                        <span class="estrelas"><?php echo estrelas($av['nota']); ?></span>
                    </div>
                    <?php if (!empty($av['comentario'])): ?>
                        <p class="avaliacao-texto"><?php echo htmlspecialchars($av['comentario']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="texto-suave">Nenhuma avaliação ainda.</p>
    <?php endif; ?>

</div>
<?php include('rodape.php'); ?>
</body>
</html>
