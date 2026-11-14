<?php
// Rodar no terminal: php geraSenha.php
// Mostra o hash bcrypt da senha, para colocar no INSERT do usuário no banco
echo password_hash('123', PASSWORD_BCRYPT);
