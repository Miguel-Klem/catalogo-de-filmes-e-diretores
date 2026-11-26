<?php
// Funções usadas em mais de uma página, pra não copiar o código toda hora

// pega o id do vídeo no link do YouTube
// (serve pra validar o link do trailer)
function pegarIdYoutube(string $link) {
    // Formatos de link do YouTube
    $padroes = [
        '/youtube\.com\/watch\?v=([^\&\?\/]+)/',
        '/youtube\.com\/embed\/([^\&\?\/]+)/',
        '/youtube\.com\/v\/([^\&\?\/]+)/',
        '/youtu\.be\/([^\&\?\/]+)/'
    ];

    foreach ($padroes as $padrao) {
        // preg_match testa se o link bate com o formato
        if (preg_match($padrao, $link, $achados)) {
            return $achados[1];
        }
    }
    return ""; // link inválido
}

// valida, redimensiona (GD) e salva a imagem na pasta img
// devolve o caminho da imagem ou a mensagem de erro
function redimensionarESalvarImagem(array $arquivo, $largura = 300, $altura = 300) {
    $caminho = "img/" . uniqid() . '_' . basename($arquivo["name"]);
    $tipo = strtolower(pathinfo($caminho, PATHINFO_EXTENSION));

    // validações
    if (!getimagesize($arquivo["tmp_name"])) return "Arquivo não é uma imagem válida.";
    if ($arquivo["size"] > 5000000) return "Imagem muito grande. Máximo: 5 MB.";
    if (!in_array($tipo, ["jpg", "jpeg", "png"])) return "Apenas JPG e PNG são aceitos.";

    // calcula o tamanho novo mantendo a proporção
    $original = imagecreatefromstring(file_get_contents($arquivo["tmp_name"]));
    $largura_original = imagesx($original);
    $altura_original = imagesy($original);
    $proporcao = min($largura / $largura_original, $altura / $altura_original);
    $nova_largura = (int)($largura_original * $proporcao);
    $nova_altura = (int)($altura_original * $proporcao);

    // cria a imagem menor e salva
    $nova = imagecreatetruecolor($nova_largura, $nova_altura);
    imagecopyresampled($nova, $original, 0, 0, 0, 0, $nova_largura, $nova_altura, $largura_original, $altura_original);

    if ($tipo === "png") {
        imagepng($nova, $caminho);
    } else {
        imagejpeg($nova, $caminho, 90);
    }

    return $caminho;
}

// -----------------------------------------------
// média das notas de um filme
// devolve a média e quantas avaliações ele tem
// -----------------------------------------------
function mediaDoFilme($conexao, $filme_id) {
    $comando = $conexao->prepare("SELECT nota FROM avaliacoes WHERE filme_id = ?");
    $comando->bind_param("i", $filme_id);
    $comando->execute();
    $resultado = $comando->get_result();

    $soma = 0;
    $total = 0;
    while ($linha = $resultado->fetch_assoc()) {
        $soma = $soma + $linha['nota'];
        $total = $total + 1;
    }
    $comando->close();

    $media = 0;
    if ($total > 0) {
        $media = $soma / $total;
    }
    return ['media' => $media, 'total' => $total];
}

// transforma uma nota (0 a 5) em estrelas: 4 vira ★★★★☆
function estrelas($nota) {
    $cheias = intval($nota + 0.5);
    return str_repeat('★', $cheias) . str_repeat('☆', 5 - $cheias);
}

// apaga as avaliações e os favoritos de um filme (tem que ser antes de apagar o filme)
function excluirAvaliacoesEFavoritos($conexao, $filme_id) {
    $comando = $conexao->prepare("DELETE FROM avaliacoes WHERE filme_id = ?");
    $comando->bind_param("i", $filme_id);
    $comando->execute();
    $comando->close();

    $comando = $conexao->prepare("DELETE FROM favoritos WHERE filme_id = ?");
    $comando->bind_param("i", $filme_id);
    $comando->execute();
    $comando->close();
}

// -----------------------------------------------
// desenha o cartão de um filme (usado no início, na biblioteca e na minha lista)
// $extra = botões que aparecem embaixo do cartão (opcional)
// -----------------------------------------------
function mostrarCartaoFilme($conexao, $filme, $extra = '', $posicao = 0) {
    $avaliacao = mediaDoFilme($conexao, $filme['id']);

    echo '<div class="cartao-filme">';
    echo '<a href="filme.php?id=' . $filme['id'] . '" class="cartao-link">';

    // se o arquivo do pôster não existir na pasta img, mostra um quadro em branco
    if (!empty($filme['poster_url']) && file_exists($filme['poster_url'])) {
        echo '<img class="cartao-poster" src="' . htmlspecialchars($filme['poster_url']) . '" alt="Pôster">';
    } else {
        echo '<div class="cartao-sem-poster"></div>';
    }

    echo '<div class="cartao-info">';
    if ($posicao > 0) {
        echo '<div class="cartao-posicao">' . $posicao . 'º</div>';
    }
    echo '<div class="cartao-titulo">' . htmlspecialchars($filme['titulo']) . '</div>';
    if (!empty($filme['diretor_nome'])) {
        echo '<div class="cartao-sub">' . htmlspecialchars($filme['diretor_nome']) . '</div>';
    }
    if ($avaliacao['total'] > 0) {
        echo '<div class="cartao-nota"><span class="estrelas">' . estrelas($avaliacao['media']) . '</span> '
            . number_format($avaliacao['media'], 1, ',', '') . ' (' . $avaliacao['total'] . ')</div>';
    } else {
        echo '<div class="cartao-nota sem-nota">Sem avaliações</div>';
    }
    echo '</div></a>';

    if ($extra != '') {
        echo '<div class="cartao-acoes">' . $extra . '</div>';
    }
    echo '</div>';
}
