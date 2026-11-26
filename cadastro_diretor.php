<?php

// CRUD dos Diretores

include('valida_sessao.php');
include('conexao.php');
include('funcoes.php');

$mensagem = null;
$classe = null;

// Cadastrar e Editar
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $pais = $_POST['pais'] ?? '';
    $estilo = $_POST['estilo'] ?? '';

    // Se enviou foto, redimensiona e salva ela
    $foto_url = "";
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $upload = redimensionarESalvarImagem($_FILES['imagem']);
        if (str_starts_with($upload, 'img/')) {
            $foto_url = $upload;
        } else {
            $mensagem = $upload; // Aqui $upload é a mensagem de erro
            $classe = "erro";
        }
    }

    if (!isset($classe)) {
        if ($id) {

            // Editar (só troca a foto se enviou uma nova)
            $sql = "UPDATE diretores SET nome=?, pais=?, estilo=?";
            $parametros = [$nome, $pais, $estilo];
            if ($foto_url) {
                $sql .= ", foto_url=?";
                $parametros[] = $foto_url;
            }
            $sql .= " WHERE id=?";
            $parametros[] = intval($id);

            $comando = $conexao->prepare($sql);
            // Indentifica o "s" para texto e o "i" para o id no final
            $tipos = str_repeat('s', count($parametros) - 1) . 'i';
            $comando->bind_param($tipos, ...$parametros);
            $mensagem = "Diretor atualizado com sucesso!";
        } else {
            // Cadastrar
            $comando = $conexao->prepare("INSERT INTO diretores (nome, pais, estilo, foto_url) VALUES (?,?,?,?)");
            $comando->bind_param("ssss", $nome, $pais, $estilo, $foto_url);
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

// Deletar
if (isset($_GET['excluir_id'])) {
    $id = intval($_GET['excluir_id']);

    // Não deixa excluir diretor que tem filmes
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

// Buscar os dados para a tela
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
    <title><?php echo $diretor ? 'Editar Diretor' : 'Novo Diretor'; ?> - Cineflix</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27><rect width=%27100%27 height=%27100%27 rx=%2718%27 fill=%27%23e50914%27/><text x=%2750%27 y=%2772%27 font-size=%2770%27 text-anchor=%27middle%27 fill=%27white%27 font-family=%27Arial%27 font-weight=%27bold%27>C</text></svg>">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php $pagina_atual = 'diretores'; include('menu.php'); ?>
<div class="conteudo">

    <div class="barra-pagina">
        <h2><?php echo $diretor ? 'Editar Diretor' : 'Novo Diretor'; ?></h2>
    </div>

    <?php if (isset($mensagem)): ?>
        <div class="mensagem <?php echo $classe; ?>">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <!-- enctype: É obrigatório para enviar arquivo -->
    <form method="post" action="" enctype="multipart/form-data">

        <!-- Guarda o id quando está editando -->
        <input type="hidden" name="id" value="<?php echo $diretor['id'] ?? ''; ?>">

        <div class="grade-formulario">

            <div class="grupo-campo">
                <label for="nome">Nome do Diretor *</label>
                <input type="text" id="nome" name="nome"
                       value="<?php echo htmlspecialchars($diretor['nome'] ?? ''); ?>"
                       required placeholder="Ex: Christopher Nolan">
            </div>

            <div class="grupo-campo">
                <label for="pais">País *</label>
                <input type="text" id="pais" name="pais"
                       value="<?php echo htmlspecialchars($diretor['pais'] ?? ''); ?>"
                       required placeholder="Ex: Reino Unido, Brasil...">
            </div>

            <div class="grupo-campo">
                <label for="estilo">Estilo (gênero principal) *</label>
                <input type="text" id="estilo" name="estilo"
                       value="<?php echo htmlspecialchars($diretor['estilo'] ?? ''); ?>"
                       required placeholder="Ex: Ficção Científica, Drama...">
            </div>

            <div class="grupo-campo">
                <label for="imagem">Foto do Diretor</label>
                <input type="file" id="imagem" name="imagem" accept="image/*">
                <small>JPG ou PNG · Máximo 5 MB<?php echo $diretor ? ' · Deixe vazio para manter a foto atual' : ''; ?></small>

                <?php if (!empty($diretor['foto_url']) && file_exists($diretor['foto_url'])): ?>
                    <div class="imagem-atual">
                        <img src="<?php echo htmlspecialchars($diretor['foto_url']); ?>" alt="Foto atual">
                        <small>Foto atual</small>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <div class="acoes-formulario">
            <button type="submit" class="botao-principal">
                <?php echo $diretor ? 'Atualizar' : 'Cadastrar'; ?>
            </button>
            <?php if ($diretor): ?>
                <a href="cadastro_diretor.php" class="botao-voltar">Cancelar</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Tabela de diretores -->
    <h3>Diretores Cadastrados</h3>

    <div class="tabela-container">
        <table>
            <thead>
                <tr>
                    <th class="col-imagem">Foto</th>
                    <th>Nome</th>
                    <th>País</th>
                    <th>Estilo</th>
                    <th class="col-acoes">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($diretores->num_rows > 0): ?>
                    <?php while ($linha = $diretores->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if ($linha['foto_url'] && file_exists($linha['foto_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($linha['foto_url']); ?>"
                                         alt="<?php echo htmlspecialchars($linha['nome']); ?>"
                                         class="miniatura">
                                <?php else: ?>
                                    <!-- sem foto: mostra um emoji -->
                                    <div class="sem-imagem"></div>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($linha['nome']); ?></td>
                            <td><?php echo htmlspecialchars($linha['pais']) ?: '—'; ?></td>
                            <td><?php echo htmlspecialchars($linha['estilo']) ?: '—'; ?></td>
                            <td>
                                <div class="botoes-acao">
                                    <a href="?editar_id=<?php echo $linha['id']; ?>" class="botao-editar">Editar</a>
                                    <a href="?excluir_id=<?php echo $linha['id']; ?>"
                                       onclick="return confirm('Excluir <?php echo addslashes($linha['nome']); ?>?')"
                                       class="botao-excluir">Excluir</a>
                                    <a href="cadastro_filme.php?diretor_id=<?php echo $linha['id']; ?>"
                                       class="botao-filmes">Filmes</a>
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
<?php include('rodape.php'); ?>
</body>
</html>
