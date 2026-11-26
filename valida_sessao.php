<?php
// Se o usuário não fez login, manda pro cadastro.php (a tela principal do sistema)
// quem já tem conta clica lá em "Já tenho conta" e vai pro login.php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: cadastro.php');
    exit();
}
