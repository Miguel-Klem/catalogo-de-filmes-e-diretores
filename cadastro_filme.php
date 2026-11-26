<?php
// cadastro_filme.php
// CRUD de filmes. O diretor_id liga o filme ao diretor (chave estrangeira)
include('valida_sessao.php');
include('conexao.php');
include('funcoes.php');

$mensagem = null;
$classe = null;

// cadastrar e editar
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $diretor_id = $_POST['diretor_id'] ?? '';
    $titulo = $_POST['titulo'] ?? '';
    $ano_estudio = $_POST['ano_estudio'] ?? '';
    $genero = $_POST['genero'] ?? '';
    $sinopse = $_POST['sinopse'] ?? '';
    $trailer_url = $_POST['trailer_url'] ?? '';

    // valida o link do trailer
    if (!empty($trailer_url) && empty(pegarIdYoutube($trailer_url))) {  // o trailer é opcional, mas se vier preenchido tem que ser do YouTube
        $mensagem = "Insira um link válido do YouTube (ex: https://www.youtube.com/watch?v=...).";
        $classe = "erro";
    } else {
        // upload do pôster (se enviou)
        $poster_url = "";
        if (isset($_FILES['poster_url']) && $_FILES['poster_url']['error'] == 0) {
            $upload = redimensionarESalvarImagem($_FILES['poster_url']);
            if (str_starts_with($upload, 'img/')) {
                $poster_url = $upload;
            } else {
                $mensagem = $upload;
                $classe = "erro";
            }
        }

        if (!isset($classe)) {
            if ($id) {
                // editar (só troca o pôster se enviou um novo)
                $sql = "UPDATE filmes SET diretor_id=?, titulo=?, ano_estudio=?, genero=?, sinopse=?, trailer_url=?";
                $parametros = [$diretor_id, $titulo, $ano_estudio, $genero, $sinopse, $trailer_url];
                if ($poster_url) {
                    $sql .= ", poster_url=?";
                    $parametros[] = $poster_url;
                }
                $sql .= " WHERE id=?";
                $parametros[] = intval($id);

                $comando = $conexao->prepare($sql);
                $tipos = str_repeat('s', count($parametros) - 1) . 'i';
                $comando->bind_param($tipos, ...$parametros);
                $mensagem = "Filme atualizado com sucesso!";
            } else {
                // cadastrar
                $comando = $conexao->prepare("INSERT INTO filmes (diretor_id, titulo, ano_estudio, genero, sinopse, trailer_url, poster_url) VALUES (?,?,?,?,?,?,?)");
                $comando->bind_param("sssssss", $diretor_id, $titulo, $ano_estudio, $genero, $sinopse, $trailer_url, $poster_url);
                $mensagem = "Filme cadastrado com sucesso!";
            }

            if ($comando->execute()) {
                $classe = "sucesso";
            } else {
                $classe = "erro";
                $mensagem = "Erro: " . $comando->error;
            }
            $comando->close();
        }
    }
}

// excluir (apaga o pôster da pasta img também)
if (isset($_GET['excluir_id'])) {
    $id = intval($_GET['excluir_id']);
    excluirAvaliacoesEFavoritos($conexao, $id); // apaga o que está ligado ao filme antes

    $comando = $conexao->prepare("SELECT poster_url FROM filmes WHERE id = ?");
    $comando->bind_param("i", $id);
    $comando->execute();
    $arquivo = $comando->get_result()->fetch_assoc();
    $comando->close();

    // unlink apaga o arquivo da pasta img
    if ($arquivo && !empty($arquivo['poster_url']) && file_exists($arquivo['poster_url'])) {
        unlink($arquivo['poster_url']);
    }

    $comando = $conexao->prepare("DELETE FROM filmes WHERE id = ?");
    $comando->bind_param("i", $id);
    if ($comando->execute()) {
        $classe = "sucesso";
        $mensagem = "Filme excluído!";
    } else {
        $classe = "erro";
        $mensagem = "Erro: " . $comando->error;
    }
    $comando->close();
}

// buscar os dados para a tela

// filme que está sendo editado
$filme = null;
if (isset($_GET['editar_id'])) {
    $editar_id = intval($_GET['editar_id']);
    $comando = $conexao->prepare("SELECT * FROM filmes WHERE id = ?");
    $comando->bind_param("i", $editar_id);
    $comando->execute();
    $filme = $comando->get_result()->fetch_assoc();
    $comando->close();
}

// diretores para o select do formulário
$diretores = $conexao->query("SELECT id, nome FROM diretores ORDER BY nome");

// se veio do botão "Filmes" da tela de diretores, mostra só os filmes dele
$diretor_id_filtro = isset($_GET['diretor_id']) ? intval($_GET['diretor_id']) : ($filme['diretor_id'] ?? null);

