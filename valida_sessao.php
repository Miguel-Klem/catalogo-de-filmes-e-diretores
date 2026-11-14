<?php
// valida_sessao.php
// se o usuário não fez login, volta pro login.php
// (as páginas internas dão include nesse arquivo no começo)
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit(); // sem o exit o PHP continua rodando
}
