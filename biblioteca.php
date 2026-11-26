<?php
// biblioteca.php
// lista todos os filmes em cartões, com filtros combináveis e ordenação
include('valida_sessao.php');
include('conexao.php');
include('funcoes.php');

$mensagem = null;
$tipo_mensagem = null;

// -----------------------------------------------
// excluir (as avaliações e a lista do filme são apagadas antes)
// -----------------------------------------------
if (isset($_GET['excluir_id'])) {
    $id = intval($_GET['excluir_id']);
    excluirAvaliacoesEFavoritos($conexao, $id);

    $comando = $conexao->prepare("SELECT poster_url FROM filmes WHERE id = ?");
    $comando->bind_param("i", $id);
    $comando->execute();
    $arquivo = $comando->get_result()->fetch_assoc();
    $comando->close();

    if ($arquivo && !empty($arquivo['poster_url']) && file_exists($arquivo['poster_url'])) {
        unlink($arquivo['poster_url']);
    }

    $comando = $conexao->prepare("DELETE FROM filmes WHERE id = ?");
    $comando->bind_param("i", $id);
    if ($comando->execute()) {
        $tipo_mensagem = "sucesso";
        $mensagem = "Filme excluído!";
    } else {
        $tipo_mensagem = "erro";
        $mensagem = "Erro: " . $comando->error;
    }
    $comando->close();
}

// -----------------------------------------------
// filtros da busca
// -----------------------------------------------
// começa com 1=1 (sempre verdadeiro) pra poder ir colocando AND nos filtros
$onde = "1=1";
$parametros = [];
$tipos = "";

if (!empty($_GET['diretor'])) {
    $onde .= " AND d.id = ?";
    $parametros[] = intval($_GET['diretor']); // intval garante que é número
    $tipos .= "i";
}

if (!empty($_GET['genero'])) {
    $onde .= " AND f.genero LIKE ?";
    $parametros[] = "%" . $_GET['genero'] . "%"; // % = qualquer texto antes e depois
    $tipos .= "s";
}

if (!empty($_GET['busca'])) {
    $onde .= " AND (f.titulo LIKE ? OR d.nome LIKE ?)";
    $busca = "%" . $_GET['busca'] . "%";
    $parametros[] = $busca; // para o título
    $parametros[] = $busca; // para o nome do diretor
    $tipos .= "ss";
}

// ordenação: só aceita as opções da lista (não coloca o que vem da URL direto no SQL)
$ordem = $_GET['ordem'] ?? 'titulo';
switch ($ordem) {
    case 'recentes':
        $ordenar = "f.id DESC";
        break;
    case 'diretor':
        $ordenar = "d.nome, f.titulo";
        break;
    default:
        $ordem = 'titulo';
        $ordenar = "f.titulo";
}

// guarda os filtros atuais pra não perder a busca ao excluir um filme
$manter_filtros = "busca=" . urlencode($_GET['busca'] ?? '')
                . "&diretor=" . urlencode($_GET['diretor'] ?? '')
                . "&genero=" . urlencode($_GET['genero'] ?? '')
                . "&ordem=" . urlencode($ordem);

// listas dos selects
$diretores = $conexao->query("SELECT id, nome FROM diretores ORDER BY nome");
$generos = $conexao->query("SELECT DISTINCT genero FROM filmes WHERE genero != '' ORDER BY genero");

// -----------------------------------------------
// consulta principal
// -----------------------------------------------
// o JOIN junta filmes com diretores pra pegar o nome do diretor
$sql = "SELECT f.*, d.nome AS diretor_nome
        FROM filmes f
        JOIN diretores d ON f.diretor_id = d.id
        WHERE $onde
        ORDER BY $ordenar";

