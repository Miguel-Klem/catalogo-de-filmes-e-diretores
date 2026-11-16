-- Banco de dados do Cineflix (catálogo de filmes e diretores)

CREATE DATABASE IF NOT EXISTS cinema_senai;
USE cinema_senai;

-- Tabela de usuários (login)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);

-- Tabela de diretores
CREATE TABLE diretores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    estilo VARCHAR(100) NOT NULL,
    pais VARCHAR(80) NOT NULL,
    foto_url VARCHAR(255)
);

-- Tabela de filmes (cada filme pertence a um diretor)
CREATE TABLE filmes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    diretor_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    ano_estudio VARCHAR(100),
    genero VARCHAR(80) NOT NULL,
    trailer_url VARCHAR(255),
    poster_url VARCHAR(255),
    sinopse TEXT,
    FOREIGN KEY (diretor_id) REFERENCES diretores(id)
);

-- Índices para a busca e os filtros da Biblioteca
CREATE INDEX idx_filmes_titulo ON filmes(titulo);
CREATE INDEX idx_filmes_genero ON filmes(genero);

-- Usuário padrão: admin / senha 123 (a senha fica em hash bcrypt)
INSERT INTO usuarios (nome, usuario, senha) VALUES
('Administrador', 'admin', '$2y$12$zw1c2aDgt4HHnAzXc3g3dOV6Mn96Dm2UaSIketIhYe1xmtuaJG6qa');