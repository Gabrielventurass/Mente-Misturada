create database mente;
use mente;

CREATE TABLE usuario (
  email VARCHAR(45) PRIMARY KEY,
  nome VARCHAR(45),
  senha VARCHAR(255)
) ENGINE=InnoDB;
ALTER TABLE usuario
ADD COLUMN pontuacao INT;
create table admin(
	codigo int auto_increment primary key,
	email varchar(45) UNIQUE,
	nome varchar(45),
	senha varchar(255),
	dt_cr datetime DEFAULT CURRENT_TIMESTAMP,
    pendente TINYINT(1) NOT NULL DEFAULT 1
);

create table admins_pendentes(
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

CREATE TABLE resposta_comunidade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_email VARCHAR(45),             -- Alterar de comunidade_email para usuario_email
    quiz_id INT,
    pergunta_id INT,                       -- Coluna para armazenar a pergunta respondida
    alternativa_id INT,                     -- Coluna para armazenar a alternativa escolhida
    acertos INT,                            -- Para contar os acertos, se necessário
    tempo INT,                              -- Tempo gasto para responder
    pontuacao INT NOT NULL DEFAULT 0,
    data_resposta DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quiz(id),
    FOREIGN KEY (usuario_email) REFERENCES usuario(email),
    FOREIGN KEY (pergunta_id) REFERENCES perguntas_user(id),  -- Corrigir para perguntas_user
    FOREIGN KEY (alternativa_id) REFERENCES alternativas(id)   -- Corrigir para alternativas
);

CREATE TABLE quizzes_user (
    id INT AUTO_INCREMENT PRIMARY KEY,        -- ID do Quiz
    titulo VARCHAR(255) NOT NULL,             -- Título do Quiz
    descricao TEXT,                           -- Descrição do Quiz
    usuario_email VARCHAR(255),               -- Email do Usuário que criou o Quiz
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Data de criação do Quiz
    FOREIGN KEY (usuario_email) REFERENCES usuario(email) -- Relacionamento com a tabela de usuários
);

CREATE TABLE perguntas_user (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    quiz_id INT,                        
    texto TEXT NOT NULL,                
    FOREIGN KEY (quiz_id) REFERENCES quizzes_user(id) 
);

CREATE TABLE alternativas (
    id INT AUTO_INCREMENT PRIMARY KEY,   
    pergunta_id INT,                     
    texto TEXT NOT NULL,                 
    correta BOOLEAN NOT NULL DEFAULT 0,   
    FOREIGN KEY (pergunta_id) REFERENCES perguntas_user(id) 
);


UPDATE quiz q
SET total_questoes = (
    SELECT COUNT(*) FROM pergunta p WHERE p.quiz_id = q.id
);
SHOW ENGINE INNODB STATUS;
SELECT id, tema, total_questoes FROM quiz;

ALTER TABLE resposta_usuario
ADD COLUMN pontuacao INT NOT NULL DEFAULT 0;

ALTER TABLE quizzes_user
ADD COLUMN tema VARCHAR(255) NOT NULL AFTER descricao;

ALTER TABLE usuario
MODIFY COLUMN pontuacao INT NOT NULL DEFAULT 0;

ALTER TABLE usuario ADD COLUMN cor TINYINT(1) DEFAULT 0;


DESCRIBE usuario;
SET GLOBAL wait_timeout = 60;
SHOW PROCESSLIST;
kill 1772;
SET GLOBAL max_connections = 800;
SHOW STATUS LIKE 'Threads_connected';


select * from usuario;
select * from admin;
select * from quiz;
select * from pergunta;
select * from alternativa;
select * from resposta_usuario;