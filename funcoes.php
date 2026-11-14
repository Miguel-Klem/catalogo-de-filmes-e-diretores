<?php
/*
 * ARQUIVO: funcoes.php
 * FUNÇÃO: funções que mais de uma página usa
 * (assim não precisamos copiar o mesmo código em vários arquivos)
 */

// -----------------------------------------------
// Pega o ID do vídeo a partir do link do YouTube
// Usado para validar o link do trailer
// -----------------------------------------------
function pegarIdYoutube(string $link) {
    // Cada item é um formato de link diferente do YouTube
    $padroes = [
        '/youtube\.com\/watch\?v=([^\&\?\/]+)/',
        '/youtube\.com\/embed\/([^\&\?\/]+)/',
        '/youtube\.com\/v\/([^\&\?\/]+)/',
        '/youtu\.be\/([^\&\?\/]+)/'
    ];

    foreach ($padroes as $padrao) {
        // preg_match testa se o link bate com o padrão
        if (preg_match($padrao, $link, $achados)) {
            return $achados[1];
        }
    }
    return ""; // não achou nenhum ID: link inválido
}

// -----------------------------------------------
// Valida, redimensiona (biblioteca GD) e salva a imagem na pasta img/
// Devolve o caminho da imagem se deu certo, ou a mensagem de erro
// -----------------------------------------------
function redimensionarESalvarImagem(array $arquivo, $largura = 300, $altura = 300) {
    $caminho = "img/" . uniqid() . '_' . basename($arquivo["name"]);
    $tipo = strtolower(pathinfo($caminho, PATHINFO_EXTENSION));

    // Validações básicas
    if (!getimagesize($arquivo["tmp_name"])) return "Arquivo não é uma imagem válida.";
    if ($arquivo["size"] > 5000000) return "Imagem muito grande. Máximo: 5 MB.";
    if (!in_array($tipo, ["jpg", "jpeg", "png"])) return "Apenas JPG e PNG são aceitos.";

    // Abre a imagem original e calcula o novo tamanho mantendo a proporção
    $original = imagecreatefromstring(file_get_contents($arquivo["tmp_name"]));
    $largura_original = imagesx($original);
    $altura_original = imagesy($original);
    $proporcao = min($largura / $largura_original, $altura / $altura_original);
    $nova_largura = (int)($largura_original * $proporcao);
    $nova_altura = (int)($altura_original * $proporcao);

    // Cria a imagem nova, menor, e salva
    $nova = imagecreatetruecolor($nova_largura, $nova_altura);
    imagecopyresampled($nova, $original, 0, 0, 0, 0, $nova_largura, $nova_altura, $largura_original, $altura_original);

    if ($tipo === "png") {
        imagepng($nova, $caminho);
    } else {
        imagejpeg($nova, $caminho, 90);
    }

    imagedestroy($original);
    imagedestroy($nova);
    return $caminho;
}
