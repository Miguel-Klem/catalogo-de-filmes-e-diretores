<?php
// logout.php - sair do sistema
session_start();

// Limpa a sessão
$_SESSION = array();

// Apaga o cookie da sessão
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Destrói a sessão e volta pro login
session_destroy();
header('Location: login.php?logout=sucesso');
exit();
