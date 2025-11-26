	create database if not exists mente;
	use mente;


	CREATE TABLE usuario (
	  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	  email VARCHAR(45),
	  nome VARCHAR(45),
	  senha VARCHAR(255),
	  pontuacao INT NOT NULL DEFAULT 0,
      cor TINYINT(1) DEFAULT 0,
      is_admin tinyint(1) NOT NULL DEFAULT 0
	) ENGINE=InnoDB;

	CREATE TABLE IF NOT EXISTS admin (
	  codigo INT AUTO_INCREMENT PRIMARY KEY,
	  email VARCHAR(100) UNIQUE NOT NULL,
	  nome VARCHAR(100) NOT NULL,
	  senha VARCHAR(255) NOT NULL,
	  dt_cr DATETIME DEFAULT CURRENT_TIMESTAMP,
	  pendente TINYINT(1) NOT NULL DEFAULT 0,
      token_recuperacao VARCHAR(255) NULL,
	  token_expira DATETIME NULL
	);
	INSERT INTO admin 
(codigo, email, nome, senha, dt_cr, pendente, token_recuperacao, token_expira)
VALUES
(0, 'adm@gmail', 'adm', '$2y$10$gj.4qaYvf3o7vPhCjUz1U.tUXC.MgkmJrtz8NcCj1lSR4HRS7RO0G', NOW(), 0, NULL, NULL);

	-- Tabela temporária para admins pendentes (aguardando aprovação)
	CREATE TABLE IF NOT EXISTS admins_pendentes (
	  codigo INT AUTO_INCREMENT PRIMARY KEY,
	  email VARCHAR(100) UNIQUE NOT NULL,
	  nome VARCHAR(100) NOT NULL,
	  senha VARCHAR(255) NOT NULL,
	  dt_cr DATETIME DEFAULT CURRENT_TIMESTAMP
	);

	CREATE TABLE admin_recuperacao (
		id INT AUTO_INCREMENT PRIMARY KEY,
		email VARCHAR(255) NOT NULL,
		token VARCHAR(255) NOT NULL,
		data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
		expiracao DATETIME NOT NULL,
		FOREIGN KEY (email) REFERENCES admin(email) ON DELETE CASCADE
	);

	CREATE TABLE quiz (
		id INT AUTO_INCREMENT PRIMARY KEY,
		tema VARCHAR(255),
		art TEXT,
		total_questoes INT,
        titulo VARCHAR(255),
		descricao TEXT,
        origem ENUM('sql','json') DEFAULT 'sql'
	) ENGINE=InnoDB;

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
select * from quiz;
select * from alternativas;
select * from pergunta;
	CREATE TABLE resposta_usuario (
		id INT AUTO_INCREMENT PRIMARY KEY,
		usuario_id INT unsigned,
		quiz_id INT ,
		acertos INT,
		tempo INT,
		data_resposta DATETIME DEFAULT CURRENT_TIMESTAMP,
        pontuacao INT NOT NULL DEFAULT 0,
		FOREIGN KEY (quiz_id) REFERENCES quiz(id),
		FOREIGN KEY (usuario_id) REFERENCES usuario(id)
	) ENGINE=InnoDB;

	CREATE TABLE quizzes_user (
		id INT AUTO_INCREMENT PRIMARY KEY,        
		titulo VARCHAR(255) NOT NULL,             
		descricao TEXT,                          
		usuario_id INT unsigned,               
		data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        tema VARCHAR(255) NOT NULL,
        dt_cr DATETIME DEFAULT CURRENT_TIMESTAMP,
		FOREIGN KEY (usuario_id) REFERENCES usuario(id)
	) ENGINE=InnoDB;


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

	CREATE TABLE resposta_comunidade (
		id INT AUTO_INCREMENT PRIMARY KEY,
		usuario_id INT unsigned,
		quiz_id INT,
		pergunta_id INT,
		alternativa_id INT,
		acertos INT,
		tempo INT,
		pontuacao INT NOT NULL DEFAULT 0,
		data_resposta DATETIME DEFAULT CURRENT_TIMESTAMP,
		FOREIGN KEY (quiz_id) REFERENCES quiz(id),
		FOREIGN KEY (usuario_id) REFERENCES usuario(id),
		FOREIGN KEY (pergunta_id) REFERENCES perguntas_user(id),
		FOREIGN KEY (alternativa_id) REFERENCES alternativas(id)
	);
    
CREATE TABLE comentario (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT UNSIGNED NULL,
  quiz_id INT NOT NULL,
  coment TEXT NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (quiz_id),
  INDEX (usuario_id),
  CONSTRAINT fk_coment_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes_user(id) ON DELETE CASCADE,
  CONSTRAINT fk_coment_usuario FOREIGN KEY (usuario_id) REFERENCES usuario(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



    SHOW CREATE TABLE quizzes_user;
SHOW CREATE TABLE usuario;

    ALTER TABLE usuario add column is_admin tinyint(1) NOT NULL DEFAULT 0;

ALTER TABLE resposta_comunidade
ADD CONSTRAINT fk_quiz
FOREIGN KEY (quiz_id) REFERENCES quizzes_user(id)
ON DELETE CASCADE;
