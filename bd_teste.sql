-- Banco de dados para o catálogo de filmes - Versão teste

CREATE DATABASE catalogo_filmes;
USE catalogo_filmes;

-- usuários do sistema
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL,
    senha VARCHAR(255) NOT NULL
);

-- diretores
CREATE TABLE diretores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    pais VARCHAR(50),
    genero VARCHAR(50),
    imagem VARCHAR(255)
);

-- filmes (cada filme tem um diretor)
CREATE TABLE filmes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    diretor_id INT,
    titulo VARCHAR(100) NOT NULL,
    ano INT,
    genero VARCHAR(50),
    trailer_link VARCHAR(255),
    sinopse TEXT,
    imagem_poster VARCHAR(255),
    FOREIGN KEY (diretor_id) REFERENCES diretores(id)
);

-- usuário admin, senha 123 (hash bcrypt, o mesmo do bd.sql de artistas-musicas)
-- se o login não funcionar: rodar geraSenha.php e trocar o hash aqui
INSERT INTO usuarios (usuario, senha)
VALUES ('admin', '$2y$12$zw1c2aDgt4HHnAzXc3g3dOV6Mn96Dm2UaSIketIhYe1xmtuaJG6qa');