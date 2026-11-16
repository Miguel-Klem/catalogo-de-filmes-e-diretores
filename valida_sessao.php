<?php
// Se o usuário não fez login, volta pro login.php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}
