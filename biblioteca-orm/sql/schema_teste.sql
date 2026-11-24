-- Modelo relacional da Aula 11 (MySQL 8 / InnoDB).
CREATE DATABASE IF NOT EXISTS biblioteca_orm_teste CHARACTER SET utf8mb4;
USE biblioteca_orm_teste;

CREATE TABLE IF NOT EXISTS livros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    isbn VARCHAR(13) UNIQUE,
    ano_publicacao INT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS autores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    nacionalidade VARCHAR(80) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS livros_autores (
    livro_id INT NOT NULL,
    autor_id INT NOT NULL,
    PRIMARY KEY (livro_id, autor_id),
    FOREIGN KEY (livro_id) REFERENCES livros(id),
    FOREIGN KEY (autor_id) REFERENCES autores(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS exemplares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    livro_id INT NOT NULL,
    codigo_patrimonio VARCHAR(30) NOT NULL UNIQUE,
    status ENUM('disponivel', 'emprestado', 'manutencao') NOT NULL DEFAULT 'disponivel',
    FOREIGN KEY (livro_id) REFERENCES livros(id)
) ENGINE=InnoDB;