$sql_lista = "SELECT f.*, d.nome AS diretor_nome FROM filmes f JOIN diretores d ON f.diretor_id = d.id";
if ($diretor_id_filtro) {
    $comando = $conexao->prepare($sql_lista . " WHERE f.diretor_id = ? ORDER BY f.titulo");
    $comando->bind_param("i", $diretor_id_filtro);
    $comando->execute();
    $filmes = $comando->get_result();
    $comando->close();
} else {
    $filmes = $conexao->query($sql_lista . " ORDER BY f.titulo");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $filme ? 'Editar Filme' : 'Novo Filme'; ?> — Cineflix</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27><rect width=%27100%27 height=%27100%27 rx=%2718%27 fill=%27%23e50914%27/><text x=%2750%27 y=%2772%27 font-size=%2770%27 text-anchor=%27middle%27 fill=%27white%27 font-family=%27Arial%27 font-weight=%27bold%27>C</text></svg>">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php $pagina_atual = 'filmes'; include('menu.php'); ?>
<div class="conteudo">

    <div class="barra-pagina">
        <h2><?php echo $filme ? 'Editar Filme' : 'Novo Filme'; ?></h2>
    </div>

    <?php if (isset($mensagem)): ?>
        <div class="mensagem <?php echo $classe; ?>">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="" enctype="multipart/form-data">

        <input type="hidden" name="id" value="<?php echo $filme['id'] ?? ''; ?>">

        <div class="grade-formulario">

            <div class="grupo-campo">
                <label for="diretor_id">Diretor *</label>
                <select id="diretor_id" name="diretor_id" required>
                    <option value="">Selecione o diretor...</option>
                    <?php while ($d = $diretores->fetch_assoc()): ?>
                        <option value="<?php echo $d['id']; ?>"
                            <?php echo ($d['id'] == ($diretor_id_filtro ?? '')) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($d['nome']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="grupo-campo">
                <label for="titulo">Título do Filme *</label>
                <input type="text" id="titulo" name="titulo"
                       value="<?php echo htmlspecialchars($filme['titulo'] ?? ''); ?>"
                       required placeholder="Ex: Interestelar">
            </div>

            <div class="grupo-campo">
                <label for="ano_estudio">Ano / Estúdio</label>
                <input type="text" id="ano_estudio" name="ano_estudio"
                       value="<?php echo htmlspecialchars($filme['ano_estudio'] ?? ''); ?>"
                       placeholder="Ex: 2014 / Warner Bros.">
            </div>

            <div class="grupo-campo">
                <label for="genero">Gênero *</label>
                <input type="text" id="genero" name="genero"
                       value="<?php echo htmlspecialchars($filme['genero'] ?? ''); ?>"
                       required placeholder="Ex: Ficção Científica">
            </div>

            <div class="grupo-campo">
                <label for="trailer_url">Link do Trailer (YouTube)</label>
                <input type="text" id="trailer_url" name="trailer_url"
                       value="<?php echo htmlspecialchars($filme['trailer_url'] ?? ''); ?>"
                       placeholder="https://www.youtube.com/watch?v=...">
            </div>

            <div class="grupo-campo">
                <label for="poster_url">Pôster do Filme</label>
                <input type="file" id="poster_url" name="poster_url" accept="image/*">
                <small>JPG ou PNG · Máximo 5 MB<?php echo $filme ? ' · Deixe vazio para manter o pôster atual' : ''; ?></small>

                <?php if (!empty($filme['poster_url']) && file_exists($filme['poster_url'])): ?>
                    <div class="imagem-atual">
                        <img src="<?php echo htmlspecialchars($filme['poster_url']); ?>" alt="Pôster atual">
                        <small>Pôster atual</small>
                    </div>
                <?php endif; ?>
            </div>

            <div class="grupo-campo campo-largo">
                <label for="sinopse">Sinopse</label>
                <textarea id="sinopse" name="sinopse" rows="4"
                          placeholder="Resumo da história do filme..."><?php echo htmlspecialchars($filme['sinopse'] ?? ''); ?></textarea>
            </div>

        </div>

        <div class="acoes-formulario">
            <button type="submit" class="botao-principal">
                <?php echo $filme ? 'Atualizar' : 'Cadastrar'; ?>
            </button>
            <?php if ($filme): ?>
                <a href="cadastro_filme.php" class="botao-voltar">Cancelar</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- tabela de filmes -->
    <h3>Filmes Cadastrados</h3>

    <div class="tabela-container">
        <table>
            <thead>
                <tr>
                    <th class="col-imagem">Pôster</th>
                    <th>Título</th>
                    <th>Gênero • Ano / Estúdio</th>
                    <th>Diretor</th>
                    <th class="col-acoes">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($filmes->num_rows > 0): ?>
                    <?php while ($linha = $filmes->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if ($linha['poster_url'] && file_exists($linha['poster_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($linha['poster_url']); ?>"
                                         alt="Pôster" class="miniatura">
                                <?php else: ?>
                                    <div class="sem-imagem"></div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($linha['titulo']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($linha['genero']) ?: '—'; ?>
                                • <?php echo htmlspecialchars($linha['ano_estudio']) ?: '—'; ?>
                            </td>
                            <td><?php echo htmlspecialchars($linha['diretor_nome']); ?></td>
                            <td>
                                <div class="botoes-acao">
                                    <?php
                                    // monta o link do trailer com o id do vídeo
                                    $id_video = pegarIdYoutube($linha['trailer_url'] ?? '');
                                    if ($id_video):
                                    ?>
                                        <a href="https://www.youtube.com/watch?v=<?php echo $id_video; ?>"
                                           target="_blank" rel="noopener noreferrer"
                                           class="botao-trailer">Trailer</a>
                                    <?php endif; ?>
                                    <a href="?editar_id=<?php echo $linha['id']; ?>" class="botao-editar">Editar</a>
                                    <a href="?excluir_id=<?php echo $linha['id']; ?>"
                                       onclick="return confirm('Excluir <?php echo addslashes($linha['titulo']); ?>?')"
                                       class="botao-excluir">Excluir</a>
                                    <a href="filme.php?id=<?php echo $linha['id']; ?>" class="botao-detalhes">Detalhes</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="celula-vazia">Nenhum filme cadastrado ainda.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
<?php include('rodape.php'); ?>
</body>
</html>
