<?php
// Dados do banco
$servidor = "localhost";
$usuario_bd = "root";
$senha_bd = "Senai@118";
$nome_banco = "cinema_senai";

// Criando a conexão com mysqli
$conexao = new mysqli($servidor, $usuario_bd, $senha_bd, $nome_banco);

// Se deu erro na conexão, para tudo e mostra o erro
if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}

// utf8mb4 - Permite acentuação e emojis
$conexao->set_charset("utf8mb4");

// Cria a pasta img (onde ficam os pôsteres e fotos) se ela ainda não existir
if (!file_exists("img")) {
    mkdir("img", 0777, true);
}