if (!empty($parametros)) {
    $comando = $conexao->prepare($sql);
    $comando->bind_param($tipos, ...$parametros); // ... espalha o array
    $comando->execute();
    $filmes = $comando->get_result();
} else {
    $filmes = $conexao->query($sql);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca de Filmes — Cineflix</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27><rect width=%27100%27 height=%27100%27 rx=%2718%27 fill=%27%23e50914%27/><text x=%2750%27 y=%2772%27 font-size=%2770%27 text-anchor=%27middle%27 fill=%27white%27 font-family=%27Arial%27 font-weight=%27bold%27>C</text></svg>">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php $pagina_atual = 'biblioteca'; include('menu.php'); ?>

<div class="conteudo">

    <div class="barra-pagina">
        <h2>Biblioteca de Filmes</h2>
        <div class="acoes-topo">
            <a href="cadastro_filme.php" class="botao-principal">Novo Filme</a>
        </div>
    </div>

    <!-- filtros (get deixa a busca na URL) -->
    <div class="filtros">
        <form method="get" class="formulario-filtros">
            <div class="grupo-filtros quatro">

                <div class="item-filtro">
                    <label for="busca">Busca geral</label>
                    <input type="text" name="busca" id="busca"
                           value="<?php echo htmlspecialchars($_GET['busca'] ?? ''); ?>"
                           placeholder="Título ou diretor...">
                </div>

                <div class="item-filtro">
                    <label for="diretor">Diretor</label>
                    <select name="diretor" id="diretor">
                        <option value="">Todos os diretores</option>
                        <?php while ($d = $diretores->fetch_assoc()): ?>
                            <option value="<?php echo $d['id']; ?>"
                                <?php echo (isset($_GET['diretor']) && $_GET['diretor'] == $d['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['nome']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="item-filtro">
                    <label for="genero">Gênero</label>
                    <select name="genero" id="genero">
                        <option value="">Todos os gêneros</option>
                        <?php while ($g = $generos->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($g['genero']); ?>"
                                <?php echo (isset($_GET['genero']) && $_GET['genero'] == $g['genero']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($g['genero']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="item-filtro">
                    <label for="ordem">Ordenar por</label>
                    <select name="ordem" id="ordem">
                        <option value="titulo" <?php echo $ordem == 'titulo' ? 'selected' : ''; ?>>Título (A-Z)</option>
                        <option value="recentes" <?php echo $ordem == 'recentes' ? 'selected' : ''; ?>>Mais recentes</option>
                        <option value="diretor" <?php echo $ordem == 'diretor' ? 'selected' : ''; ?>>Diretor</option>
                    </select>
                </div>

            </div>
            <div class="acoes-filtros">
                <button type="submit" class="botao-principal">Filtrar</button>
                <?php if (!empty($_GET['busca']) || !empty($_GET['diretor']) || !empty($_GET['genero'])): ?>
                    <a href="biblioteca.php" class="botao-voltar">Limpar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if (isset($mensagem)): ?>
        <div class="mensagem <?php echo $tipo_mensagem; ?>">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <p class="texto-suave"><?php echo $filmes->num_rows; ?> filme(s) encontrado(s)</p>

    <?php if ($filmes->num_rows > 0): ?>
        <div class="grade-filmes">
            <?php while ($filme = $filmes->fetch_assoc()): ?>
                <?php
                // botões que aparecem embaixo de cada cartão
                $botoes = '';
                $id_video = pegarIdYoutube($filme['trailer_url'] ?? '');
                if ($id_video) {
                    $botoes .= '<a href="https://www.youtube.com/watch?v=' . $id_video . '" target="_blank" rel="noopener noreferrer" class="botao-trailer">Trailer</a>';
                }
                $botoes .= '<a href="cadastro_filme.php?editar_id=' . $filme['id'] . '" class="botao-editar">Editar</a>';
                $botoes .= '<a href="?excluir_id=' . $filme['id'] . '&' . $manter_filtros . '" '
                         . 'onclick="return confirm(\'Excluir ' . addslashes($filme['titulo']) . '?\')" class="botao-excluir">Excluir</a>';

                mostrarCartaoFilme($conexao, $filme, $botoes);
                ?>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="vazio">
            <p>Nenhum filme encontrado.</p>
        </div>
    <?php endif; ?>

</div>
<?php include('rodape.php'); ?>
</body>
</html>
