-- bd_teste.sql
-- banco só para TESTAR o sistema no Workbench (o bd.sql oficial é do grupo do banco)
-- como usar: abrir no MySQL Workbench e executar tudo (raio)

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

-- dados de exemplo
INSERT INTO diretores (nome, pais, genero, imagem) VALUES
('Christopher Nolan', 'Reino Unido', 'Ficção Científica', ''),
('Quentin Tarantino', 'EUA', 'Ação', ''),
('Fernando Meirelles', 'Brasil', 'Drama', '');

INSERT INTO filmes (diretor_id, titulo, ano, genero, trailer_link, sinopse, imagem_poster) VALUES
(1, 'Interestelar', 2014, 'Ficção Científica', 'https://www.youtube.com/watch?v=zSWdZVtXT7E', 'Um grupo de astronautas viaja por um buraco de minhoca em busca de um novo planeta para a humanidade.', ''),
(1, 'A Origem', 2010, 'Ficção Científica', 'https://www.youtube.com/watch?v=YoHD9XEInc0', 'Um ladrão invade sonhos para roubar segredos e recebe a missão de plantar uma ideia.', ''),
(2, 'Pulp Fiction', 1994, 'Ação', 'https://www.youtube.com/watch?v=s7EdQ4FqbhY', 'Histórias de criminosos de Los Angeles que se cruzam.', ''),
(3, 'Cidade de Deus', 2002, 'Drama', 'https://www.youtube.com/watch?v=dcUOO4Itgmw', 'A vida de jovens numa comunidade do Rio de Janeiro entre o crime e o sonho de sair dali.', '');
