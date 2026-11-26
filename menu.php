<?php
// menu.php - barra de abas do topo
// as páginas internas dão include aqui depois do <body>
// antes do include a página define $pagina_atual (ex: $pagina_atual = 'inicio';) para marcar a aba ativa

// pega nome e foto do usuário logado para mostrar no canto direito
$comando_menu = $conexao->prepare("SELECT usuario, foto_url FROM usuarios WHERE id = ?");
$id_menu = intval($_SESSION['usuario_id'] ?? 0);
$comando_menu->bind_param("i", $id_menu);
$comando_menu->execute();
$dados_menu = $comando_menu->get_result()->fetch_assoc();
$comando_menu->close();

if (!$dados_menu) {
    $dados_menu = ['usuario' => $_SESSION['usuario'], 'foto_url' => ''];
}

// cada aba: chave => [arquivo, texto]
$abas = [
    'inicio'     => ['index.php', 'Início'],
    'diretores'  => ['cadastro_diretor.php', 'Diretores'],
    'filmes'     => ['cadastro_filme.php', 'Filmes'],
    'biblioteca' => ['biblioteca.php', 'Biblioteca'],
    'minhalista' => ['minha_lista.php', 'Minha lista']
];
?>
<nav class="menu-topo">
    <a href="index.php" class="menu-logo">CINEFLIX</a>

    <ul class="menu-abas">
        <?php foreach ($abas as $chave => $aba): ?>
            <li>
                <a href="<?php echo $aba[0]; ?>" class="aba <?php echo (isset($pagina_atual) && $pagina_atual == $chave) ? 'ativa' : ''; ?>">
                    <?php echo $aba[1]; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- canto direito: perfil e sair -->
    <div class="menu-usuario">
        <a href="perfil.php" class="menu-perfil <?php echo (isset($pagina_atual) && $pagina_atual == 'perfil') ? 'ativa' : ''; ?>">
            <?php if (!empty($dados_menu['foto_url']) && file_exists($dados_menu['foto_url'])): ?>
                <img src="<?php echo htmlspecialchars($dados_menu['foto_url']); ?>" alt="Foto de perfil" class="menu-avatar">
            <?php else: ?>
                <span class="menu-avatar sem-foto"></span>
            <?php endif; ?>
            <span class="menu-nome"><?php echo htmlspecialchars($dados_menu['usuario']); ?></span>
        </a>
        <a href="logout.php" class="menu-sair">Sair</a>
    </div>
</nav>
