<?php
/*
 * ARQUIVO: cadastro_filme.php
 * FUNÇÃO: CRUD de filmes (cada filme pertence a um diretor)
 *
 * O diretor_id é a chave estrangeira: liga o filme ao diretor.
 * O link do trailer é validado antes de salvar.
 */
include('valida_sessao.php');
include('conexao.php');
include('funcoes.php');

$mensagem = null;
$classe = null;

// -----------------------------------------------
// CRIAR / EDITAR
// -----------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $diretor_id = $_POST['diretor_id'] ?? '';
    $titulo = $_POST['titulo'] ?? '';
    $ano = $_POST['ano'] ?? '';
    $genero = $_POST['genero'] ?? '';
    $sinopse = $_POST['sinopse'] ?? '';
    $trailer_link = $_POST['trailer_link'] ?? '';

    // Ano vazio vira NULL, porque a coluna ano é número e não aceita texto vazio
    if ($ano === '') {
        $ano = null;
    }

    // Validação do link do trailer
    if (empty(pegarIdYoutube($trailer_link))) {
        $mensagem = "Insira um link válido do YouTube (ex: https://www.youtube.com/watch?v=...).";
        $classe = "erro";
    } else {
        // Upload do pôster (se enviou)
        $imagem_poster = "";
        if (isset($_FILES['imagem_poster']) && $_FILES['imagem_poster']['error'] == 0) {
            $upload = redimensionarESalvarImagem($_FILES['imagem_poster']);
            if (str_starts_with($upload, 'img/')) {
                $imagem_poster = $upload;
            } else {
                $mensagem = $upload;
                $classe = "erro";
            }
        }

        if (!isset($classe)) {
            if ($id) {
                // EDITAR: só troca o pôster se enviou um novo
                $sql = "UPDATE filmes SET diretor_id=?, titulo=?, ano=?, genero=?, sinopse=?, trailer_link=?";
                $parametros = [$diretor_id, $titulo, $ano, $genero, $sinopse, $trailer_link];
                if ($imagem_poster) {
                    $sql .= ", imagem_poster=?";
                    $parametros[] = $imagem_poster;
                }
                $sql .= " WHERE id=?";
                $parametros[] = intval($id);

                $comando = $conexao->prepare($sql);
                $tipos = str_repeat('s', count($parametros) - 1) . 'i';
                $comando->bind_param($tipos, ...$parametros);
                $mensagem = "Filme atualizado com sucesso!";
            } else {
                // CRIAR
                $comando = $conexao->prepare("INSERT INTO filmes (diretor_id, titulo, ano, genero, sinopse, trailer_link, imagem_poster) VALUES (?,?,?,?,?,?,?)");
                $comando->bind_param("sssssss", $diretor_id, $titulo, $ano, $genero, $sinopse, $trailer_link, $imagem_poster);
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

// -----------------------------------------------
// EXCLUIR (apaga também o arquivo do pôster)
// -----------------------------------------------
if (isset($_GET['excluir_id'])) {
    $id = intval($_GET['excluir_id']);

    $comando = $conexao->prepare("SELECT imagem_poster FROM filmes WHERE id = ?");
    $comando->bind_param("i", $id);
    $comando->execute();
    $arquivo = $comando->get_result()->fetch_assoc();
    $comando->close();

    // unlink() apaga o arquivo da pasta img/
    if ($arquivo && !empty($arquivo['imagem_poster']) && file_exists($arquivo['imagem_poster'])) {
        unlink($arquivo['imagem_poster']);
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

// -----------------------------------------------
// LER
// -----------------------------------------------

// Filme que está sendo editado
$filme = null;
if (isset($_GET['editar_id'])) {
    $editar_id = intval($_GET['editar_id']);
    $comando = $conexao->prepare("SELECT * FROM filmes WHERE id = ?");
    $comando->bind_param("i", $editar_id);
    $comando->execute();
    $filme = $comando->get_result()->fetch_assoc();
    $comando->close();
}

// Filme que o usuário quer ver os detalhes (sinopse)
$detalhes = null;
if (isset($_GET['detalhes_id'])) {
    $detalhes_id = intval($_GET['detalhes_id']);
    $comando = $conexao->prepare("SELECT f.*, d.nome AS diretor_nome FROM filmes f JOIN diretores d ON f.diretor_id = d.id WHERE f.id = ?");
    $comando->bind_param("i", $detalhes_id);
    $comando->execute();
    $detalhes = $comando->get_result()->fetch_assoc();
    $comando->close();
}

// Lista de diretores para o select do formulário
$diretores = $conexao->query("SELECT id, nome FROM diretores ORDER BY nome");

// Se veio de "Filmes" na tela de diretores, mostra só os filmes dele
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
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27><text y=%27.9em%27 font-size=%2790%27>🎬</text></svg>">
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<div class="conteudo">

    <div class="barra-pagina">
        <h2><?php echo $filme ? '✏️ Editar Filme' : '🎞️ Novo Filme'; ?></h2>
        <div class="acoes-topo">
            <a href="cadastro_diretor.php" class="botao-voltar">⬅ Voltar para Diretores</a>
            <a href="index.php" class="botao-voltar">🏠 Menu</a>
        </div>
    </div>

    <?php if (isset($mensagem)): ?>
        <div class="mensagem <?php echo $classe; ?>">
            <?php echo $classe === 'sucesso' ? '✅' : '⚠️'; ?> <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <!-- Caixa com a sinopse (aparece quando clica em Detalhes) -->
    <?php if ($detalhes): ?>
        <div class="caixa-sinopse">
            <h3>🎬 <?php echo htmlspecialchars($detalhes['titulo']); ?></h3>
            <p><strong>Diretor:</strong> <?php echo htmlspecialchars($detalhes['diretor_nome']); ?></p>
            <p><strong>Sinopse:</strong> <?php echo htmlspecialchars($detalhes['sinopse']) ?: 'Sem sinopse cadastrada.'; ?></p>
            <a href="cadastro_filme.php" class="botao-voltar">✖ Fechar</a>
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
                <label for="ano">Ano de Lançamento</label>
                <input type="number" id="ano" name="ano" min="1888" max="2100"
                       value="<?php echo htmlspecialchars($filme['ano'] ?? ''); ?>"
                       placeholder="Ex: 2014">
            </div>

            <div class="grupo-campo">
                <label for="genero">Gênero</label>
                <input type="text" id="genero" name="genero"
                       value="<?php echo htmlspecialchars($filme['genero'] ?? ''); ?>"
                       placeholder="Ex: Ficção Científica">
            </div>

            <div class="grupo-campo">
                <label for="trailer_link">Link do Trailer (YouTube) *</label>
                <input type="text" id="trailer_link" name="trailer_link"
                       value="<?php echo htmlspecialchars($filme['trailer_link'] ?? ''); ?>"
                       required placeholder="https://www.youtube.com/watch?v=...">
            </div>

            <div class="grupo-campo">
                <label for="imagem_poster">Pôster do Filme</label>
                <input type="file" id="imagem_poster" name="imagem_poster" accept="image/*">
                <small>JPG ou PNG · Máximo 5 MB<?php echo $filme ? ' · Deixe vazio para manter o pôster atual' : ''; ?></small>

                <?php if (!empty($filme['imagem_poster'])): ?>
                    <div class="imagem-atual">
                        <img src="<?php echo htmlspecialchars($filme['imagem_poster']); ?>" alt="Pôster atual">
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
                <?php echo $filme ? '💾 Atualizar' : '➕ Cadastrar'; ?>
            </button>
            <?php if ($filme): ?>
                <a href="cadastro_filme.php" class="botao-voltar">✖ Cancelar</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- TABELA DE FILMES -->
    <h3>📋 Filmes Cadastrados</h3>

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
                                    <a href="?editar_id=<?php echo $linha['id']; ?>" class="botao-editar">✏️ Editar</a>
                                    <a href="?excluir_id=<?php echo $linha['id']; ?>"
                                       onclick="return confirm('Excluir <?php echo addslashes($linha['titulo']); ?>?')"
                                       class="botao-excluir">🗑️</a>
                                    <a href="?detalhes_id=<?php echo $linha['id']; ?>" class="botao-detalhes">📖 Detalhes</a>
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
</body>
</html>
