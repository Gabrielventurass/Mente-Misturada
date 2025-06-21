create database mente;
use mente;

CREATE TABLE usuario (
  email VARCHAR(45) PRIMARY KEY,
  nome VARCHAR(45),
  senha VARCHAR(255)
) ENGINE=InnoDB;
            
CREATE TABLE admins_pendentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    email VARCHAR(100),
    senha VARCHAR(255),
    token_aprovacao VARCHAR(64),
    dt_cr DATETIME DEFAULT CURRENT_TIMESTAMP
);

create table admin(
	codigo int auto_increment primary key,
	email varchar(45) UNIQUE,
	nome varchar(45),
	senha varchar(255),
	dt_cr datetime DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE quiz (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tema VARCHAR(255),
    art TEXT,
    total_questoes INT
);

CREATE TABLE pergunta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT,
    texto TEXT,
    resposta_correta VARCHAR(255),
    FOREIGN KEY (quiz_id) REFERENCES quiz(id) ON DELETE CASCADE
);


CREATE TABLE alternativa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pergunta_id INT,
    texto VARCHAR(255),
    correta BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (pergunta_id) REFERENCES pergunta(id) ON DELETE CASCADE
);

CREATE TABLE resposta_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_email VARCHAR(45),
    quiz_id INT,
    acertos INT,
    tempo INT,
    data_resposta DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quiz(id),
    FOREIGN KEY (usuario_email) REFERENCES usuario(email)
) ENGINE=InnoDB;

UPDATE quiz q
SET total_questoes = (
    SELECT COUNT(*) FROM pergunta p WHERE p.quiz_id = q.id
);
SHOW ENGINE INNODB STATUS;
SELECT id, tema, total_questoes FROM quiz;


select * from usuario;
select * from admins_pendentes;
select * from admin;
select * from quiz;
select * from pergunta;
select * from alternativa;
select * from resposta_usuario;