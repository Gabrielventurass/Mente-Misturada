CREATE DATABASE mente;
USE mente;

-- Tabela de Usuários
CREATE TABLE usuario (
  email VARCHAR(45) PRIMARY KEY,
  nome VARCHAR(45),
  senha VARCHAR(255),
  pontuacao INT DEFAULT 0 -- Pontuação do usuário, com valor inicial 0
) ENGINE=InnoDB;

-- Tabela de Administradores
CREATE TABLE admin (
  codigo INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(45) UNIQUE,
  nome VARCHAR(45),
  senha VARCHAR(255),
  dt_cr DATETIME DEFAULT CURRENT_TIMESTAMP,
  pendente TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- Tabela de Administradores Pendentes
CREATE TABLE admins_pendentes (
  codigo INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(45) UNIQUE,
  nome VARCHAR(45),
  senha VARCHAR(255),
  dt_cr DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de Quizzes
CREATE TABLE quiz (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tema VARCHAR(255),
  art TEXT,
  total_questoes INT -- Total de questões do quiz
) ENGINE=InnoDB;

-- Tabela de Perguntas (relacionada ao quiz)
CREATE TABLE pergunta (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT,
  texto TEXT,
  resposta_correta VARCHAR(255),
  FOREIGN KEY (quiz_id) REFERENCES quiz(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela de Alternativas (relacionada a cada pergunta)
CREATE TABLE alternativa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pergunta_id INT,
  texto VARCHAR(255),
  correta BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (pergunta_id) REFERENCES pergunta(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela de Respostas dos Usuários
CREATE TABLE resposta_usuario (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_email VARCHAR(45),
  quiz_id INT,
  acertos INT,
  tempo INT,
  pontuacao INT NOT NULL DEFAULT 0, -- Pontuação obtida no quiz
  data_resposta DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (quiz_id) REFERENCES quiz(id),
  FOREIGN KEY (usuario_email) REFERENCES usuario(email)
) ENGINE=InnoDB;

-- Tabela de Respostas da Comunidade
CREATE TABLE resposta_comunidade (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_email VARCHAR(45),
  quiz_id INT,
  pergunta_id INT,
  alternativa_id INT,
  acertos INT,
  tempo INT,
  pontuacao INT NOT NULL DEFAULT 0,
  data_resposta DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (quiz_id) REFERENCES quiz(id),
  FOREIGN KEY (usuario_email) REFERENCES usuario(email),
  FOREIGN KEY (pergunta_id) REFERENCES perguntas_user(id),
  FOREIGN KEY (alternativa_id) REFERENCES alternativas(id)
) ENGINE=InnoDB;

-- Tabela de Quizzes Criados pelos Usuários
CREATE TABLE quizzes_user (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(255) NOT NULL,
  descricao TEXT,
  usuario_email VARCHAR(255),
  tema VARCHAR(255) NOT NULL, -- Tema do quiz
  data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_email) REFERENCES usuario(email)
) ENGINE=InnoDB;

-- Tabela de Perguntas Criadas pelos Usuários (relacionada aos quizzes criados pelos usuários)
CREATE TABLE perguntas_user (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT,
  texto TEXT NOT NULL,
  FOREIGN KEY (quiz_id) REFERENCES quizzes_user(id)
) ENGINE=InnoDB;

-- Tabela de Alternativas Criadas pelos Usuários (relacionadas às perguntas criadas pelos usuários)
CREATE TABLE alternativas_user (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pergunta_id INT,
  texto TEXT NOT NULL,
  correta BOOLEAN NOT NULL DEFAULT 0,
  FOREIGN KEY (pergunta_id) REFERENCES perguntas_user(id)
) ENGINE=InnoDB;

-- Atualizando a coluna `total_questoes` da tabela `quiz` para refletir o número total de questões de cada quiz
UPDATE quiz q
SET total_questoes = (
    SELECT COUNT(*) FROM pergunta p WHERE p.quiz_id = q.id
);

-- Consultas de exemplo para verificar os dados
SELECT * FROM usuario;
SELECT * FROM admin;
SELECT * FROM quiz;
SELECT * FROM pergunta;
SELECT * FROM alternativa;
SELECT * FROM resposta_usuario;
SELECT * FROM resposta_comunidade;
