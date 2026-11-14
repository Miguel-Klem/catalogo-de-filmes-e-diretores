<?php
/*
 * ARQUIVO: biblioteca.php
 * FUNÇÃO: Biblioteca de Filmes (listar e filtrar)
 *
 * Filtros que podem ser usados juntos:
 *   - Busca geral (título ou diretor)
 *   - Diretor
 *   - Gênero
 */
include('valida_sessao.php');
include('conexao.php');
include('funcoes.php');

$mensagem = null;
$tipo_mensagem = null;

// -----------------------------------------------
// MONTAGEM DOS FILTROS
// -----------------------------------------------
// Começa com "1=1" (sempre verdadeiro) para poder ir colocando AND
// sem precisar descobrir qual é o primeiro filtro.
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

// Texto com os filtros atuais, para não perder a busca ao excluir um filme
$manter_filtros = "busca=" . urlencode($_GET['busca'] ?? '')
                . "&diretor=" . urlencode($_GET['diretor'] ?? '')
                . "&genero=" . urlencode($_GET['genero'] ?? '');

// Listas dos selects de filtro
$diretores = $conexao->query("SELECT id, nome FROM diretores ORDER BY nome");
$generos = $conexao->query("SELECT DISTINCT genero FROM filmes WHERE genero != '' ORDER BY genero");

// -----------------------------------------------
// EXCLUIR
// -----------------------------------------------
if (isset($_GET['excluir_id'])) {
    $id = intval($_GET['excluir_id']);

    $comando = $conexao->prepare("SELECT imagem_poster FROM filmes WHERE id = ?");
    $comando->bind_param("i", $id);
    $comando->execute();
    $arquivo = $comando->get_result()->fetch_assoc();
    $comando->close();

    if ($arquivo && !empty($arquivo['imagem_poster']) && file_exists($arquivo['imagem_poster'])) {
        unlink($arquivo['imagem_poster']);
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
// DETALHES (sinopse do filme escolhido)
// -----------------------------------------------
$detalhes = null;
if (isset($_GET['detalhes_id'])) {
    $detalhes_id = intval($_GET['detalhes_id']);
    $comando = $conexao->prepare("SELECT f.*, d.nome AS diretor_nome FROM filmes f JOIN diretores d ON f.diretor_id = d.id WHERE f.id = ?");
    $comando->bind_param("i", $detalhes_id);
    $comando->execute();
    $detalhes = $comando->get_result()->fetch_assoc();
    $comando->close();
}

// -----------------------------------------------
// CONSULTA PRINCIPAL
// -----------------------------------------------
// O JOIN junta filmes com diretores para trazer o nome do diretor
$sql = "SELECT f.*, d.nome AS diretor_nome
        FROM filmes f
        JOIN diretores d ON f.diretor_id = d.id
        WHERE $onde
        ORDER BY d.nome, f.titulo";

if (!empty($parametros)) {
    // Com filtros: consulta preparada
    $comando = $conexao->prepare($sql);
    $comando->bind_param($tipos, ...$parametros); // ... espalha o array nos parâmetros
    $comando->execute();
    $filmes = $comando->get_result();
} else {
    // Sem filtros: consulta simples
    $filmes = $conexao->query($sql);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biblioteca de Filmes — Cineflix</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27><text y=%27.9em%27 font-size=%2790%27>🎬</text></svg>">
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<div class="conteudo">

    <div class="barra-pagina">
        <h2>🍿 Biblioteca de Filmes</h2>
        <div class="acoes-topo">
            <a href="index.php" class="botao-voltar">⬅ Voltar ao Menu</a>
            <a href="cadastro_filme.php" class="botao-principal">➕ Novo Filme</a>
        </div>
    </div>

    <!-- FILTROS (method="get" deixa a busca na URL) -->
    <div class="filtros">
        <form method="get" class="formulario-filtros">
            <div class="grupo-filtros">

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

            </div>
            <div class="acoes-filtros">
                <button type="submit" class="botao-principal">🔍 Filtrar</button>
                <?php if (!empty($_GET)): ?>
                    <a href="biblioteca.php" class="botao-voltar">✖ Limpar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if (isset($mensagem)): ?>
        <div class="mensagem <?php echo $tipo_mensagem; ?>">
            <?php echo $tipo_mensagem === 'sucesso' ? '✅' : '⚠️'; ?> <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <?php if ($detalhes): ?>
        <div class="caixa-sinopse">
            <h3>🎬 <?php echo htmlspecialchars($detalhes['titulo']); ?></h3>
            <p><strong>Diretor:</strong> <?php echo htmlspecialchars($detalhes['diretor_nome']); ?></p>
            <p><strong>Sinopse:</strong> <?php echo htmlspecialchars($detalhes['sinopse']) ?: 'Sem sinopse cadastrada.'; ?></p>
            <a href="biblioteca.php?<?php echo $manter_filtros; ?>" class="botao-voltar">✖ Fechar</a>
        </div>
    <?php endif; ?>

    <!-- TABELA DE FILMES -->
    <div class="tabela-container">
        <table>
            <thead>
                <tr>
                    <th class="col-imagem">Pôster</th>
                    <th>Título</th>
                    <th>Gênero • Ano</th>
                    <th>Diretor</th>
                    <th class="col-acoes">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($filmes->num_rows > 0): ?>
                    <?php while ($linha = $filmes->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if ($linha['imagem_poster']): ?>
                                    <img src="<?php echo htmlspecialchars($linha['imagem_poster']); ?>"
                                         alt="Pôster" class="miniatura">
                                <?php else: ?>
                                    <div class="sem-imagem">🎬</div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($linha['titulo']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($linha['genero']) ?: '—'; ?>
                                • <?php echo htmlspecialchars($linha['ano']) ?: '—'; ?>
                            </td>
                            <td><?php echo htmlspecialchars($linha['diretor_nome']); ?></td>
                            <td>
                                <div class="botoes-acao">
                                    <?php
                                    // Monta o link do trailer a partir do ID do vídeo
                                    $id_video = pegarIdYoutube($linha['trailer_link'] ?? '');
                                    if ($id_video):
                                    ?>
                                        <!-- target="_blank" abre em outra aba -->
                                        <a href="https://www.youtube.com/watch?v=<?php echo $id_video; ?>"
                                           target="_blank" rel="noopener noreferrer"
                                           class="botao-trailer">▶ Trailer</a>
                                    <?php endif; ?>
                                    <a href="?detalhes_id=<?php echo $linha['id']; ?>&<?php echo $manter_filtros; ?>"
                                       class="botao-detalhes">📖 Detalhes</a>
                                    <a href="cadastro_filme.php?editar_id=<?php echo $linha['id']; ?>" class="botao-editar">✏️ Editar</a>
                                    <a href="?excluir_id=<?php echo $linha['id']; ?>&<?php echo $manter_filtros; ?>"
                                       onclick="return confirm('Excluir <?php echo addslashes($linha['titulo']); ?>?')"
                                       class="botao-excluir">🗑️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="celula-vazia">Nenhum filme encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>
