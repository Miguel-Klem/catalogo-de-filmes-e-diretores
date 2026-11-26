<?php
// perfil.php - perfil do usuário
// aqui dá para trocar ou remover a foto e excluir a conta
include('valida_sessao.php');
include('conexao.php');
include('funcoes.php');

$usuario_id = intval($_SESSION['usuario_id']);
$mensagem = '';
$tipo_mensagem = '';

// pega os dados atuais do usuário
function buscarUsuario($conexao, $usuario_id) {
    $comando = $conexao->prepare("SELECT * FROM usuarios WHERE id = ?");
    $comando->bind_param("i", $usuario_id);
    $comando->execute();
    $linha = $comando->get_result()->fetch_assoc();
    $comando->close();
    return $linha;
}

$dados = buscarUsuario($conexao, $usuario_id);

// se a conta não existe mais, sai do sistema
if (!$dados) {
    header('Location: logout.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $acao = $_POST['acao'] ?? '';

    // enviar uma foto nova
    if ($acao == 'foto') {
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $upload = redimensionarESalvarImagem($_FILES['foto']);

            if (str_starts_with($upload, 'img/')) {
                // apaga a foto antiga da pasta img
                if (!empty($dados['foto_url']) && file_exists($dados['foto_url'])) {
                    unlink($dados['foto_url']);
                }
                $comando = $conexao->prepare("UPDATE usuarios SET foto_url = ? WHERE id = ?");
                $comando->bind_param("si", $upload, $usuario_id);
                $comando->execute();
                $comando->close();
                $mensagem = "Foto de perfil atualizada!";
                $tipo_mensagem = "sucesso";
            } else {
                $mensagem = $upload; // mensagem de erro da função
                $tipo_mensagem = "erro";
            }
        } else {
            $mensagem = "Escolha uma imagem primeiro.";
            $tipo_mensagem = "erro";
        }
    }

    // remover a foto
    if ($acao == 'remover_foto') {
        if (!empty($dados['foto_url']) && file_exists($dados['foto_url'])) {
            unlink($dados['foto_url']);
        }
        $comando = $conexao->prepare("UPDATE usuarios SET foto_url = '' WHERE id = ?");
        $comando->bind_param("i", $usuario_id);
        $comando->execute();
        $comando->close();
        $mensagem = "Foto removida.";
        $tipo_mensagem = "sucesso";
    }

    // excluir a conta
    if ($acao == 'excluir') {
        if ($dados['usuario'] == 'admin') {
            // o admin é a conta padrão do sistema, então não pode ser apagado
            $mensagem = "A conta admin é a conta padrão do sistema e não pode ser excluída.";
            $tipo_mensagem = "erro";
        } else {
            // primeiro apaga o que está ligado ao usuário (avaliações e lista)
            $comando = $conexao->prepare("DELETE FROM avaliacoes WHERE usuario_id = ?");
            $comando->bind_param("i", $usuario_id);
            $comando->execute();
            $comando->close();

            $comando = $conexao->prepare("DELETE FROM favoritos WHERE usuario_id = ?");
            $comando->bind_param("i", $usuario_id);
            $comando->execute();
            $comando->close();

            // apaga a foto de perfil
            if (!empty($dados['foto_url']) && file_exists($dados['foto_url'])) {
                unlink($dados['foto_url']);
            }

            // agora sim apaga o usuário
            $comando = $conexao->prepare("DELETE FROM usuarios WHERE id = ?");
            $comando->bind_param("i", $usuario_id);
            $comando->execute();
            $comando->close();

            // encerra a sessão e volta pro cadastro
            $_SESSION = array();
            session_destroy();
            header('Location: cadastro.php?conta=excluida');
            exit();
        }
    }

    // atualiza os dados que vão aparecer na tela
    $dados = buscarUsuario($conexao, $usuario_id);
}

// números do perfil
$comando = $conexao->prepare("SELECT COUNT(*) AS total FROM avaliacoes WHERE usuario_id = ?");
$comando->bind_param("i", $usuario_id);
$comando->execute();
$minhas_avaliacoes = $comando->get_result()->fetch_assoc()['total'];
$comando->close();

$comando = $conexao->prepare("SELECT COUNT(*) AS total FROM favoritos WHERE usuario_id = ?");
$comando->bind_param("i", $usuario_id);
$comando->execute();
$minha_lista = $comando->get_result()->fetch_assoc()['total'];
$comando->close();

$tem_foto = !empty($dados['foto_url']) && file_exists($dados['foto_url']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu perfil — Cineflix</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27><rect width=%27100%27 height=%27100%27 rx=%2718%27 fill=%27%23e50914%27/><text x=%2750%27 y=%2772%27 font-size=%2770%27 text-anchor=%27middle%27 fill=%27white%27 font-family=%27Arial%27 font-weight=%27bold%27>C</text></svg>">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php $pagina_atual = 'perfil'; include('menu.php'); ?>

<div class="conteudo">

    <div class="barra-pagina">
        <h2>Meu perfil</h2>
    </div>

    <?php if ($mensagem): ?>
        <div class="mensagem <?php echo $tipo_mensagem; ?>"><?php echo $mensagem; ?></div>
    <?php endif; ?>

    <!-- cartão com a foto e os números -->
    <section class="perfil-topo">
        <?php if ($tem_foto): ?>
            <img src="<?php echo htmlspecialchars($dados['foto_url']); ?>" alt="Foto de perfil" class="perfil-foto">
        <?php else: ?>
            <div class="perfil-foto sem-foto"></div>
        <?php endif; ?>

        <div>
            <h1>@<?php echo htmlspecialchars($dados['usuario']); ?></h1>
            <p><strong><?php echo $minhas_avaliacoes; ?></strong> avaliações · <strong><?php echo $minha_lista; ?></strong> filmes na lista</p>
        </div>
    </section>

    <div class="grade-perfil">

        <!-- foto: enviar e remover lado a lado -->
        <form method="post" action="" enctype="multipart/form-data">
            <h3>Foto de perfil</h3>
            <div class="grupo-campo">
                <input type="file" name="foto" accept="image/*">
                <small>JPG ou PNG · Máximo 5 MB</small>
            </div>
            <div class="acoes-formulario">
                <button type="submit" name="acao" value="foto" class="botao-principal">Enviar foto</button>
                <?php if ($tem_foto): ?>
                    <button type="submit" name="acao" value="remover_foto" class="botao-voltar">Remover foto</button>
                <?php endif; ?>
            </div>
        </form>

        <!-- excluir a conta -->
        <form method="post" action="" class="zona-perigo" onsubmit="return confirm('Tem certeza? Sua conta, suas avaliações e sua lista serão apagadas.')">
            <h3>Excluir conta</h3>
            <p class="texto-suave">Essa ação apaga sua conta, suas avaliações e sua lista. Não dá para desfazer.</p>
            <input type="hidden" name="acao" value="excluir">
            <button type="submit" class="botao-excluir">Excluir minha conta</button>
        </form>

    </div>

</div>
<?php include('rodape.php'); ?>
</body>
</html>
