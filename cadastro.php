<?php
// cadastro.php - tela principal do sistema
// quem ainda não tem conta cria uma aqui
// quem já tem conta clica em "Já tenho conta" e vai pro login.php
session_start();

// se já está logado, vai direto pro início
if (isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}

include('conexao.php');

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($usuario) || empty($senha)) {
        $mensagem = "Preencha todos os campos.";
        $tipo_mensagem = "erro";
    } elseif (!preg_match('/^[a-zA-Z0-9_.]+$/', $usuario)) {
        // só letras, números, _ e .
        $mensagem = "O usuário só pode ter letras, números, ponto e underline (sem espaços).";
        $tipo_mensagem = "erro";
    } elseif (strlen($senha) < 4) {
        $mensagem = "A senha precisa ter pelo menos 4 caracteres.";
        $tipo_mensagem = "erro";
    } else {
        // confere se o usuário já existe
        $comando = $conexao->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $comando->bind_param("s", $usuario);
        $comando->execute();
        $existe = $comando->get_result()->num_rows;
        $comando->close();

        if ($existe > 0) {
            $mensagem = "Esse usuário já existe. Escolha outro ou entre na sua conta.";
            $tipo_mensagem = "erro";
        } else {
            // a senha é guardada com bcrypt, igual no login
            $hash = password_hash($senha, PASSWORD_BCRYPT);

            $comando = $conexao->prepare("INSERT INTO usuarios (usuario, senha) VALUES (?, ?)");
            $comando->bind_param("ss", $usuario, $hash);

            if ($comando->execute()) {
                $comando->close();
                header('Location: login.php?cadastro=sucesso');
                exit();
            } else {
                $mensagem = "Erro ao criar a conta: " . $comando->error;
                $tipo_mensagem = "erro";
                $comando->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar conta — Cineflix</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27><rect width=%27100%27 height=%27100%27 rx=%2718%27 fill=%27%23e50914%27/><text x=%2750%27 y=%2772%27 font-size=%2770%27 text-anchor=%27middle%27 fill=%27white%27 font-family=%27Arial%27 font-weight=%27bold%27>C</text></svg>">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="pagina-login">

    <div class="cartao-login">
        <div class="cabecalho-login">
            <h1>CINEFLIX</h1>
            <p>Crie sua conta para começar</p>
        </div>

        <?php if ($mensagem): ?>
            <div class="mensagem <?php echo $tipo_mensagem; ?>"><?php echo $mensagem; ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['conta']) && $_GET['conta'] === 'excluida'): ?>
            <div class="mensagem sucesso">Sua conta foi excluída.</div>
        <?php endif; ?>

        <form method="post" action="">

            <div class="grupo-campo">
                <label for="usuario">Usuário</label>
                <input type="text" id="usuario" name="usuario" value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>" required>
            </div>

            <div class="grupo-campo">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>


            <button type="submit" class="botao-principal">Criar conta</button>
        </form>

        <p class="dica-login">Já tem uma conta? <a href="login.php" class="link-destaque">Já tenho conta</a></p>
    </div>

</body>
</html>
