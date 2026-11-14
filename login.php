<?php
// login.php
// se já está logado vai pro index.php, senão confere usuário e senha no banco
session_start();

if (isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}

include('conexao.php');

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($usuario) || empty($senha)) {
        $mensagem = "Preencha todos os campos.";
        $tipo_mensagem = "erro";
    } else {
        // ? é trocado pelo valor com segurança (evita SQL Injection)
        $comando = $conexao->prepare("SELECT * FROM usuarios WHERE usuario = ?");
        $comando->bind_param("s", $usuario);
        $comando->execute();
        $resultado = $comando->get_result();

        if ($resultado->num_rows > 0) {
            $linha = $resultado->fetch_assoc();

            // compara a senha digitada com o hash que está no banco
            if (password_verify($senha, $linha['senha'])) {
                $_SESSION['usuario'] = $usuario;
                $_SESSION['usuario_id'] = $linha['id'];
                header('Location: index.php');
                exit();
            } else {
                $mensagem = "Senha incorreta.";
                $tipo_mensagem = "erro";
            }
        } else {
            $mensagem = "Usuário não encontrado.";
            $tipo_mensagem = "erro";
        }
        $comando->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Cineflix</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27><text y=%27.9em%27 font-size=%2790%27>🎬</text></svg>">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="pagina-login">

    <div class="cartao-login">
        <div class="cabecalho-login">
            <h1>🎬 CINEFLIX</h1>
            <p>Entre com suas credenciais</p>
        </div>

        <?php if ($mensagem): ?>
            <div class="mensagem <?php echo $tipo_mensagem; ?>">
                ⚠️ <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['logout']) && $_GET['logout'] === 'sucesso'): ?>
            <div class="mensagem sucesso">✅ Você saiu do sistema.</div>
        <?php endif; ?>

        <!-- post: os dados não aparecem na URL -->
        <form method="post" action="">
            <div class="grupo-campo">
                <label for="usuario">Usuário</label>
                <input type="text" id="usuario" name="usuario" required>
            </div>

            <div class="grupo-campo">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>

            <button type="submit" class="botao-principal">Entrar</button>
        </form>

        <p class="dica-login">Teste: usuário <strong>admin</strong> / senha <strong>123</strong></p>
    </div>

</body>
</html>
