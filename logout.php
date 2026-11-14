<?php
/*
 * ARQUIVO: logout.php
 * FUNÇÃO: sair do sistema (encerrar a sessão)
 */
session_start();

// Limpa tudo que estava guardado na sessão
$_SESSION = array();

// Apaga o cookie da sessão no navegador
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Destrói a sessão no servidor e volta para o login
session_destroy();
header('Location: login.php?logout=sucesso');
exit();
