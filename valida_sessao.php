<?php
/*
 * ARQUIVO: valida_sessao.php
 * FUNÇÃO: barrar quem não fez login
 *
 * Este arquivo é incluído no começo de todas as páginas internas.
 * Se não existir usuário na sessão, manda para o login.
 */
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit(); // sem o exit o PHP continuaria rodando o resto da página
}
