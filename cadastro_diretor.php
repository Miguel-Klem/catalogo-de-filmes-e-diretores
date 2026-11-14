<?php
// cadastro_diretor.php
// cadastrar, listar, editar e excluir diretores (CRUD)
include('valida_sessao.php');
include('conexao.php');
include('funcoes.php');

$mensagem = null;
$classe = null;

// cadastrar e editar
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $pais = $_POST['pais'] ?? '';
    $genero = $_POST['genero'] ?? '';

    // se enviou foto, redimensiona e salva
    $imagem = "";
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $upload = redimensionarESalvarImagem($_FILES['imagem']);
        if (str_starts_with($upload, 'img/')) {
            $imagem = $upload;
        } else {
            $mensagem = $upload; // aqui $upload é a mensagem de erro
            $classe = "erro";
        }
    }

    if (!isset($classe)) {
        if ($id) {
            // editar (só troca a foto se enviou uma nova)
            $sql = "UPDATE diretores SET nome=?, pais=?, genero=?";
            $parametros = [$nome, $pais, $genero];
            if ($imagem) {
                $sql .= ", imagem=?";
                $parametros[] = $imagem;
            }
            $sql .= " WHERE id=?";
            $parametros[] = intval($id);

            $comando = $conexao->prepare($sql);
            // "s" para texto e "i" para o id no final
            $tipos = str_repeat('s', count($parametros) - 1) . 'i';
            $comando->bind_param($tipos, ...$parametros);
            $mensagem = "Diretor atualizado com sucesso!";
        } else {
            // cadastrar
            $comando = $conexao->prepare("INSERT INTO diretores (nome, pais, genero, imagem) VALUES (?,?,?,?)");
            $comando->bind_param("ssss", $nome, $pais, $genero, $imagem);
            $mensagem = "Diretor cadastrado com sucesso!";
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

// excluir
if (isset($_GET['excluir_id'])) {
    $id = intval($_GET['excluir_id']);

    // não deixa excluir diretor que tem filmes
    $verifica = $conexao->prepare("SELECT COUNT(*) AS total FROM filmes WHERE diretor_id = ?");
    $verifica->bind_param("i", $id);
    $verifica->execute();
    $total = $verifica->get_result()->fetch_assoc()['total'];
    $verifica->close();

    if ($total > 0) {
        $mensagem = "Não é possível excluir: o diretor possui filmes cadastrados.";
        $classe = "erro";
    } else {
        $comando = $conexao->prepare("DELETE FROM diretores WHERE id = ?");
        $comando->bind_param("i", $id);
        if ($comando->execute()) {
            $classe = "sucesso";
            $mensagem = "Diretor excluído!";
        } else {
            $classe = "erro";
            $mensagem = "Erro: " . $comando->error;
        }
        $comando->close();
    }
}

// buscar os dados para a tela
$diretor = null;
if (isset($_GET['editar_id'])) {
    $editar_id = intval($_GET['editar_id']);
    $comando = $conexao->prepare("SELECT * FROM diretores WHERE id = ?");
    $comando->bind_param("i", $editar_id);
    $comando->execute();
    $diretor = $comando->get_result()->fetch_assoc();
    $comando->close();
}

$diretores = $conexao->query("SELECT * FROM diretores ORDER BY nome");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $diretor ? 'Editar Diretor' : 'Novo Diretor'; ?> — Cineflix</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27><text y=%27.9em%27 font-size=%2790%27>🎬</text></svg>">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="conteudo">

    <div class="barra-pagina">
        <h2><?php echo $diretor ? '✏️ Editar Diretor' : '🎥 Novo Diretor'; ?></h2>
        <div class="acoes-topo">
            <a href="index.php" class="botao-voltar">⬅ Voltar ao Menu</a>
        </div>
    </div>

    <?php if (isset($mensagem)): ?>
        <div class="mensagem <?php echo $classe; ?>">
            <?php echo $classe === 'sucesso' ? '✅' : '⚠️'; ?> <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <!-- enctype é obrigatório para enviar arquivo -->
    <form method="post" action="" enctype="multipart/form-data">

        <!-- guarda o id quando está editando -->
        <input type="hidden" name="id" value="<?php echo $diretor['id'] ?? ''; ?>">

        <div class="grade-formulario">

            <div class="grupo-campo">
                <label for="nome">Nome do Diretor *</label>
                <input type="text" id="nome" name="nome"
                       value="<?php echo htmlspecialchars($diretor['nome'] ?? ''); ?>"
                       required placeholder="Ex: Christopher Nolan">
            </div>

            <div class="grupo-campo">
                <label for="pais">País</label>
                <input type="text" id="pais" name="pais"
                       value="<?php echo htmlspecialchars($diretor['pais'] ?? ''); ?>"
                       placeholder="Ex: Reino Unido, Brasil...">
            </div>

            <div class="grupo-campo">
                <label for="genero">Gênero Principal</label>
                <input type="text" id="genero" name="genero"
                       value="<?php echo htmlspecialchars($diretor['genero'] ?? ''); ?>"
                       placeholder="Ex: Ficção Científica, Drama...">
            </div>

            <div class="grupo-campo">
                <label for="imagem">Foto do Diretor</label>
                <input type="file" id="imagem" name="imagem" accept="image/*">
                <small>JPG ou PNG · Máximo 5 MB<?php echo $diretor ? ' · Deixe vazio para manter a foto atual' : ''; ?></small>

                <?php if (!empty($diretor['imagem'])): ?>
                    <div class="imagem-atual">
                        <img src="<?php echo htmlspecialchars($diretor['imagem']); ?>" alt="Foto atual">
                        <small>Foto atual</small>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <div class="acoes-formulario">
            <button type="submit" class="botao-principal">
                <?php echo $diretor ? '💾 Atualizar' : '➕ Cadastrar'; ?>
            </button>
            <?php if ($diretor): ?>
                <a href="cadastro_diretor.php" class="botao-voltar">✖ Cancelar</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- tabela de diretores -->
    <h3>📋 Diretores Cadastrados</h3>

    <div class="tabela-container">
        <table>
            <thead>
                <tr>
                    <th class="col-imagem">Foto</th>
                    <th>Nome</th>
                    <th>País</th>
                    <th>Gênero Principal</th>
                    <th class="col-acoes">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($diretores->num_rows > 0): ?>
                    <?php while ($linha = $diretores->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if ($linha['imagem']): ?>
                                    <img src="<?php echo htmlspecialchars($linha['imagem']); ?>"
                                         alt="<?php echo htmlspecialchars($linha['nome']); ?>"
                                         class="miniatura">
                                <?php else: ?>
                                    <!-- sem foto: mostra um emoji -->
                                    <div class="sem-imagem">👤</div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($linha['nome']); ?></td>
                            <td><?php echo htmlspecialchars($linha['pais']) ?: '—'; ?></td>
                            <td><?php echo htmlspecialchars($linha['genero']) ?: '—'; ?></td>
                            <td>
                                <div class="botoes-acao">
                                    <a href="?editar_id=<?php echo $linha['id']; ?>" class="botao-editar">✏️ Editar</a>
                                    <a href="?excluir_id=<?php echo $linha['id']; ?>"
                                       onclick="return confirm('Excluir <?php echo addslashes($linha['nome']); ?>?')"
                                       class="botao-excluir">🗑️</a>
                                    <a href="cadastro_filme.php?diretor_id=<?php echo $linha['id']; ?>"
                                       class="botao-filmes">🎞️ Filmes</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="celula-vazia">Nenhum diretor cadastrado ainda.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>
