-- =========================================================================
-- Banco de dados do Cineflix (catálogo de filmes e diretores) - Consolidado
-- =========================================================================

DROP DATABASE IF EXISTS cinema_senai;
CREATE DATABASE cinema_senai;

USE cinema_senai;

-- Tabela de usuários (login e perfil)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    foto_url VARCHAR(255)
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

-- Tabela de avaliações (cada usuário avalia um filme uma vez, nota de 1 a 5)
CREATE TABLE avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filme_id INT NOT NULL,
    usuario_id INT NOT NULL,
    nota INT NOT NULL,
    comentario TEXT,
    UNIQUE (filme_id, usuario_id),
    FOREIGN KEY (filme_id) REFERENCES filmes(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Tabela de favoritos (a "Minha lista" de cada usuário)
CREATE TABLE favoritos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filme_id INT NOT NULL,
    usuario_id INT NOT NULL,
    UNIQUE (filme_id, usuario_id),
    FOREIGN KEY (filme_id) REFERENCES filmes(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Índices para a busca e os filtros da Biblioteca
CREATE INDEX idx_filmes_titulo ON filmes(titulo);
CREATE INDEX idx_filmes_genero ON filmes(genero);

-- =========================================================================
-- Carga inicial de dados
-- =========================================================================

-- Usuário padrão: admin / senha 123 (a senha fica em hash bcrypt)
INSERT INTO usuarios (usuario, senha) VALUES
('admin', '$2y$12$zw1c2aDgt4HHnAzXc3g3dOV6Mn96Dm2UaSIketIhYe1xmtuaJG6qa');

-- Diretores e filmes aclamados pela crítica (aparecem no início, na ordem do ranking)
-- as imagens ficam na pasta img com os nomes abaixo (veja img/nomes_das_imagens.txt)
INSERT INTO diretores (nome, estilo, pais, foto_url) VALUES
('Frank Darabont', 'Drama', 'Estados Unidos', 'img/diretor_frank-darabont.jpg'),
('Francis Ford Coppola', 'Crime', 'Estados Unidos', 'img/diretor_francis-ford-coppola.jpg'),
('Christopher Nolan', 'Ficção Científica', 'Reino Unido', 'img/diretor_christopher-nolan.jpg'),
('Peter Jackson', 'Fantasia', 'Nova Zelândia', 'img/diretor_peter-jackson.jpg'),
('Sidney Lumet', 'Drama', 'Estados Unidos', 'img/diretor_sidney-lumet.jpg'),
('Steven Spielberg', 'Aventura', 'Estados Unidos', 'img/diretor_steven-spielberg.jpg'),
('Quentin Tarantino', 'Crime', 'Estados Unidos', 'img/diretor_quentin-tarantino.jpg'),
('Sergio Leone', 'Faroeste', 'Itália', 'img/diretor_sergio-leone.jpg'),
('Robert Zemeckis', 'Drama', 'Estados Unidos', 'img/diretor_robert-zemeckis.jpg'),
('David Fincher', 'Suspense', 'Estados Unidos', 'img/diretor_david-fincher.jpg'),
('Irvin Kershner', 'Ficção Científica', 'Estados Unidos', 'img/diretor_irvin-kershner.jpg');

INSERT INTO filmes (diretor_id, titulo, ano_estudio, genero, trailer_url, poster_url, sinopse) VALUES
(1, 'Um Sonho de Liberdade', '1994', 'Drama', '', 'img/poster_um-sonho-de-liberdade.jpg', 'Um banqueiro condenado injustamente à prisão perpétua faz amizade na cadeia e encontra um jeito de manter a esperança viva.'),
(2, 'O Poderoso Chefão', '1972', 'Crime', '', 'img/poster_o-poderoso-chefao.jpg', 'A história da família Corleone, uma poderosa família da máfia, e da difícil passagem do poder de pai para filho.'),
(3, 'Batman: O Cavaleiro das Trevas', '2008', 'Ação', '', 'img/poster_batman-o-cavaleiro-das-trevas.jpg', 'Batman enfrenta o Coringa, que mergulha Gotham no caos e testa os limites do herói e da cidade.'),
(2, 'O Poderoso Chefão: Parte II', '1974', 'Crime', '', 'img/poster_o-poderoso-chefao-2.jpg', 'Mostra a juventude de Vito Corleone e, ao mesmo tempo, Michael lutando para manter o poder da família.'),
(4, 'O Senhor dos Anéis: O Retorno do Rei', '2003', 'Fantasia', '', 'img/poster_senhor-dos-aneis-o-retorno-do-rei.jpg', 'Na batalha final pela Terra-média, Frodo e Sam seguem rumo à Montanha da Perdição enquanto Aragorn lidera a luta contra Sauron.'),
(5, '12 Homens e uma Sentença', '1957', 'Drama', '', 'img/poster_12-homens-e-uma-sentenca.jpg', 'Doze jurados precisam decidir o destino de um jovem acusado de assassinato, e a dúvida de um deles muda toda a discussão.'),
(6, 'A Lista de Schindler', '1993', 'Drama', '', 'img/poster_a-lista-de-schindler.jpg', 'Um empresário alemão usa sua fábrica para salvar mais de mil judeus durante o Holocausto.'),
(4, 'O Senhor dos Anéis: A Sociedade do Anel', '2001', 'Fantasia', '', 'img/poster_senhor-dos-aneis-a-sociedade-do-anel.jpg', 'Um jovem hobbit recebe um anel poderoso e forma uma sociedade para destruí-lo antes que o vilão o recupere.'),
(7, 'Pulp Fiction: Tempo de Violência', '1994', 'Crime', '', 'img/poster_pulp-fiction.jpg', 'Histórias de criminosos de Los Angeles se cruzam em episódios violentos, engraçados e inesperados.'),
(4, 'O Senhor dos Anéis: As Duas Torres', '2002', 'Fantasia', '', 'img/poster_senhor-dos-aneis-as-duas-torres.jpg', 'A sociedade se divide: Frodo e Sam avançam rumo a Mordor, enquanto os outros heróis defendem Rohan de um grande ataque.'),
(8, 'Três Homens em Conflito', '1966', 'Faroeste', '', 'img/poster_tres-homens-em-conflito.jpg', 'Três pistoleiros disputam um tesouro enterrado em meio à Guerra Civil americana.'),
(9, 'Forrest Gump - O Contador de Histórias', '1994', 'Drama', '', 'img/poster_forrest-gump.jpg', 'Forrest, um homem simples e de bom coração, participa sem querer de grandes momentos da história dos Estados Unidos.'),
(3, 'A Origem', '2010', 'Ficção Científica', '', 'img/poster_a-origem.jpg', 'Um ladrão especializado em invadir sonhos recebe a missão de plantar uma ideia na mente de um empresário.'),
(10, 'Clube da Luta', '1999', 'Drama', '', 'img/poster_clube-da-luta.jpg', 'Um homem entediado com a vida forma, com um vendedor carismático, um clube clandestino que foge do controle.'),
(11, 'Star Wars: Episódio V - O Império Contra-Ataca', '1980', 'Ficção Científica', '', 'img/poster_star-wars-o-imperio-contra-ataca.jpg', 'Com o Império no encalço, os rebeldes se espalham: Luke treina com Yoda enquanto Han e Leia fogem de Darth Vader.');